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

        $mform->addElement('static', 'intro', get_string('wysiwygIntroTitle', 'local_questionssimplified'));
        $mform->setDefault('intro', get_string('wysiwygIntroContent', 'local_questionssimplified'));

        $mform->addElement('editor', 'questions', get_string('questions', 'question'), null, self::editor_options());
        $mform->setType('questions', PARAM_RAW);
        $mform->addRule('questions', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('questions', 'wysiwigtext', 'local_questionssimplified');

        $this->add_action_buttons(false, get_string('submitToQBank', 'local_questionssimplified'));

        $mform->addElement('hidden', 'category');
        $mform->setType('category', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $this->_customdata['course']->id);
        $mform->setType('courseid', PARAM_INT);

        $this->init_values();
    }

    /**
     * Called at the end of the form definition.
     *
     * @global moodle_database $DB
     */
    function init_values(){
        if (empty($this->_customdata)) {
            return;
        }
        if (!empty($this->_customdata['category'])) {
            $this->_form->setDefault('category', $this->_customdata['category']->id);
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        /**
         * @todo validate
         */
        return $errors;
    }
}

