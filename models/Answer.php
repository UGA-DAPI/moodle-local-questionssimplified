<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace sqc;

global $DB;
/* @var $DB \moodle_database */

class Answer
{
    /** @var integer */
    public $id;

    /** @var integer */
    public $questionId;

    /** @var string */
    public $content;

    /** @var boolean */
    public $correct;

    /**
     * Saves the instance into the DB.
     *
     * @global \moodle_database $DB
     * @var integer $divisor (opt) Number of correct answers.
     * @return boolean Success?
     */
    public function save($divisor=1) {
        global $DB;
        $record = $this->convertToDbRecord($divisor);
        if ($record->id) {
            $DB->update_record('question_answers', $record);
        } else {
            $this->id = $DB->insert_record('question_answers', $record);
        }
        return (boolean) $this->id;
    }

    public static function findAllByQuestion($qid) {
        global $DB;
        $records = $DB->get_records('question_answers', array('question' => $qid));
        $answers = array();
        foreach ($records as $record) {
            $answers[] = self::buildFromRecord($record);
        }
        return $answers;
    }

    /**
     * Returns a Answer instance built from a record object.
     *
     * @param object $record
     * @return \sql\Answer
     */
    public static function buildFromRecord(\stdClass $record)
    {
        /**
         * @todo check that the answer has the fraction = 0|1
         */
        $answer = new self();
        $answer->id = $record->id;
        $answer->questionId = $record->question;
        /**
         * @todo convert formated text into raw text
         */
        $answer->content = strip_tags($record->answer);
        $answer->correct = $record->fraction == 0 ? false : true;
        return $answer;
    }

    /**
     * Returns a Answer instance built from an array (form).
     *
     * @param array $record
     * @return \sql\Answer
     */
    public static function buildFromArray(array $record)
    {
        if (empty($record['content'])) {
            return null;
        }
        $answer = new self();
        if (isset($record['id'])) {
            $answer->id = $record['id'];
        } else {
            $answer->id = null;
        }
        if (!empty($record['questionId'])) {
            $answer->questionId = $record['questionId'];
        }
        $answer->content = strip_tags($record['content']);
        $answer->correct = !empty($record['correct']);
        return $answer;
    }

    /**
     * Convert an instance to a stdClass suitable for the DB table "question_answers".
     *
     * @var integer $divisor (opt) Number of correct answers.
     * @return stdClass
     */
    protected function convertToDbRecord($divisor=1)
    {
        $record = array(
            'id' => $this->id,
            'question' => $this->questionId,
            'answer' => $this->content,
            'answerformat' => FORMAT_PLAIN,
            'fraction' => $this->correct ? ($divisor ? 1.0/$divisor : "1.0") : 0,
            'feedback' => '',
            'feedbackformat' => FORMAT_PLAIN,
        );
        if (empty($this->id)) {
            $record['id'] = null;
        } else {
            $record['id'] = $this->id;
        }
        return (object) $record;
    }
}
