<?php

/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

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

function html_courses_list($courses, $baseurl, $currentcourseid, $redirect) {

    $html = "<ul>\n";
    foreach ($courses as $courseid => $name) {
        if ($courseid == $currentcourseid) {
            $em = "<strong>";
            $endem = "</strong>";
        } else {
            $em = '';
            $endem = '';
        }
		$url = new moodle_url($baseurl, array('course' => $courseid, 'redirect' => $redirect));
        $html .= "<li>" . $em . html_writer::link($url, $name) . $endem . "</li> \n";
    }
    $html .= "</ul>\n";
    return $html;
}
