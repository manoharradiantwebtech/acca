<?php

defined('MOODLE_INTERNAL') || die;

class local_query_renderable implements renderable {
	public $page;

    public $perpage;

    public $url;

    public $date;

    public $userid;

    public $showusers;

    public $showselectorform;

    public $order;

    public $tablequery;
	
	public $queryformat;
	
	public $is_download;

	public function __construct($userid = 0, $showusers = false, $showquery = true, $showselectorform = true, $url = "", $date = 0,$action = 'pending',
        $queryformat='showashtml',$page = 0, $perpage = 30, $order = "updatedon ASC") {
        global $PAGE;
        if (empty($url)) {
            $url = new moodle_url($PAGE->url);
        } else {
            $url = new moodle_url($url);
        }
        $this->userid = $userid;
        $this->createdon = $date;
		$this->action = $action;
        $this->page = $page;
        $this->perpage = $perpage;
        $this->url = $url;
        $this->order = $order;
        $this->showusers = $showusers;
        $this->showquery = $showquery;
		$this->queryformat = $queryformat;
        $this->showselectorform = $showselectorform;
		$this->is_download = ($queryformat)?1:0;
    }
	
	public function setup_table() {
        
        $filter = new \stdClass();
        $filter->userid = $this->userid;
        $filter->createdon = $this->createdon;
		$filter->action = $this->action;
        $filter->orderby = $this->order;
		$filter->is_download = $this->is_download;
        $this->tablequery = new local_query_table_query('local_query', $filter);
        $this->tablequery->define_baseurl($this->url);
        $this->tablequery->is_downloadable(true);
        $this->tablequery->show_download_buttons_at(array(TABLE_P_BOTTOM));
		
		
    }

	public function get_user_list() {
        global $CFG, $SITE;
        $courseid = $SITE->id;
        if (!empty($this->course)) {
            $courseid = $this->course->id;
        }
        $context = context_course::instance($courseid);
        $limitfrom = empty($this->showusers) ? 0 : '';
        $limitnum  = empty($this->showusers) ? COURSE_MAX_USERS_PER_DROPDOWN + 1 : '';
        $courseusers = get_enrolled_users($context, '', 0, 'u.id, ' . get_all_user_name_fields(true, 'u'),null, $limitfrom, $limitnum);
        if (count($courseusers) < COURSE_MAX_USERS_PER_DROPDOWN && !$this->showusers) {
            $this->showusers = 1;
        }
        $users = array();
        if ($this->showusers) {
            if ($courseusers) {
                foreach ($courseusers as $courseuser) {
                     $users[$courseuser->id] = fullname($courseuser, has_capability('moodle/site:viewfullnames', $context));
                }
            }
            $users[$CFG->siteguest] = get_string('guestuser');
        }
        return $users;
    }
	public function get_action_options(){
		return [
			'2' => get_string('all','local_query'),
			'1' => get_string('replyed','local_query'),
			'0' => get_string('pending','local_query'),
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
        $filename = 'query_' .  userdate(time(), get_string('backupnameformat', 'langconfig'), 99, false);
        $this->tablequery->is_downloading($this->queryformat, $filename);
        $this->tablequery->out($this->perpage, false);
    }
}