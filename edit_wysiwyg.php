<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/locallib.php';
require_once __DIR__ . '/forms/wysiwyg.php';
require_once $CFG->libdir . '/questionlib.php';

global $DB, $COURSE, $OUTPUT, $PAGE;

/* @var $DB moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */

$categoryid = optional_param('category', 0, PARAM_INT);
$courseid  = optional_param('courseid', 0, PARAM_INT);

// var_dump($COURSE); die('edit_wysiwyg');

$course = $DB->get_record('course', array('id' => $courseid), '*');
unset($courseid);
if (!$course) {
    redirect(new moodle_url('course_choice.php', array('redirect' => 'wysiwyg')));
}

$category = $categoryid ? $DB->get_record('question_categories', array('id' => $categoryid)) : null;
unset($categoryid);
if (!$category) {
    $category = get_default_qcategory($course);
    if (!$category) {
        $ccontext = context_course::instance($course->id);
        $category = question_make_default_categories(array($ccontext));
    }
}
$context = context::instance_by_id($category->contextid);

if (!has_capability('moodle/question:add', $context)) {
    redirect(new moodle_url('course_choice.php', array('redirect' => 'wysiwyg')));
}

/**
 * @todo If user has disabled WYSIWYG editor, redirect to edit_standard.php
 */


$PAGE->set_pagelayout('admin');

$url = new moodle_url('/local/questionssimplified/edit_wysiwyg.php');
$PAGE->set_url($url);

$PAGE->set_context($context);
$PAGE->set_title(get_string('wysiwygEdit', 'local_questionssimplified'));
$PAGE->set_heading(get_string('wysiwygEdit', 'local_questionssimplified') . ' - ' . $category->name);

$form = new questionssimplified_wysiwyg_form(null, array('category' => $category, 'course' => $course));

$data = $form->get_data();
if ($data) {
    try {
        $questions = \sqc\Question::createMultiFromHtml($data->questions['text']);
    } catch (Exception $e) {
        /// @todo Handle errors when parsing HTML
        echo "ERROR: " . $e->getMessage();
        $questions = array();
    }
    /**
     * @todo handle errors when saving questions
     */
    //var_dump($questions); var_dump($data); die();
    $ids = array();
    foreach ($questions as $q) {
        if (empty($q->categoryId)) {
            $q->categoryId = $category->id;
        }
        $q->save();
        $ids[] = $q->id;
    }
    redirect(new moodle_url("edit_standard.php", array('questions' => join(',', $ids), 'courseid' => $course->id)));
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
