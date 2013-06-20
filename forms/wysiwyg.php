<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');

class questionssimplified_wysiwyg_form extends moodleform {
    /**
     * Returns the options array to use in forum text editor
     *
     * @return array
     */
    public static function editor_options() {
        return array(
            'noclean' => true,
        );
    }

    function definition() {
        global $CFG;
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('wysiwygHeader', 'local_questionssimplified'));

        $mform->addElement('editor', 'questions', get_string('questions', 'question'), null, self::editor_options());
        $mform->setType('questions', PARAM_RAW);
        $mform->addRule('questions', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(false, get_string('submit'));

        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        /**
         * @todo validate
         */
        return $errors;
    }
}

