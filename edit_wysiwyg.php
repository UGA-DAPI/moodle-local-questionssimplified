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

global $DB, $OUTPUT, $PAGE, $SITE;
/* @var $DB moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */

$courseid  = optional_param('course', $SITE->id, PARAM_INT);   // course id (defaults to Site)
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

/**
 * @todo Check permissions
 * has_capability('moodle/question:add', $catcontext);
 * has_capability('moodle/question:editall', $catcontext);
require_login($course);
require_capability();
 */

/**
 * @todo If user has disabled WYSIWYG editor, redirect to edit_standard.php
 */


$PAGE->set_pagelayout('admin');

$url = new moodle_url('/local/questionssimplified/edit_wysiwig.php');
$PAGE->set_url($url);

$context = context_course::instance($course->id);
$PAGE->set_context($context);
$PAGE->set_title(get_string('wysiwygEdit', 'local_questionssimplified'));
$PAGE->set_heading(get_string('wysiwygEdit', 'local_questionssimplified') . ' - ' . $course->fullname);

$form = new questionssimplified_wysiwyg_form(null, array('course' => $course));

$data = $form->get_data();
if ($data) {
    $questions = array();
    try {
        $questions = \sqc\Question::createMultiFromHtml($data->questions['text']);
    } catch (Exception $e) {
        /// @todo Handle errors when parsing HTML
        echo "ERROR: " . $e->getMessage();
    }
    /**
     * @todo handle errors when saving questions
     */
    //var_dump($questions); var_dump($data); die();
    $ids = array();
    foreach ($questions as $q) {
        $q->save();
        $ids[] = $q->id;
    }
    redirect("edit_standard.php?questions=" . join(',', $ids));
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
