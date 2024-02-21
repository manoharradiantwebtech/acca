<?php

defined('MOODLE_INTERNAL') || die;

class local_nse_renderable implements renderable {
	public $page;

    public $perpage;

    public $url;

    public $order;
	
	public $queryformat;
	
	public $showselectorform;
	
	public $shownserecord;
	
	public $is_download;

	public function __construct($courseid,$choosequery,$url = "",$action = 'pending',$nseformat='',$filter,$page = 0, $perpage = 30, $order = "updateddate ASC") {
        global $PAGE;
        if (empty($url)) {
            $url = new moodle_url($PAGE->url);
        } else {
            $url = new moodle_url($url);
        }
		$this->action = $action;
        $this->page = $page;
        $this->perpage = $perpage;
        $this->url = $url;
		$this->shownserecord = 1;
		$this->showselectorform = $filter;
        $this->order = $order;
		$this->courseid = $courseid;
		$this->queryformat = $nseformat;
		$this->is_download = ($nseformat)?1:0;
    }
	
	public function setup_table() {
        
        $filter = new \stdClass();
		$filter->action = $this->action;
        $filter->orderby = $this->order;
		$filter->courseid = $this->courseid;
		$filter->is_download = $this->is_download;
        $this->tablense = new local_nse_tablense('local_nse', $filter);
        $this->tablense->define_baseurl($this->url);
        $this->tablense->is_downloadable(true);
        $this->tablense->show_download_buttons_at(array(TABLE_P_BOTTOM));
		
		
    }

	public function get_course_list() {
        global $DB, $SITE;

        $courses = array();
        $sitecontext = context_system::instance();
        $numcourses = $DB->count_records("course");
		
        if ($numcourses < COURSE_MAX_COURSES_PER_DROPDOWN && !$this->showcourses) {
            $this->showcourses = 1;
        }
        // Check if course filter should be shown.
        // if (has_capability('report/log:view', $sitecontext) && $this->showcourses) {
			
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
        // }
        return $courses;
    }
	public function local_selector_form(local_nse_renderable $nselog) {
		echo html_writer::start_div("text-right");
		echo html_writer::link(new moodle_url('/local/nse/edit.php',[]), get_string('addnsecourse','local_nse'),['class' => 'btn btn-secondary']);
		echo html_writer::end_div();
        echo html_writer::start_tag('form', array('class' => 'nseselecform', 'action' => $nselog->url, 'method' => 'get'));
        echo html_writer::start_div();
        echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'filter', 'value' => '1'));
        echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'shownserecord', 'value' => $nselog->shownserecord));
        $course = $nselog->get_course_list();
        if ($nselog->shownserecord) {
			echo html_writer::label(get_string('selctauser'), 'menuuser', false, array('class' => 'accesshide'));
			echo html_writer::select($course, "courseid", $nselog->courseid);
        }
		echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('search','local_cperecord'), 'class' => 'btn btn-secondary'));
        echo html_writer::end_div();
        echo html_writer::end_tag('form');
    }
	public function get_action_options(){
		return [
			'2' => get_string('all','local_nse'),
			'1' => get_string('replyed','local_nse'),
			'0' => get_string('pending','local_nse'),
		];
	}
	public function get_date_options() {
        global $SITE;
        $strftimedate = get_string("strftimedate");
        $strftimedaydate = get_string("strftimedaydate");
		$timenow = time(); // GMT.
        $timemidnight = usergetmidnight($timenow);
        $dates = array("$timemidnight" => get_string("today").", ".userdate($timenow, $strftimedate) );
        $course = $SITE;
        if (!empty($this->course)) {
            $course = $this->course;
        }
        if (!$course->startdate or ($course->startdate > $timenow)) {
            $course->startdate = $course->timecreated;
        }
        $numdates = 1;
        while ($timemidnight > $course->startdate and $numdates < 365) {
            $timemidnight = $timemidnight - 86400;
            $timenow = $timenow - 86400;
            $dates["$timemidnight"] = userdate($timenow, $strftimedaydate);
            $numdates++;
        }
        return $dates;
    }
	public function download() {
        $filename = 'nsecourse_' .  userdate(time(), get_string('backupnameformat', 'langconfig'), 99, false);
        $this->tablense->is_downloading($this->queryformat, $filename,'Sheet1');
        $this->tablense->out($this->perpage, false);
    }
}