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

    /**
     * Create a new Question from a HTML string.
     *
     * @param string $html
     * @return Question
     */
    public static function createFromHtml($html)
    {
        $q = new self;

        // TODO

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
        if (isset($attr['id'])) {
            $this->id = $attr['id'];
        } else {
            $this->id = null;
        }
        $this->title = $attr['title'];
        $this->intro = $attr['intro']['text'];
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
        if ($record->id) {
            $this->id = $DB->update_record('question', $record);
        } else {
            $record->timecreated = $_SERVER['REQUEST_TIME'];
            $record->createdby = $USER->id;
            $this->id = $DB->insert_record('question', $record);
        }
        if (!$this->id) {
            return false;
        }
        if ($this->answers) {
            foreach ($this->answers as $answer) {
                $answer->questionId = $this->id;
                if (!$answer->save()) {
                    return false;
                }
            }
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
            $questions[] = self::buildFromRecord($record);
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
        /**
         * @todo check that the question type is MultipleChoice.
         */
        $question = new self();
        $question->id = $record->id;
        /**
         * @todo split the question.questiontext into title+intro
         */
        $question->title = '';
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
        return iconv('UTF-8', 'ASCII//TRANSLIT', trim(preg_replace('/[?!.;,]/', '', $this->title)));
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
}

