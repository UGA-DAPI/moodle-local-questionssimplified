<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace sqc;

/* @var $DB \moodle_database */
global $DB;

class Question
{
    /** @var integer */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $intro;

    /** @var integer */
    public $introformat;

    /** @var Answer[] */
    public $answers;

    protected static function cleanupHtml($html)
    {
        return trim(
                preg_replace('#<strong><br ?/?></strong>#i', '<br />',
                    preg_replace('#<span[^>]*><br ?/?></span>#i', '<br />', $html)
                )
        );
    }

    /**
     * Create a new Question from a HTML string.
     *
     * @param string $html
     * @return Question
     */
    public static function createFromHtml($html)
    {
        $q = new self;
        $q->answers = array();
        $html = self::cleanupHtml($html);

        $dom = new \DOMDocument();
        $dom->loadHTML('<div>' . $html . '</div>');
        $dom->removeChild($dom->firstChild); // remove <!DOCTYPE
        $spans = $dom->getElementsByTagName('span');
        $spansToDelete = array();
        foreach ($spans as $span) {
            if ($span->attributes->getNamedItem("style")) {
                $style = $span->attributes->getNamedItem("style")->nodeValue;
                if (preg_match('/\b(line-through|underline)\b/', $style, $m)) {
                    $a = new Answer();
                    $a->correct = $m[1] === 'underline' ? true : false;
                    foreach ($span->childNodes as $node) {
                        if ($node->nodeName === 'br') {
                            if (trim($a->content)) {
                                // newline => split answer
                                $q->answers[] = $a;
                                $a = new Answer();
                                $a->correct = $m[1] === 'underline' ? true : false;
                            }
                        } else {
                            $a->content .= $dom->saveXml($node);
                        }
                    }
                    if (trim($a->content)) {
                        $q->answers[] = $a;
                    }
                    $spansToDelete[] = $span;
                }
            }
        }
        // cannot update dom while reading, a second loop is necessary
        foreach ($spansToDelete as $span) {
            $span->parentNode->removeChild($span);
        }
        //echo "\n**********\n$html";
        $html = $dom->saveXml($dom->firstChild->firstChild->firstChild->firstChild);
        //echo "\n***********\n$html\n***********\n"; print_r($q->answers);

        if (preg_match('#^\s*<p>\s*<strong>(.+?)</strong>\s*</p>#i', $html, $m)) {
            $strong = $m[1];
            if (stripos($strong, '<p>') === false) {
                if (stripos($strong, '<br') === false) {
                    $q->title = $strong;
                    $html = preg_replace('#^\s*<p>\s*<strong>(.+?)</strong>\s*</p>#', '', $html);
                } else {
                    preg_match('#^(.+?)<br ?/?>(.+)$#', $strong, $m);
                    $q->title = preg_replace('#</strong>\s*$#i', '', $m[1]);
                    $html = preg_replace('#^\s*<p>\s*<strong>(.+?)</strong>\s*<br ?/?>#', '<p>', $html);
                }
            }
        }
        if (empty($q->title)) {
            if (preg_match('#^\s*<p>\s*<strong>(.+?)</strong>\s*<br ?/?>#i', $html, $m)) {
                $q->title = $m[1];
                $html = str_replace($m[0], '<p>', $html);
            } else if (preg_match('#^\s*<p>\s*<strong>(.+?)<br ?/?>\s*</strong>#i', $html, $m)) {
                $q->title = $m[1];
                echo $m[0];
                $html = str_replace($m[0], '<p>', $html);
            } else {
                throw new \Exception("Invalid format of HTML");
            }
        }

        if (preg_match('/^\s*<p>/s', $html)) {
            $q->intro = trim(preg_replace('#<br ?/?>\s*</p>\s*$#s', '</p>', $html));
        } else if (preg_match('/^\s*$/s', $html)) {
            $q->intro = '';
        } else {
            $q->intro = '<p>' . trim(preg_replace('#<br ?/?>\s*$#s', '', $html)) . '</p>';
        }
        $q->introformat = 1; // FORMAT_HTML;

        return $q;
    }

    /**
     * Create several Questions from a HTML string.
     *
     * @param string $html
     * @return Question[]
     */
    public static function createMultiFromHtml($html)
    {
        // TODO
        $split = array($html);

        return array_map(array('\sqc\Question', 'createFromHtml'), $split);
    }

