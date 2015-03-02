<?php

/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */
/* @var $DB moodle_database */
/*
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
		$url = new moodle_url($baseurl, array('courseid' => $courseid, 'redirect' => $redirect));
        $html .= "<li>" . $em . html_writer::link($url, $name) . $endem . "</li> \n";
    }
    $html .= "</ul>\n";
    return $html;
}
*/
function get_default_qcategory($course) {
    global $DB;
    // If no category is given, use the course's default question category
    if (!$course) {
        print_error('categorydoesnotexist', 'question');
    }
    return $DB->get_record_sql(
            "SELECT * FROM {question_categories} WHERE contextid = ? ORDER BY id ASC LIMIT 1",
            array(context_course::instance($course->id)->id)
    );
}

function get_qcategories($course) {
    global $DB;
    if (!$course) {
        die('Course required');
    }
    return $DB->get_records_sql_menu(
            "SELECT id, name FROM {question_categories} WHERE contextid = ? ORDER BY sortorder ASC",
            array(context_course::instance($course->id)->id)
    );
}

function questionssimplified_is_teacher($user) {
    global $DB, $COURSE;
    if (isset($COURSE->id)) {
        $context = context_course::instance($COURSE->id);
    } else {
        $context = context_system::instance();
    }
    if (has_capability('moodle/question:add', $context, $user)) {
        return true;
    }
    $pattern = get_config('local_questionssimplified', 'cohortpattern');
    if (!$pattern) {
        return false;
    }
    return $DB->record_exists_sql(
            "SELECT 1 FROM {cohort_members} cm JOIN {cohort} c ON cm.cohortid = c.id "
            . "WHERE cm.userid = ? AND c.idnumber LIKE ?",
            array($user->id, $pattern)
    );
}
