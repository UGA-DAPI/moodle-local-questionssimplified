<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace sqc;

/* @var $DB moodle_database */
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

        return array_map(array(self, 'createFromHtml'), $split);
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
        $this->intro = $attr['intro'];
        if (!empty($attr['answer'])) {
            foreach ($attr['answer'] as $a) {
                $answer = Answer::buildFromArray($a);
                if ($answer) {
                    $this->answers[] = $answer;
                }
            }
        }
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
}

