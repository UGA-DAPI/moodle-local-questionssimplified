<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';

global $COURSE, $OUTPUT, $PAGE;

/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */

$context = context_course::instance($COURSE->id);

$PAGE->set_pagelayout('admin');
$PAGE->set_url(new moodle_url('/local/questionssimplified/help.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('help'));
$PAGE->set_heading(get_string('help') . ' - ' . get_string('pluginname', 'local_questionssimplified'));

echo $OUTPUT->header();

echo get_config('local_questionssimplified', 'helppage');

echo $OUTPUT->footer();
