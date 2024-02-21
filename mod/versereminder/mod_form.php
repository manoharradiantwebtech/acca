<?php


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_versereminder_mod_form extends moodleform_mod {

    public function definition() {

        global $COURSE, $CFG;
        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        if (!$COURSE->enablecompletion) {
            $coursecontext = context_course::instance($COURSE->id);
            if (has_capability('moodle/course:update', $coursecontext)) {
                $mform->addElement('static', 'completionwillturnon', get_string('completion', 'versereminder'),
                                   get_string('completionwillturnon', 'versereminder'));
            }
        }
        $mform->addElement('text', 'name', get_string('verseremindername', 'versereminder'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addElement('header', 'reminderversefieldset', get_string('reminderversefieldset', 'versereminder'));
        $mform->setExpanded('reminderversefieldset', true);
		$mform->addElement('hidden', 'emailuser', 2);
        $mform->addElement('text', 'emailsubject', get_string('emailsubject', 'versereminder'), array('size' => '64'));
        $mform->setType('emailsubject', PARAM_TEXT);
        $mform->addRule('emailsubject', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->disabledif('emailsubject', 'emailuser', 'eq', REMINDERVERSE_EMAILUSER_NEVER);
        $mform->addHelpButton('emailsubject', 'emailsubject', 'versereminder');
        $mform->addElement('editor', 'emailcontent', get_string('emailcontent', 'versereminder'), null, null);
        $mform->setDefault('emailcontent', get_string('emailcontentdefaultvalue', 'versereminder'));
		$mform->setDefault('emailsubject', get_string('emailsubjectdefault', 'versereminder'));
        $mform->setType('emailcontent', PARAM_RAW);
        $mform->disabledif('emailcontent', 'emailuser', 'eq', REMINDERVERSE_EMAILUSER_NEVER);
		$mform->addElement('editor', 'manageremailcontent', get_string('manageremailcontent', 'versereminder'), null, null);
		$mform->setType('manageremailcontent', PARAM_RAW);
        $mform->disabledif('manageremailcontent', 'emailuser', 'eq', REMINDERVERSE_EMAILUSER_NEVER);
        $mform->addElement('advcheckbox', 'suppressemail', get_string('suppressemail', 'versereminder'));
        $mform->disabledif('suppressemail', 'emailuser', 'eq', REMINDERVERSE_EMAILUSER_NEVER);
        $mform->addHelpbutton('suppressemail', 'suppressemail', 'versereminder');
        $truemods = get_fast_modinfo($COURSE->id);
        $mods = array();
        $mods[0] = get_string('nosuppresstarget', 'versereminder');
        foreach ($truemods->cms as $mod) {
            $mods[$mod->id] = $mod->name;
        }
        $mform->addElement('select', 'suppresstarget', get_string('suppresstarget', 'versereminder'), $mods);
        $mform->disabledif('suppresstarget', 'emailuser', 'eq', REMINDERVERSE_EMAILUSER_NEVER);
        $mform->disabledif('suppresstarget', 'suppressemail', 'notchecked');
        $mform->addHelpbutton('suppresstarget', 'suppresstarget', 'versereminder');
		$mform->addElement('header', 'minimumrestriction', get_string('minimumrestriction', 'versereminder'));
		$emaildelay = [];
        $periods = [];
        $periods[60] = get_string('minutes', 'versereminder');
        $periods[3600] = get_string('hours', 'versereminder');
        $periods[86400] = get_string('days', 'versereminder');
        $periods[604800] = get_string('weeks', 'versereminder');
        $emaildelay[] = $mform->createElement('text', 'emailperiodcount', '', ['class="emailperiodcount"']);
        $emaildelay[] = $mform->createElement('select', 'emailperiod', '', $periods);
        $mform->addGroup($emaildelay, 'emaildelay', get_string('emaildelay', 'versereminder'), [' '], false);
        $mform->addHelpButton('emaildelay', 'emaildelay', 'versereminder');
        $mform->setType('emailperiodcount', PARAM_INT);
        $mform->setDefault('emailperiodcount', '1');
        $mform->setDefault('emailperiod', '604800');
		$mform->disabledif('emaildelay', 'emailuser', 'neq', REMINDERVERSE_EMAILUSER_TIME);
		$mform->addElement('text', 'verseremindercount', get_string('verseremindercount', 'versereminder'), array('maxlength' => '3'));
        $mform->setType('verseremindercount', PARAM_INT);
        $mform->setDefault('verseremindercount', '1');
        $mform->addRule('verseremindercount', get_string('err_numeric', 'form'), 'numeric', '', 'client');
        $mform->addHelpButton('verseremindercount', 'verseremindercount', 'versereminder');
        $mform->disabledif('verseremindercount', 'emailuser', 'neq', REMINDERVERSE_EMAILUSER_TIME);
		$startdelay = [];
		$reference = [];
		$reference [USERENROLL] = get_string('userenroll', 'versereminder');
		$reference [COURSESTART] = get_string('coursestart', 'versereminder');
		$mform->addElement('select', 'referancedate', get_string('referancedate', 'versereminder'), $reference);
        $startdelay[] = $mform->createElement('text', 'startperiodcount', '', ['class="startperiodcount"']);
        $startdelay[] = $mform->createElement('select', 'startperiod', '', $periods);
        $mform->addGroup($startdelay, 'startdelay', get_string('startdelay', 'versereminder'), [' '], false);
        $mform->addHelpButton('startdelay', 'startdelay', 'versereminder');
		$mform->addHelpButton('referancedate', 'referancedate', 'versereminder');
        $mform->setType('startperiodcount', PARAM_INT);
        $mform->setDefault('startperiodcount', '1');
        $mform->setDefault('startperiod', '604800');
		$mform->addElement('date_time_selector', 'thirdpartyemails', get_string('versereminderclosedon', 'versereminder'),['optional' => true]);
	    $mform->addElement('header', 'manageremail', get_string('manageremail', 'versereminder'));
        $mform->addElement('selectyesno', 'managersemail', get_string('managersemail', 'versereminder'));
        $mform->addHelpButton('managersemail', 'managersemail', 'versereminder');
		$manageremaildelay = array();
		$managerperiods = array();
        $managerperiods[60] = get_string('minutes', 'versereminder');
        $managerperiods[3600] = get_string('hours', 'versereminder');
        $managerperiods[86400] = get_string('days', 'versereminder');
        $managerperiods[604800] = get_string('weeks', 'versereminder');
		$manageremaildelay[] = $mform->createElement('text', 'manageremailperiodcount', '', array('class=manageremailperiodcount'));
        $manageremaildelay[] = $mform->createElement('select', 'manageremailperiod', '', $managerperiods);
		$manageremailfrequency[] = $mform->createElement('text', 'manageremailfrequencyperiodcount', '', array('class=manageremailperiodcount'));
        $manageremailfrequency[] = $mform->createElement('select', 'manageremailfrequencyperiod', '', $managerperiods);
        $mform->addGroup($manageremaildelay, 'manageremaildelay', get_string('manageremaildelay', 'versereminder'), array(' '), false);
		$mform->addGroup($manageremailfrequency, 'manageremailfrequencydelay', get_string('manageremailfrequencydelay', 'versereminder'), array(' '), false);
        $mform->addHelpButton('manageremaildelay', 'manageremaildelay', 'versereminder');
        $mform->setType('manageremailperiodcount', PARAM_INT);
        $mform->setDefault('manageremailperiodcount', '1');
        $mform->setDefault('manageremailperiod', '604800');
		$mform->addHelpButton('manageremailfrequency', 'manageremailfrequency', 'versereminder');
        $mform->setType('manageremailfrequencyperiodcount', PARAM_INT);
        $mform->setDefault('manageremailfrequencyperiodcount', '1');
        $mform->setDefault('manageremailfrequencyperiod', '604800');
		$mform->addElement('header', 'comanagermail', get_string('comanagermail', 'versereminder'));
        $mform->addElement('selectyesno', 'comanagersemail', get_string('comanagersemail', 'versereminder'));
        $mform->addHelpButton('comanagersemail', 'comanagersemail', 'versereminder');
		$comanagersemaildelay = array();
		$comanagersperiods = array();
        $comanagersperiods[60] = get_string('minutes', 'versereminder');
        $comanagersperiods[3600] = get_string('hours', 'versereminder');
        $comanagersperiods[86400] = get_string('days', 'versereminder');
        $comanagersperiods[604800] = get_string('weeks', 'versereminder');
		$comanageremaildelay[] = $mform->createElement('text', 'comanagersemailperiodcount', '', array('class=comanagersemailperiodcount'));
        $comanageremaildelay[] = $mform->createElement('select', 'comanageremailperiod', '', $comanagersperiods);
		$comanageremailfrequency[] = $mform->createElement('text', 'comanageremailfrequencyperiodcount', '', array('class=comanagersemailperiodcount'));
        $comanageremailfrequency[] = $mform->createElement('select', 'comanageremailfrequencyperiod', '', $comanagersperiods);
        $mform->addGroup($comanageremaildelay, 'comanageremaildelay', get_string('comanagersemaildelay', 'versereminder'), array(' '), false);
		$mform->addGroup($comanageremailfrequency, 'comanagersemailfrequencydelay', get_string('comanagersemailfrequencydelay', 'versereminder'), array(' '), false);
        $mform->addHelpButton('comanagersemaildelay ', 'comanagersemaildelay', 'versereminder');
        $mform->setType('comanagersemailperiodcount', PARAM_INT);
        $mform->setDefault('comanagersemailperiodcount', '1');
        $mform->setDefault('comanageremailperiod', '604800');
		$mform->addHelpButton('comanageremailfrequency', 'comanageremailfrequency', 'versereminder');
        $mform->setType('comanageremailfrequencyperiodcount', PARAM_INT);
        $mform->setDefault('comanageremailfrequencyperiodcount', '1');
        $mform->setDefault('comanageremailfrequencyperiod', '604800');
		
        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();
       
        // Hide some elements not relevant to this activity (student visibility)
        if ($mform->elementExists('visible')) {
            $mform->removeElement('visible');
            $mform->addElement('hidden', 'visible', 0);
            $mform->setType('visible', PARAM_INT);
            if ($mform->elementExists('visibleoncoursepage')) {
                $mform->removeElement('visibleoncoursepage');
            }
            $mform->addElement('hidden', 'visibleoncoursepage', 1);
            $mform->setType('visibleoncoursepage', PARAM_INT);
        }
        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * Load in existing data as form defaults
     *
     * @param stdClass|array $toform object or array of default values
     */
    public function set_data($toform) {
        global $CFG;
		if (!empty($toform->duration)) {
            list ($periodcount, $period) = versereminder_get_readable_duration($toform->duration);
            $toform->period = $period;
            $toform->periodcount = $periodcount;
            unset($toform->duration);
        }
        if (!empty($toform->emaildelay)) {
            list ($periodcount, $period) = versereminder_get_readable_duration($toform->emaildelay);
            $toform->emailperiod = $period;
            $toform->emailperiodcount = $periodcount;
            unset($toform->emaildelay);
        }
		//manager
		if(!empty($toform->manageremaildelay)){
			list ($managerperiodcount, $managerperiod) = versereminder_get_readable_duration($toform->manageremaildelay);
            $toform->manageremailperiod = $managerperiod;
            $toform->manageremailperiodcount = $managerperiodcount;
            unset($toform->manageremaildelay);
		}
		if(!empty($toform->manageremailfrequency)){
			list ($manageremailfrequencyperiodcount, $managerfrequencyperiod) = versereminder_get_readable_duration($toform->manageremailfrequency);
            $toform->manageremailfrequencyperiod = $managerfrequencyperiod;
            $toform->manageremailfrequencyperiodcount = $manageremailfrequencyperiodcount;
            unset($toform->manageremailfrequency);
		}
		//partner
		if(!empty($toform->comanagersemaildelay)){
			list ($comanagersemailperiodcount, $comanageremailperiod) = versereminder_get_readable_duration($toform->comanagersemaildelay);
            $toform->comanageremailperiod = $comanageremailperiod;
            $toform->comanagersemailperiodcount = $comanagersemailperiodcount;
            unset($toform->comanagersemaildelay);
		}
		if(!empty($toform->comanagersemailfrequency)){
			list ($comanageremailfrequencyperiodcount, $comanageremailfrequencyperiod) = versereminder_get_readable_duration($toform->comanagersemailfrequency);
            $toform->comanageremailfrequencyperiod = $comanageremailfrequencyperiod;
            $toform->comanageremailfrequencyperiodcount = $comanageremailfrequencyperiodcount;
            unset($toform->comanagersemaildelay);
		}
		
		if($toform->referancedate){
			list ($startperiodcount, $startperiod) = versereminder_get_readable_duration($toform->startdelay);
            $toform->startperiod = $startperiod;
            $toform->startperiodcount = $startperiodcount;
            unset($toform->manageremaildelay);
		}
        if (!isset($toform->emailcontent)) {
            $toform->emailcontent = get_string('emailcontentdefaultvalue', 'versereminder');
        }
        if (!isset($toform->emailcontentformat)) {
            $toform->emailcontentformat = 1;
        }
		if (!isset($toform->manageremailcontentformat)) {
            $toform->manageremailcontentformat = 1;
        }
        $toform->emailcontent = array('text' => $toform->emailcontent, 'format' => $toform->emailcontentformat);
        
		if (!isset($toform->manageremailcontent)) {
            $toform->manageremailcontent = get_string('manageremailcontentdefaultvalue', 'versereminder');
        }
        $toform->manageremailcontent = array('text' => $toform->manageremailcontent, 'format' => 1);
        
		
		if (!isset($toform->emailcontentthirdparty)) {
            $toform->emailcontentthirdparty = get_string('emailcontentthirdpartydefaultvalue', 'versereminder');
        }
        if (!isset($toform->emailcontentthirdpartyformat)) {
            $toform->emailcontentthirdpartyformat = 1;
        }
        $toform->emailcontentthirdparty = array('text' => $toform->emailcontentthirdparty,
                                                'format' => $toform->emailcontentthirdpartyformat);
        if (empty($toform->suppresstarget)) {
            $toform->suppressemail = 0;
        } else {
            $toform->suppressemail = 1;
        }
        $toform->completion = 0;
        $toform->visible = 0;
        $result = parent::set_data($toform);
        return $result;
    }

    public function get_data() {
        global $CFG;
        $istotara = true;
		
        $fromform = parent::get_data();
        if (!empty($fromform)) {
            $fromform->completion = 0;
            $fromform->visible = 0;
            if (isset($fromform->period) && isset($fromform->periodcount)) {
                $fromform->duration = $fromform->period * $fromform->periodcount;
            }
			unset($fromform->period);
            unset($fromform->periodcount);
			//minimum delay
			if($fromform->referancedate){
				$fromform->startdelay = $fromform->startperiod * $fromform->startperiodcount;
			}
			unset($fromform->startperiod);
            unset($fromform->startperiodcount);
			
            if (isset($fromform->emailperiod) && isset($fromform->emailperiodcount)) {
                $fromform->emaildelay = $fromform->emailperiod * $fromform->emailperiodcount;
            }
            if (empty($fromform->emaildelay) || $fromform->emaildelay < 300) {
                $fromform->emaildelay = 300;
            }
			unset($fromform->emailperiod);
            unset($fromform->emailperiodcount);
			//manager email delay
			if (isset($fromform->manageremailperiod) && isset($fromform->manageremailperiodcount)) {
                $fromform->manageremaildelay = $fromform->manageremailperiod * $fromform->manageremailperiodcount;
            }
            if (empty($fromform->manageremaildelay) || $fromform->manageremaildelay < 300) {
                $fromform->manageremaildelay = 86400;
            }
			if(!$fromform->managersemail){
				$fromform->manageremaildelay = 0;
			}
			unset($fromform->manageremailperiod);
            unset($fromform->manageremailperiodcount);
			//manager email frequency
			if (isset($fromform->manageremailfrequencyperiod) && isset($fromform->manageremailfrequencyperiodcount)) {
                $fromform->manageremailfrequency = $fromform->manageremailfrequencyperiod * $fromform->manageremailfrequencyperiodcount;
            }
            if (empty($fromform->manageremaildelay) || $fromform->manageremaildelay < 300) {
                $fromform->manageremailfrequency = 3600;
            }
			if(!$fromform->managersemail){
				$fromform->manageremailfrequency = 0;
			}
			unset($fromform->manageremailfrequencyperiod);
            unset($fromform->manageremailfrequencyperiodcount);
			//partner email frequency
			if (isset($fromform->comanageremailfrequencyperiod) && isset($fromform->comanageremailfrequencyperiodcount)) {
                $fromform->comanagersemailfrequency = $fromform->comanageremailfrequencyperiod * $fromform->comanageremailfrequencyperiodcount;
            }
            if (empty($fromform->manageremaildelay) || $fromform->manageremaildelay < 300) {
                $fromform->comanagersemailfrequency = 3600;
            }
			if(!$fromform->managersemail){
				$fromform->comanagersemailfrequency = 0;
			}
			// unset($fromform->comanageremailfrequencyperiod);
            // unset($fromform->comanageremailfrequencyperiodcount);
			
			//partner email delay
            if (isset($fromform->comanageremailperiod) && isset($fromform->comanagersemailperiodcount)) {
                $fromform->comanagersemaildelay = $fromform->comanageremailperiod * $fromform->comanagersemailperiodcount;
            }
            if (empty($fromform->comanagersemaildelay) || $fromform->comanagersemaildelay < 300) {
                $fromform->comanagersemaildelay = 86400;
            }
			if(!$fromform->comanagersemail){
				$fromform->comanagersemaildelay = 0;
			}
			$fromform->emailcontentformat = $fromform->emailcontent['format'];
            $fromform->emailcontent = $fromform->emailcontent['text'];
			$fromform->manageremailcontentformat = $fromform->manageremailcontent['format'];
            $fromform->manageremailcontent = $fromform->manageremailcontent['text'];
			// unset($fromform->comanageremailperiod);
            // unset($fromform->comanagersemailperiodcount);
            // $fromform->emailcontentthirdpartyformat = $fromform->emailcontentthirdparty['format'];
            // $fromform->emailcontentthirdparty = $fromform->emailcontentthirdparty['text'];
			
        }
        return $fromform;
    }
    /**
     *  Add custom completion rules for reminderverse.
     *
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules() {
        $mform =& $this->_form;
        $periods = array();
        $periods[60] = get_string('minutes', 'versereminder');
        $periods[3600] = get_string('hours', 'versereminder');
        $periods[86400] = get_string('days', 'versereminder');
        $periods[604800] = get_string('weeks', 'versereminder');
        $duration[] = &$mform->createElement('text', 'periodcount', '', array('class="periodcount"'));
        
		$mform->setType('periodcount', PARAM_INT);
        $duration[] = &$mform->createElement('select', 'period', '', $periods);
        $mform->addGroup($duration, 'duration', get_string('reminderverseduration', 'versereminder'), array(' '), false);
        $mform->addHelpButton('duration', 'duration', 'versereminder');
        $mform->setDefault('periodcount', '1');
        $mform->setDefault('period', '604800');
        return array('duration');
    }

    /**
     * A custom completion rule is enabled by reminderverse.
     *
     * @param array $data Input data (not yet validated)
     * @return bool True if one or more rules is enabled, false if none are;
     *   default returns false
     */
    public function completion_rule_enabled($data) {
        return false;
    }

    /**
     * Perform validation on the settings form
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
		
        $errors = parent::validation($data, $files);

        return $errors;
    }
}
