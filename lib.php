<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require __DIR__ . '/models/Question.php';
require __DIR__ . '/models/Answer.php';


// doc https://moodle.org/mod/forum/discuss.php?d=170325#yui_3_7_3_2_1359043225921_310

function local_questionssimplified_extends_navigation(global_navigation $navigation) {
    global $PAGE;

    $permission = TRUE;

    if ($permission) {
        $node1 = $navigation->add(get_string('MCQcreate', 'local_questionssimplified'));
        $node1->add(
			get_string('wysiwygEdit', 'local_questionssimplified'),
			new moodle_url('/local/questionssimplified/edit_wysiwyg.php')
		);
        $node1->add(
			get_string('standardEdit', 'local_questionssimplified'),
			new moodle_url('/local/questionssimplified/edit_standard.php')
		);
		$node1->add(
			get_string('questionbank', 'question'),
			new moodle_url('/question/edit.php') // manque courseid=? //** @todo **/
		);
	}
}



function find_user_courses_as_teacher() {
    global $DB, $USER;

    $sql = "SELECT cr.id, cr.fullname "
         . "FROM {course} cr "
         . "JOIN {context} co ON (contextlevel = ? AND cr.id = co.instanceid) "
         . "JOIN {role_assignments} ra ON (ra.contextid = co.id) "
         . "JOIN {role} ro ON (ra.roleid = ro.id) "
         . "WHERE ra.userid = ? AND ro.shortname IN ('coursecreator', 'editingteacher', 'teacher') "
         . "ORDER BY cr.fullname ASC";
    $courses = $DB->get_records_sql_menu($sql, array(CONTEXT_COURSE, $USER->id));

    return $courses;
}

function html_courses_list($courses, $currentcourseid) {

    $html = "<ul>\n";
    foreach ($courses as $courseid => $name) {
        if ($courseid == $currentcourseid) {
            $em = "<strong>";
            $endem = "</strong>";
        } else {
            $em = '';
            $endem = '';
        }
        $html .= "<ul>" . $em . $name . $endem . "</ul> \n";
    }
    $html .= "</ul>\n";
    return $html;
}
