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
require_once __DIR__ . '/forms/standard.php';

global $DB, $OUTPUT, $PAGE;
/* @var $DB moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */

$courseid  = optional_param('courseid', $COURSE->id, PARAM_INT);
$questionsId = optional_param('questions', '', PARAM_SEQUENCE);

$course = $DB->get_record('course', array('id' => $courseid), '*');
unset($courseid);
if (!$course) {
    if (!$questionsId) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'parameter "course" required');
    } else {
        redirect(new moodle_url('course_choice.php', array('redirect' => 'wysiwyg')));
    }
}

$categories = get_qcategories($course);
if (!$categories) {
    die("Error, no question category available for this course.");
}
$context = context_course::instance($course->id);

if ($questionsId) {
    $questions = \sqc\Question::findAllById(explode(',', $questionsId));
} else {
    $questions = array();
}
unset($questionsId);

if (!has_capability('moodle/question:add', $context)) {
    redirect(new moodle_url('course_choice', array('redirect' => 'standard')));
}

$PAGE->set_pagelayout('admin');

$url = new moodle_url('/local/questionssimplified/edit_standard.php');
$PAGE->set_url($url);

$PAGE->set_context($context);
$PAGE->set_title(get_string('standardEdit', 'local_questionssimplified'));
$PAGE->set_heading(get_string('standardEdit', 'local_questionssimplified') . ' - ' . $course->shortname);

$form = new questionssimplified_standard_form(null, array('categories' => $categories, 'questions' => $questions, 'course' => $course));

$data = $form->get_data();
if ($data) {
    foreach ($data->question as $line) {
        $question = \sqc\Question::buildFromArray($line);
        if (empty($question->categoryId)) {
            print_error('categorydoesnotexist', 'question');
        } else {
            $qcategory = $DB->get_record('question_categories', array('id' => $question->categoryId));
            if (!$qcategory) {
                print_error('categorydoesnotexist', 'question');
            }
        }
        if (!$question->save() && $question->title) {
            echo "ERROR saving question";
            var_dump($question); /// @todo Remove var_dump from prod code
        }
    }
    redirect(new moodle_url('/index.php'));
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
