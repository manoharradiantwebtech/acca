<?php

defined('MOODLE_INTERNAL') || die;
class escalate_task{
	
	function alert_escalate(){
	    global $CFG, $DB;
		
	   $settings = get_config('local_activityalert');
	   if(!$settings->active){
		 exit();  
	   }
	   $sql = " SELECT u.id as userid
		,u.username AS UserName
		,rn.shortname AS RoleName
		,c.fullname AS CourseName
		,c.id as CourseId
		,u.lastname AS LastName
		,u.firstname AS FirstName
		,u.email AS Email
		,c.shortname AS CourseShortname
		,c.timecreated AS CourseStart
		,ul.timeaccess AS LastAccess
		,ah.id as alerthistoryid
		,ah.datetime as lastremind
	 FROM {role_assignments} AS r
	   JOIN {user} AS u on r.userid = u.id
	   JOIN {role} AS rn on r.roleid = rn.id
	   JOIN {context} AS ctx on r.contextid = ctx.id
	   JOIN {course} AS c on ctx.instanceid = c.id
	   JOIN {user_lastaccess} AS ul on ul.courseid = c.id AND ul.userid = u.id 
	   LEFT JOIN {alerthistory} AS ah on ah.courseid = c.id AND ah.userid = u.id 
	   WHERE ul.timeaccess < :priordate AND (ah.datetime IS NULL OR ah.datetime <:reminderdate)";
	   $reminder_befor = time() - $settings->duration - 10;
	   $param = ['priordate'=>$reminder_befor,'reminderdate'=>$reminder_befor];
	   $result = $DB->get_records_sql($sql,$param);
		if(count($result)){
			$supportuser = core_user::get_support_user();
			foreach($result as $row){
				$messagehtml = self::render_email_text($settings->alerttext,$row);
				if(email_to_user($row->userid, $supportuser, 'Reminder : '.$row->coursename, '', $messagehtml)){
					if($row->alerthistoryid){
						$alerthistory = $DB->get_record('alerthistory',['id'=>$row->alerthistoryid]);
						$alerthistory->no_of_email += 1;
						$alerthistory->datetime = time();
						$DB->update_record('alerthistory', $alerthistory);
					}else{
						$alerthistory = new stdClass();
						$alerthistory->userid = $row->userid;
						$alerthistory->courseid = $row->courseid;
						$alerthistory->no_of_email = 1;
						$alerthistory->datetime = time();
						$DB->insert_record('alerthistory', $alerthistory);
					}
				}
			}
		}
   }
	function render_email_text($html,$usersrow){
		
		$templatevars = [
			'/{{UserName}}/' => $usersrow->username,
			'/{{FirstName}}/' => $usersrow->firstname,
			'/{{LastName}}/' => $usersrow->lastname,
			'/{{Email}}/' => $usersrow->email,
			'/{{CourseId}}/' => $usersrow->courseid,
			'/{{CourseName}}/' => $usersrow->coursename,
			'/{{CourseShortname}}/' => $usersrow->courseshortname,
			'/{{LastAccess}}/' => userdate($usersrow->lastaccess, get_string('strfdatetime','local_activityalert'))
		];
		$patterns = array_keys($templatevars);
		$replacements = array_values($templatevars);
		return preg_replace($patterns, $replacements,$html);
	}
	
}


