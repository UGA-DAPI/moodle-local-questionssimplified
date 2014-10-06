<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/locallib.php';
require_once($CFG->dirroot . '/lib/questionlib.php');

global $COURSE, $OUTPUT, $PAGE;
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */

$redirect = required_param('redirect', PARAM_ALPHA);
$courseid = optional_param('course', 0, PARAM_INT);   // course id (defaults to 0)
$system = optional_param('system', false, PARAM_BOOL);     // choice = system default category

$redirections = array(
	'standard' => '/local/questionssimplified/edit_standard.php',
	'wysiwyg' => '/local/questionssimplified/edit_wysiwyg.php',
	'bank' => '/question/edit.php'
);

if ( ! isset($redirections[$redirect]) ) {
	throw new coding_exception("$redirect : redirection invalide.");
}

if (isset($COURSE->id)) {
    $context = context_course::instance($COURSE->id);
} else {
    $context = context_system::instance();
}

$selfurl = '/local/questionssimplified/course_choice.php';

/**
 * @todo Check permission to access system
 */

if ( !$system && $courseid == 0 ) { // interactive page for user selection
	require_login();

	$PAGE->set_pagelayout('admin');
	$PAGE->set_context($context);
	$url = new moodle_url($selfurl);
	$PAGE->set_url($url);

	// $PAGE->set_context($context);
	$PAGE->set_title(get_string('courseChoice', 'local_questionssimplified'));
	$PAGE->set_heading(get_string('courseChoice', 'local_questionssimplified'));
	echo $OUTPUT->header();

	echo "<p>Choisissez le cours de rattachement des questions que vous allez saisir.</p>";

	$courses = find_user_courses_as_teacher();
	echo html_courses_list($courses, $selfurl, $COURSE->id, $redirect);

    /*
	echo "<ul>";
	$url = new moodle_url($selfurl, array('system' => 1, 'redirect' => $redirect));
	echo "<li>" . html_writer::link($url, "Liste globale (système)") . "</li>";
	echo "</ul>";
     */

	/**
	 * @todo ajouter : Ajouter un cours || Demander la création d'un cours **
	 */


	echo $OUTPUT->footer();

} else { // non-interactive redirection

	if ($system) {
		$context = context_system::instance();
		$qcategory = question_get_default_category($context->id);
		if ( ! $qcategory ) { // does not exist yet
			$qcategory = question_make_default_categories(array($context));
		}
		if ($redirect == 'bank') {
			$urlparams = array('courseid' => 1);
		} else { //wysiwyg or standard
			$urlparams = array('category' => $qcategory->id);
		}
		$url = new moodle_url($redirections[$redirect], $urlparams);
		redirect($url);
	} elseif ($courseid > 0) {
		$url = new moodle_url($redirections[$redirect], array('courseid' => $courseid));
		redirect($url);
	}

}
