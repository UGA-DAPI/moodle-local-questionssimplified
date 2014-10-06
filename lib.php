<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/models/Question.php';
require_once __DIR__ . '/models/Answer.php';
require_once __DIR__ . '/locallib.php';


// doc https://moodle.org/mod/forum/discuss.php?d=170325#yui_3_7_3_2_1359043225921_310

function local_questionssimplified_extends_navigation(global_navigation $navigation) {
    global $USER, $COURSE;
    if (questionssimplified_is_teacher($USER)) {
        $node1 = $navigation->add(get_string('MCQcreate', 'local_questionssimplified'));
        if (isset($COURSE) && $COURSE->id > 1) {
            $urlparams = array('courseid' => $COURSE->id);
        } else {
            $urlparams = array();
        }
        $node1->add(
                get_string('wysiwygEdit', 'local_questionssimplified'),
                new moodle_url('/local/questionssimplified/edit_wysiwyg.php', $urlparams)
        );
        $node1->add(
                get_string('standardEdit', 'local_questionssimplified'),
                new moodle_url('/local/questionssimplified/edit_standard.php', $urlparams)
        );
        $node1->add(
                get_string('questionbank', 'local_questionssimplified'), // get_string('questionbank', 'question'),
                new moodle_url('/local/questionssimplified/course_choice.php', array('redirect' => 'bank'))
        );
        $node1->add(
                get_string('help'), new moodle_url('/local/questionssimplified/help.php')
        );
    }
}
