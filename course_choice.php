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


// $context = context::instance_by_id($category->contextid);


/**
 * @todo Check permissions
 */
require_login();

$PAGE->set_pagelayout('admin');

$url = new moodle_url('/local/questionssimplified/course_choice.php');
$PAGE->set_url($url);

// $PAGE->set_context($context);
$PAGE->set_title(get_string('courseChoice', 'local_questionssimplified'));
$PAGE->set_heading(get_string('courseChoice', 'local_questionssimplified'));
echo $OUTPUT->header();


$courses = find_user_courses_as_teacher();
echo html_courses_list($courses, $COURSE->id);


echo $OUTPUT->footer();
