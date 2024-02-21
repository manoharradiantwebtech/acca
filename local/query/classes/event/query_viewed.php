<?php

namespace local_query\event;

defined('MOODLE_INTERNAL') || die();

class query_viewed  {
	
	public static function get_name() {
        return get_string('eventlocalviewed', 'local_query');
    }
	
	public function get_description() {
        return "The user with id '$this->userid' viewed the query for the user with id '$this->userid'.";
    }
	
	protected function get_legacy_querydata() {
        return array($this->userid, "User", " query ", "local/query/index.php?id=$this->userid", $this->userid);
    }
	
	public function get_url() {
        return new \moodle_url('/local/query/index.php', array('id' => $this->userid));
    }
	
	protected function validate_data() {
        parent::validate_data();
        
        if (!isset($this->other['date'])) {
            throw new \coding_exception('The \'date\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['modid'] = array('db' => 'course_modules', 'restore' => 'course_module');
        $othermapped['groupid'] = array('db' => 'groups', 'restore' => 'group');

        return $othermapped;
    }
}