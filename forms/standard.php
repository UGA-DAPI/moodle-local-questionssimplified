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
        /**
         * @todo Toggle the advanced settings (feedback+weight per question)
         */

        //-------------------------------------------------------------------------------
        $repeatQuestion = array(
            $mform->createElement('header', '', get_string('question') . ' {no}'),
            $mform->createElement('hidden', 'question[{no}][id]', 0),
            $mform->createElement('text', 'question[{no}][title]', get_string('questionname', 'question'), array('size'=>'80')),
            $mform->createElement('editor', 'question[{no}][intro]', get_string('description'), array('rows' => 10), array('maxfiles' => 0)),
        );
        $typesQuestion = array(
            "question[{no}][id]" => PARAM_INT,
            "question[{no}][title]" => PARAM_TEXT,
            "question[{no}][intro]" => PARAM_RAW,
        );

        $repeatAnswer = array(
            $mform->createElement('hidden', 'question[{qrank}][answer][{no}][id]', 0),
            $mform->createElement('text', 'question[{qrank}][answer][{no}][content]', get_string('answer', 'question') . ' {no}', array('size'=>'80')),
            $mform->createElement('checkbox', 'question[{qrank}][answer][{no}][correct]', get_string('rightanswer', 'question') . ' {no}'),
        );
        $typesAnswer = array(
            "question[{qrank}][answer][{no}][id]" => PARAM_INT,
            "question[{qrank}][answer][{no}][content]" => PARAM_TEXT,
            "question[{qrank}][answer][{no}][correct]" => PARAM_RAW,
        );

        if ($this->_customdata){
            $repeatNo = count($this->_customdata['questions']);
        } else {
            $repeatNo = 2;
        }
        $repeatNo = $this->initRepeat("questionsno", $repeatNo);

        $addstring = get_string('addfields', 'form', 2);
        for ($qrank = 0 ; $qrank < $repeatNo ; $qrank++) {
            $this->repeatElements($repeatQuestion, $typesQuestion, $qrank);

            if (empty($this->_customdata['questions'][$qrank])){
                $answersNo = 3; // empty answers if none are given
            } else {
                $answersNo = 1 + count($this->_customdata['questions'][$qrank]->answers);
            }
            $answersNo = $this->initRepeat("q{$qrank}answersno", $answersNo, "q{$qrank}answersadd");
            for ($arank = 0 ; $arank < $answersNo ; $arank++) {
                $this->repeatElements($repeatAnswer, $typesAnswer, $arank, array('{qrank}' => $qrank));
            }
            $this->_form->registerNoSubmitButton("q{$qrank}answersadd");
            $this->_form->addElement('submit', "q{$qrank}answersadd", $addstring);
        }

        //-------------------------------------------------------------------------------
        $this->add_action_buttons(false, get_string('submit'));

        $mform->addElement('hidden', 'category');
        $mform->setType('category', PARAM_INT);

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
        if (empty($this->_customdata['questions'])) {
            return;
        }
        $qrank = 0;
        foreach ($this->_customdata['questions'] as $question) {
            /* @var $question \sqc\Question */
            $this->_form->setDefault("question[$qrank][id]", $question->id);
            $this->_form->setDefault("question[$qrank][title]", $question->title);
            $this->_form->setDefault(
                    "question[$qrank][intro]",
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
     * Init the repeat sequence with a default number of repeats.
     *
     * @param string  $name HTML name of the hidden field (no [] allowed).
     * @param integer $defaultCount
     * @param string $buttonName (opt) HTML name of the button that adds fields.
     * @return integer Real number of repeats.
     */
    protected function initRepeat($name, $defaultCount, $buttonName=null)
    {
        $repeats = optional_param($name, $defaultCount, PARAM_INT);
        if ($buttonName) {
            $add = optional_param($buttonName, 0, PARAM_TEXT);
            if ($add) {
                $repeats += 2;
            }
        }
        $this->_form->addElement('hidden', $name, $repeats);
        $this->_form->setType($name, PARAM_INT);
        $this->_form->setConstant($name, $repeats);
        return $repeats;
    }

    /**
     * Insert the elements into the form at a given rank.
     *
     * @param array $elements
     * @param array $types assoc: elementName => type
     * @param integer $rank
     * @param array (opt) $replacements
     */
    protected function repeatElements($elements, $types, $rank, array $replacements=array()) {
        $replacements['{no}'] = $rank;
        foreach ($elements as $e) {
            $element = self::cloneRepeatedElement($e, $replacements);
            $this->_form->addElement($element);
        }
        foreach ($types as $name => $type) {
            $this->_form->setType(
                    str_replace(array_keys($replacements), array_values($replacements), $name),
                    $type
            );
        }
    }

    /**
     * Return a cloned element with values replaced.
     *
     * @param HTML_QuickForm_element $e
     * @return HTML_QuickForm_element
     */
    private static function cloneRepeatedElement($e, array $replacements) {
        $element = fullclone($e);
        $name = str_replace(array_keys($replacements), array_values($replacements), $element->getName());
        // display
        $replacements['{no}']++;
        $element->setName($name);
        $label = str_replace(array_keys($replacements), array_values($replacements), $element->getLabel());
        $element->setLabel($label);
        if (is_a($element, 'HTML_QuickForm_header')) {
            $element->setText(str_replace(array_keys($replacements), array_values($replacements), $element->_text));
        }
        return $element;
    }
}
