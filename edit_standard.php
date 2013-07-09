<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/forms/standard.php';

global $DB, $OUTPUT, $PAGE;
/* @var $DB moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */

$categoryid = optional_param('category', 0, PARAM_INT);
$questionsId = optional_param('questions', '', PARAM_SEQUENCE);

if ($questionsId) {
    $questions = \sqc\Question::findAllById(explode(',', $questionsId));
} else {
    $questions = array();
}
unset($questionsId);

if ($questions) {
    $categoryid = $questions[0]->categoryId;
    $category = $DB->get_record('question_categories', array('id' => $categoryid));
} else {
    if (!$categoryid) {
        redirect(new moodle_url('course_choice.php', array('redirect' => 'standard')));
    }
    $category = $DB->get_record('question_categories', array('id' => $categoryid));
    if (!$category) {
        print_error('categorydoesnotexist', 'question');
    }
}
unset($categoryid);
$context = context::instance_by_id($category->contextid);

/**
 * @todo Check permissions
 */
if (!has_capability('moodle/question:add', $context)) {
    redirect(new moodle_url('course_choice', array('redirect' => 'standard')));
}

$PAGE->set_pagelayout('admin');

$url = new moodle_url('/local/questionssimplified/edit_standard.php');
$PAGE->set_url($url);

$PAGE->set_context($context);
$PAGE->set_title(get_string('standardEdit', 'local_questionssimplified'));
$PAGE->set_heading(get_string('standardEdit', 'local_questionssimplified') . ' - ' . $category->name);
echo $OUTPUT->header();

$form = new questionssimplified_standard_form(null, array('category' => $category, 'questions' => $questions));

$data = $form->get_data();
if ($data) {
    foreach ($data->question as $line) {
        $question = \sqc\Question::buildFromArray($line);
        if (empty($question->categoryId)) {
            $question->categoryId = $category->id;
        } else {
            $qcategory = $DB->get_record('question_categories', array('id' => $question->category));
            if (!$qcategory) {
                print_error('categorydoesnotexist', 'question');
            }
        }
        if (!$question->save() && $question->title) {
            echo "ERROR saving question";
            var_dump($question); /// @todo Remove var_dump from prod code
        }
    }
    die();
}

$form->display();

echo $OUTPUT->footer();
