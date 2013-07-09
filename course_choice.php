<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';

global $COURSE, $OUTPUT, $PAGE, $SITE;
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */


$redirect = required_param('redirect', PARAM_ALPHA);
$courseid = optional_param('course', 0, PARAM_INT);   // course id (defaults to 0)

// $context = context::instance_by_id($category->contextid);

require_login();

$PAGE->set_pagelayout('admin');

$url = new moodle_url('/local/questionssimplified/course_choice.php');
$PAGE->set_url($url);

// $PAGE->set_context($context);
$PAGE->set_title(get_string('courseChoice', 'local_questionssimplified'));
$PAGE->set_heading(get_string('courseChoice', 'local_questionssimplified'));
echo $OUTPUT->header();


$courses = find_user_courses_as_teacher();
echo html_courses_list($courses, '/local/questionssimplified/course_choice.php', $COURSE->id, $redirect);



echo $OUTPUT->footer();
