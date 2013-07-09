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

global $DB, $COURSE, $OUTPUT, $PAGE, $SITE;

/* @var $DB moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */

$categoryid = optional_param('category', 0, PARAM_INT);
$courseid  = optional_param('course', $COURSE->id, PARAM_INT);   // course id (defaults to current course)

$category = $categoryid ? $DB->get_record('question_categories', array('id' => $categoryid)) : null;
unset($categoryid);
if (!$category) {
    /**
     * @todo If no category is given, redirect to a choosing page?
     */
    // If no category is given, use the course's default question category
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    if (!$course) {
        print_error('categorydoesnotexist', 'question');
    }
    $category = $DB->get_record_sql(
            "SELECT * FROM {question_categories} WHERE contextid = ? ORDER BY id ASC LIMIT 1",
            array(context_course::instance($course->id)->id)
    );
    if (!$category) {
        print_error('categorydoesnotexist', 'question');
    }
    unset($course);
}
unset($courseid);
$context = context::instance_by_id($category->contextid);

if (!has_capability('moodle/question:add', $context)) {
    redirect(new moodle_url('course_choice', array('redirect' => 'wysiwyg')));
}
/**
 * @todo Check permissions
 * has_capability('moodle/question:add', $catcontext);
 * has_capability('moodle/question:editall', $catcontext);
require_capability();
 */

/**
 * @todo If user has disabled WYSIWYG editor, redirect to edit_standard.php
 */


$PAGE->set_pagelayout('admin');

$url = new moodle_url('/local/questionssimplified/edit_wysiwyg.php');
$PAGE->set_url($url);

$PAGE->set_context($context);
$PAGE->set_title(get_string('wysiwygEdit', 'local_questionssimplified'));
$PAGE->set_heading(get_string('wysiwygEdit', 'local_questionssimplified') . ' - ' . $category->name);

$form = new questionssimplified_wysiwyg_form(null, array('category' => $category));

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
    redirect("edit_standard.php?questions=" . join(',', $ids));
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
