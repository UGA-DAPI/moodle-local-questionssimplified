<?php
/**
 * Upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_questionssimplified_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2013071901) {
        $incomplete = $DB->get_records_sql(
            "SELECT q.id, GROUP_CONCAT(qa.id) AS answers, COUNT(qa.fraction > 0) AS numright "
            . "FROM {question} q JOIN {question_answers} qa ON q.id=qa.question LEFT JOIN {question_multichoice} qm ON q.id=qm.question "
            . "WHERE qm.id IS NULL AND q.qtype = 'multichoice' "
            . "GROUP BY q.id"
        );
        if ($incomplete) {
            foreach ($incomplete as $q) {
                $record = (object) array(
                    'id' => null,
                    'question' => $q->id,
                    'answers' => $q->answers,
                    'correctfeedback' => '',
                    'partiallycorrectfeedback' => '',
                    'incorrectfeedback' => '',
                    'single' => ($q->numright > 1 ? 0 : 1),
                );
                $DB->insert_record('question_multichoice', $record);
            }
        }
    }
    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
