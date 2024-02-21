<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/moodlelib.php');
require_login();
$message = required_param('message',PARAM_RAW);
$name = required_param('fullname',PARAM_RAW);
$email = required_param('email',PARAM_RAW);
$phone = required_param('phone',PARAM_RAW);
$page = required_param('page',PARAM_RAW);
// Ensure the context is set properly 
$context = context_system::instance(); 
// Set the default context to system context
if(!empty($message)){
	raise_query(clean_param($name,PARAM_CLEAN),clean_param($page,PARAM_CLEAN),clean_param($message,PARAM_CLEAN),clean_param($phone,PARAM_CLEAN),clean_param($email,PARAM_CLEAN));
}else if(empty($message)){
	echo "Message required";
}

function raise_query($name,$page,$message,$phone,$email){
	global $DB,$USER,$CFG;
    if($name == ''){
		$name = $USER->firstname . ' ' .$USER->lastname;
	}
	if($email == ''){
		$email = $USER->email ;
	}
	if($phone == ''){
		$phone = $USER->phone1 ;
	}
    if(strpos($page, '/course/view.php') !== false) {
        $queryString = parse_url($page, PHP_URL_QUERY);
        parse_str($queryString, $params);
        $course = $DB->get_record('course', array('id'=> $params['id']));
        $page_name = $course->shortname;
    } else {
        $page_name = 'Others';
    }
    //END Changes
	$query_email = new stdClass();
	$query_email->userid = $USER->id;
	$query_email->phone = $phone;
	$query_email->name = $name;
	$query_email->email = $email;
	$query_email->page_url = $page;
	$query_email->random_id = mt_rand();
	$query_email->createdon = time();
	$query_email->updatedon = time();
	$query_email->is_rply =0;
	$query_email->page_name = $page_name;
	if($DB->insert_record('query', $query_email)){
		$query_text = new stdClass();
		$query_text->random_id = $query_email->random_id;
                 $query_text->userid = $USER->id;
		$query_text->description = $message;
		$query_text->timecreated = $query_email->createdon;
		$DB->insert_record('query_text', $query_text);
		$supportuser = \core_user::get_support_user();
		$a = new stdClass;
		$a->replyurl = $CFG->wwwroot."/local/query/view.php?reqid=".$query_text->random_id;
		$a->description = $query_text->description;
		$a->requesterfullname = $USER->firstname .' '.$USER->lastname;
		$a->requesteremail = $USER->email;
		$messagehtml = get_string('admin_email', 'local_query',$a);
		
		$eventdata = new \core\message\message();
		$eventdata->modulename = 'moodle';
		$eventdata->component = 'enrol_notificationeabc';
		$eventdata->name = 'notificationeabc_enrolment'; 
		$eventdata->userfrom = $supportuser;
		$eventdata->fullmessage = strip_tags($messagehtml); // Set the plain text content
		$eventdata->fullmessageformat = 1;
		$eventdata->fullmessagehtml = $messagehtml;
		$eventdata->smallmessage = '';
		$eventdata->userto = 2;
		$eventdata->subject = "Query raise";
		
		if(message_send($eventdata)){
			echo "success";
		}else{
			echo "An error occurred, please try again later";
		}
	}else{
		echo "An error occurred, please try again later";
	}
}
?>