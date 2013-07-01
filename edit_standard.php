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

global $DB, $OUTPUT, $PAGE, $SITE;
/* @var $DB moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */

$courseid  = optional_param('course', $SITE->id, PARAM_INT);   // course id (defaults to Site)
$questionsId = optional_param('questions', '', PARAM_SEQUENCE);   // course id (defaults to Site)

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
unset($courseid);

if ($questionsId) {
    $questions = \sqc\Question::findAllById(explode(',', $questionsId));
} else {
    $questions = array();
}
unset($questionsId);

/**
 * @todo Check permissions
 */

$PAGE->set_pagelayout('admin');

$url = new moodle_url('/local/questionssimplified/edit_standard.php');
$PAGE->set_url($url);

$context = context_course::instance($course->id);
$PAGE->set_context($context);
$PAGE->set_title(get_string('standardEdit', 'local_questionssimplified'));
$PAGE->set_heading(get_string('standardEdit', 'local_questionssimplified') . ' - ' . $course->fullname);
echo $OUTPUT->header();

$form = new questionssimplified_standard_form(null, array('course' => $course, 'questions' => $questions));

/**
 * @todo Use the "course" param unless the question already has a courseid.
 */

$data = $form->get_data();
if ($data) {
    foreach ($data->question as $line) {
        $question = \sqc\Question::buildFromArray($line);
        if (!$question->save() && $question->title) {
            echo "ERROR saving question";
            var_dump($question); /// @todo Remove var_dump from prod code
        }
    }
    die();
}

$form->display();

echo $OUTPUT->footer();
