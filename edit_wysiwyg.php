<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/forms/wysiwyg.php';

$courseid  = optional_param('course', SITEID, PARAM_INT);   // course id (defaults to Site)
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

/**
 * @todo Check permissions
 * has_capability('moodle/question:add', $catcontext);
 * has_capability('moodle/question:editall', $catcontext);
require_login($course);
require_capability();
 */

$PAGE->set_pagelayout('admin');

$url = new moodle_url('/local/questionssimplified/edit_wysiwig.php');
$PAGE->set_url($url);

$context = context_course::instance($course->id);
$PAGE->set_context($context);
$PAGE->set_title(get_string('wysiwygEdit', 'local_questionssimplified'));
$PAGE->set_heading($COURSE->fullname);
echo $OUTPUT->header();

$form = new questionssimplified_wysiwyg_form();

$form->display();

echo $OUTPUT->footer();
