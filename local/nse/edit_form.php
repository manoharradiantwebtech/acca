<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

class nse_edit_form extends moodleform {
	function definition() {
        global $CFG, $PAGE;
        $mform    = $this->_form;
		$mform->addElement('header','general', get_string('heading', 'local_nse'));
		$mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
		$course_list = $this->get_course_list();
		$mform->addElement('select', 'courseid', get_string('courseid', 'local_nse'), $course_list);
		$mform->addElement('editor', 'description', get_string('description', 'local_nse'));
		$mform->setType('description', PARAM_RAW);
		$amount_type = ['article '=>'Article ','course'=>'Course','video'=>'Video'];
		$mform->addElement('select', 'content_type', get_string('content_type', 'local_nse'), $amount_type);
		$mform->addElement('duration', 'courseduration', get_string('courseduration', 'local_nse'));
		$amount_type = ['INR'=>'INR','USD'=>'USD'];
		$mform->addElement('select', 'bhav_type', get_string('amounttype', 'local_nse'), $amount_type);
		$mform->addElement('text', 'bhav', get_string('amount', 'local_nse'), ['size' => '10']);
        $mform->addRule('bhav', null, 'required', null, 'client');
        $mform->setType('bhav', PARAM_INT);
		// $options = ['multiple' => false];
        // $mform->addElement('cohort', 'cohortid', get_string('cohort', 'cohort'), $options);
		// $mform->addRule('cohortid', get_string('required'), 'required', null, 'cohortid');
		$mform->addElement('advcheckbox', 'isactive', get_string('isactive', 'local_nse'));
		$buttonarray = array();
        $classarray = array('class' => 'form-submit');
        $buttonarray[] = &$mform->createElement('submit', 'saveanddisplay', get_string('savechanges'), $classarray);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
	}
	
	function definition_after_data() {
        global $DB;
        $mform = $this->_form;
    }
	function set_data($default_values){
		if (!is_object($default_values)) {
            $default_values = (object)$default_values;
        }
		if(!is_array($default_values->description)){
			$default_values->description = ['text'=>$default_values->description,'format'=>1];
		}
		parent::set_data($default_values);
	}
	public function get_course_list() {
        global $DB, $SITE;
        $courses = array();
        $sitecontext = context_system::instance();
        $numcourses = $DB->count_records("course");
         if ($courserecords = $DB->get_records("course", null, "fullname", "id,shortname,fullname,category")) {
             foreach ($courserecords as $course) {
				if(get_course_display_name_for_list($course) != NULL){
					if ($course->id == SITEID) {
						$courses[$course->id] = format_string($course->fullname) . ' (' . get_string('site') . ')';
					} else {
						$courses[$course->id] = format_string(get_course_display_name_for_list($course));
					}
				}
             }
         }
         core_collator::asort($courses);
        return $courses;
    }
}