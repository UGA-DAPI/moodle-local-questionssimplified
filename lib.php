<?php
/**
 * @package    local
 * @subpackage questionssimplified
 * @copyright  2013 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require __DIR__ . '/models/Question.php';
require __DIR__ . '/models/Answer.php';


// doc https://moodle.org/mod/forum/discuss.php?d=170325#yui_3_7_3_2_1359043225921_310

function local_questionssimplified_extends_navigation(global_navigation $navigation) {
    global $PAGE;

    $permission = TRUE;

    if ($permission) {
        $node1 = $navigation->add(get_string('MCQcreate', 'local_questionssimplified'));
        $node1->add(
			get_string('wysiwygEdit', 'local_questionssimplified'),
			new moodle_url('/local/questionssimplified/edit_wysiwyg.php')
		);
        $node1->add(
			get_string('standardEdit', 'local_questionssimplified'),
			new moodle_url('/local/questionssimplified/edit_standard.php')
		);
	}
}
