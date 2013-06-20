<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');

class questionssimplified_standard_form extends moodleform {
    function definition() {
        global $CFG;
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('standardHeader', 'local_questionssimplified'));

        //-------------------------------------------------------------------------------
        $repeatQuestion = array();
        $repeatQuestion[] = $mform->createElement('header', '', get_string('question') . ' {no}');
        $repeatQuestion[] = $mform->createElement('hidden', 'question[{no}][id]', 0);
        $repeatQuestion[] = $mform->createElement('text', 'question[{no}][title]', get_string('questionname', 'question'), array('size'=>'80'));
        $repeatQuestion[] = $mform->createElement('editor', 'question[{no}][introeditor]', get_string('description'), array('rows' => 10), array('maxfiles' => 0));

        $repeatAnswer = array();
        $repeatAnswer[] = $mform->createElement('hidden', 'question[{qrank}][answer][{no}][id]', 0);
        $repeatAnswer[] = $mform->createElement('text', 'question[{qrank}][answer][{no}][content]', get_string('answer', 'question') . ' {no}', array('size'=>'80'));
        $repeatAnswer[] = $mform->createElement('checkbox', 'question[{qrank}][answer][{no}][correct]', get_string('rightanswer', 'question') . ' {no}');

        if ($this->_customdata){
            $repeatNo = 1 + count($this->_customdata);
        } else {
            $repeatNo = 2;
        }

        //$this->repeat_elements($repeatQuestion, $repeatNo, array(), 'question_repeats', 'question_add_fields', 1);

        for ($qrank = 0 ; $qrank < $repeatNo ; $qrank++) {
            foreach ($repeatQuestion as $e) {
                $element = self::cloneRepeatedElement($e, $qrank);
                $mform->addElement($element);
            }
            $mform->setType("question[$qrank][id]", PARAM_INT);
            $mform->setType("question[$qrank][title]", PARAM_TEXT);
            $mform->setType("question[$qrank][introeditor]", PARAM_RAW);

            if (empty($this->_customdata[$qrank])){
                $answersNo = 3;
            } else {
                $answersNo = count($this->_customdata[$qrank]->answers);
            }
            for ($arank = 0 ; $arank < $answersNo ; $arank++) {
                foreach ($repeatAnswer as $e) {
                    $element = self::cloneRepeatedElement($e, $arank, array('{qrank}' => $qrank));
                    $mform->addElement($element);
                }
                $mform->setType("question[$qrank][answer][$arank][id]", PARAM_INT);
                $mform->setType("question[$qrank][answer][$arank][content]", PARAM_TEXT);
                $mform->setType("question[$qrank][answer][$arank][correct]", PARAM_RAW);
            }
        }

        //-------------------------------------------------------------------------------
        $this->add_action_buttons(false, get_string('submit'));

        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);

        $this->init_values();
    }

    /**
     * Called by moodleform_mod::set_data() as a pre-hook.
     *
     * @global moodle_database $DB
     * @param array $default_values
     */
    function init_values(){
        if (empty($this->_customdata)) {
            return;
        }
        $qrank = 0;
        foreach ($this->_customdata as $question) {
            /* @var $question \sqc\Question */
            $this->_form->setDefault("question[$qrank][title]", $question->title);
            $this->_form->setDefault(
                    "question[$qrank][introeditor]",
                    array(
                        "text" => $question->intro,
                        "format" => $question->introformat,
                )
            );
            if (!empty($question->answers)) {
                $arank = 0;
                foreach ($question->answers as $answer) {
                    /* @var $answer \sqc\Answer */
                    $this->_form->setDefault("question[$qrank][answer][$arank][id]", $answer->id);
                    $this->_form->setDefault("question[$qrank][answer][$arank][content]", $answer->content);
                    $this->_form->setDefault("question[$qrank][answer][$arank][correct]", $answer->correct);
                    $arank++;
                }
            }
            $qrank++;
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        /**
         * @todo validate
         */
        return $errors;
    }

    /**
     * Insert the elements into the form at a given rank.
     *
     * @param array $elements
     * @param integer $rank
     * @param array $replacements
     */
    protected function repeatElements($elements, $rank, array $replacements=array()) {
        foreach ($elements as $e) {
            $element = self::cloneRepeatedElement($e, $rank, $replacements);
            $this->_form->addElement($element);
        }
    }

    /**
     * Return a cloned element with values replaced.
     *
     * @param HTML_QuickForm_element $e
     * @param integer $rank
     * @param array $replacements
     * @return HTML_QuickForm_element
     */
    private static function cloneRepeatedElement($e, $rank, array $replacements=array()) {
        $replacements['{no}'] = $rank;
        $element = fullclone($e);
        $name = $element->getName();
        $name = str_replace(array_keys($replacements), array_values($replacements), $name);
        // display
        $replacements['{no}']++;
        $element->setName($name);
        $label = $element->getLabel();
        $label = str_replace(array_keys($replacements), array_values($replacements), $label);
        $element->setLabel($label);
        if (is_a($element, 'HTML_QuickForm_header')) {
            $element->setText(str_replace(array_keys($replacements), array_values($replacements), $element->_text));
        }
        return $element;
    }
}

