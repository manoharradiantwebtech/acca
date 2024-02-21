<?php

defined('MOODLE_INTERNAL') || die();
function local_query_get_string($string) {
    $title = $string;
    $text = explode(',', $string, 2);
    if (count($text) == 2) {
        if (clean_param($text[0], PARAM_STRINGID) !== '') {
           $title = get_string($text[0], $text[1]);
        }
    }
    return $title;
}
function local_query_answers($formdata,$ticket) {
    global $DB, $USER;
	if($ticket->random_id == $formdata->random_id){
		
		$eventdata = new \core\message\message();
		$eventdata->modulename = 'moodle';
		$eventdata->component = 'enrol_notificationeabc';
		$eventdata->name = 'notificationeabc_enrolment'; 
		$eventdata->userfrom = core_user::get_support_user();
		
		$query_text = new stdClass();
		$query_text->random_id = $formdata->random_id;
		$query_text->description = $formdata->description;
		$query_text->userid = $USER->id;
		$query_text->timecreated = time();
		if($DB->insert_record('query_text', $query_text)){
			$ticket->updatedon = time();
			$DB->update_record('query', $ticket);
			$a = new stdClass();
			$a->replyurl = "https://lms.learningt.com/local/query/view.php?reqid=".$query_text->random_id;
			$a->description = $query_text->description;
			$a->requesterfullname = $USER->firstname .' '.$USER->lastname;
			$a->requesteremail = $USER->email;
			if($ticket->is_rply){
				$ticket = $DB->get_record('query_text', ['random_id'=>$ticket->random_id]);
				if($ticket){
					$messagehtml = get_string('reply_email', 'local_query',$a);
					// $requester = $DB->get_record('user', ['id'=>$ticket->userid]);
                    
				$messagehtml = str_replace(['<p>', '</p>'], ["\n", ''], $messagehtml);
				$eventdata->fullmessage = strip_tags($messagehtml); // Set the plain text content
				$eventdata->fullmessageformat = 1;
				$eventdata->fullmessagehtml = $messagehtml;
				$eventdata->smallmessage = '';
				$eventdata->userto = $ticket->userid;
				$eventdata->subject = "Query raise";
				message_send($eventdata);
					// email_to_user($requester, $supportuser, "Query raise", '', $messagehtml, '', '', true);
				}
			}else{
				$messagehtml = get_string('admin_email', 'local_query',$a);
				// $supportuser = core_user::get_support_user();
				$messagehtml = str_replace(['<p>', '</p>'], ["\n", ''], $messagehtml);
				$eventdata->fullmessage = strip_tags($messagehtml); // Set the plain text content
				$eventdata->fullmessageformat = 1;
				$eventdata->fullmessagehtml = $messagehtml;
				$eventdata->smallmessage = '';
				$eventdata->userto = 2;
				$eventdata->subject = "Query raise";
				message_send($eventdata);
				// email_to_user($supportuser, $supportuser, "Query raise", '', $messagehtml, '', '', true);
			}
		}
	}
}
function raise_query($insert){
	global $USER,$DB;
	$query = new stdClass();
	$query->name = $insert->fullname;
	if($insert->fullname == NULL){
		$query->name = $USER->firstname . ' ' . $USER->lasttname;
	}
	$query->phone = $insert->phone;
	if($insert->phone == NULL){
		$query->phone = $USER->phone;
	}
	$query->email = $insert->email;
	if($insert->email == NULL OR !filter_var($insert->email, FILTER_VALIDATE_EMAIL)){
		$query->email = $USER->email;
	}
	$query->random_id = mt_rand();
	$query->createdon = time();
	$query->updatedon = time();
	$query->is_rply =0;

	if($DB->insert_record('query', $query)){
		$supportuser = core_user::get_support_user();
		return sendemailTouser($supportuser,'subject','email test');
	}
	return false;
}
function sendemailTouser($user,$subject='',$msg=''){
	return true;
}

function get_user_role_id($userid) {
    global $DB;
    $sql = "SELECT ra.roleid as roleid
    FROM {user} u
    JOIN {role_assignments} ra ON ra.userid = u.id
    JOIN {context} c ON ra.contextid = c.id
    JOIN {role} r ON ra.roleid = r.id
    WHERE u.id = :userid
    AND (r.shortname = 'editingteacher' OR r.shortname = 'teacher')";
    $roleid =  $DB->get_record_sql($sql, array('userid' => $userid));
    return $roleid;
}