    /**
     * Sets the attributes (including answers) of the instance.
     *
     * @param array $attr
     */
    public function setAttributes(array $attr) {
        $this->id = isset($attr['id']) ? $attr['id'] : null;
        $this->title = isset($attr['title']) ? $attr['title'] : '';
        $this->intro = isset($attr['intro']['text']) ? $attr['intro']['text'] : '';
        $this->introformat = $attr['intro']['format'];
        if (!empty($attr['answer'])) {
            foreach ($attr['answer'] as $a) {
                $answer = Answer::buildFromArray($a);
                if ($answer) {
                    $answer->questionId = $this->id;
                    $this->answers[] = $answer;
                }
            }
        }
    }

    /**
     * Saves the instance into the DB.
     *
     * @global \moodle_database $DB
     * @return boolean Success?
     */
    public function save() {
        global $DB, $USER;
        $record = $this->convertToDbRecord();
        if (!$record->name) {
            return false;
        }
        if ($record->id) {
            $DB->update_record('question', $record);
        } else {
            $record->timecreated = $_SERVER['REQUEST_TIME'];
            $record->createdby = $USER->id;
            $this->id = $DB->insert_record('question', $record);
        }
        if (!$this->id) {
            return false;
        }
        if ($this->answers) {
            $numCorrect = $this->countCorrectAnswers();
            foreach ($this->answers as $answer) {
                $answer->questionId = $this->id;
                if (!$answer->save($numCorrect)) {
                    return false;
                }
            }
            /**
             * @todo Delete other answers of this question
             * @todo Display an error when something went wrong
             */
        }
        return true;
    }

    /**
     * Returns a list of Question matching the list of ID.
     *
     * @param array $ids
     * @return array of \sql\Question
     */
    public static function findAllById(array $ids)
    {
        global $DB;
        $records = $DB->get_records_list('question', 'id', $ids);
        $questions = array();
        foreach ($records as $record) {
            $q = self::buildFromRecord($record);
            if ($q) {
                $questions[] = $q;
            } else {
                // error
            }
        }
        return $questions;
    }

    /**
     * Returns a Question instance built from a record object.
     *
     * If the answers are not given, they are read in the DB.
     *
     * @param object $record
     * @param array (opt) $answers
     * @return \sql\Question
     */
    public static function buildFromRecord(\stdClass $record, $answers=null)
    {
        if ($record->qtype !== 'multichoice') {
            return null;
        }
        $question = new self();
        $question->id = $record->id;
        $question->title = $record->name;
        $question->intro = $record->questiontext;
        $question->introformat = $record->questiontextformat;
        $question->answers = Answer::findAllByQuestion($question->id);
        return $question;
    }

    /**
     * Returns a Question instance built from a record array (form).
     *
     * @param array $record
     * @return \sql\Question
     */
    public static function buildFromArray(array $record)
    {
        $question = new self();
        $question->setAttributes($record);
        return $question;
    }

    /**
     * Returns a normalized title, as Moodle wishes it.
     *
     * @return string
     */
    protected function getNormalizedTitle() {
        if (empty($this->title)) {
            return '';
        }
        return trim(strip_tags($this->title));
    }

    /**
     * Convert an instance to a stdClass suitable for the DB table "question".
     *
     * @return stdClass
     */
    protected function convertToDbRecord() {
        global $USER;
        $record = array(
            'id' => $this->id,
            // 'category' => ,
            // 'parent' => ,
            'name' => $this->getNormalizedTitle(),
            'questiontext' => $this->intro,
            'questiontextformat' => $this->introformat,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_PLAIN,
            'defaultmark' => '1.0',
            'penalty' => '1.0',
            'qtype' => 'multichoice',
            'length' => 1,
            // 'stamp',
            // 'version',
            'hidden' => 0,
            'timemodified' => $_SERVER['REQUEST_TIME'],
            'modifiedby' => $USER->id,
        );
        if (empty($this->id)) {
            $record['id'] = null;
        } else {
            $record['id'] = $this->id;
        }
        return (object) $record;
    }

    /**
     * Return the number of correct answers.
     *
     * @return int Num of correct answers
     */
    protected function countCorrectAnswers() {
        if (empty($answers)) {
            return 0;
        }
        $count = 0;
        foreach ($this->answers as $a) {
            if ($a->correct) {
                $count++;
            }
        }
        return $count;
    }
}

