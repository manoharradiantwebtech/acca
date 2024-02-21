<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir."/completionlib.php");
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->libdir.'/gradelib.php');
define('REMINDERVERSE_EMAILUSER_NEVER', 0);
define('REMINDERVERSE_EMAILUSER_COMPLETION', 1);
define('REMINDERVERSE_EMAILUSER_TIME', 2);
define('REMINDERVERSE_EMAILUSER_RESERVED1', 3);
define('REMINDERVERSE_RECIPIENT_USER', 0);
define('REMINDERVERSE_RECIPIENT_MANAGER', 1);
define('REMINDERVERSE_RECIPIENT_BOTH', 2);
define('USERENROLL', 1);
define('COURSESTART', 2);
function versereminder_add_instance($versereminder) {
    global $DB;

    $versereminder->timecreated = time();
    if (!$versereminder->suppressemail) {
        $versereminder->suppresstarget = 0;
    }
    unset($versereminder->suppressemail);
	$course = $DB->get_record('course', array('id' => $versereminder->course));
    if (empty($course->enablecompletion)) {
        $coursecontext = context_course::instance($course->id);
        if (has_capability('moodle/course:update', $coursecontext)) {
            $data = array('id' => $course->id, 'enablecompletion' => '1');
            $DB->update_record('course', $data);
            rebuild_course_cache($course->id);
        }
    }
    return $DB->insert_record('versereminder', $versereminder);
}

function versereminder_update_instance($versereminder) {
    global $DB;
    $versereminder->timemodified = time();
    $versereminder->id = $versereminder->instance;
    if (!$versereminder->suppressemail) {
        $versereminder->suppresstarget = 0;// No target to be set.
    }
    unset($versereminder->suppressemail);
    $result = $DB->update_record('versereminder', $versereminder);
    return $result;
}

function versereminder_delete_instance($id) {
    global $DB;
    if (! $versereminder = $DB->get_record('versereminder', array('id' => $id))) {
        return false;
    }
    $result = true;
    if (! $DB->delete_records('versereminder_inprogress', array('versereminder' => $versereminder->id))) {
        $result = false;
    }
    if (! $DB->delete_records('versereminder', array('id' => $versereminder->id))) {
        $result = false;
    }
    return $result;
}

function versereminder_user_outline($course, $user, $mod, $versereminder) {
    return;
}

function versereminder_user_complete($course, $user, $mod, $versereminder) {
    return false;
}

function versereminder_get_completion_state($course, $cm, $userid, $type) {
    global $DB;
    if ($completion = $DB->get_record('course_modules_completion', array('coursemoduleid' => $cm->id, 'userid' => $userid))) {
        return $completion->completionstate == COMPLETION_COMPLETE_PASS;
    }
    return false;
}

function versereminder_print_recent_activity($course, $isteacher, $timestart) {
    return false;
}

function versereminder_crontask(){
	global $CFG, $DB;
	require_once($CFG->libdir."/completionlib.php");
    $timenow = time();
    $versereminderssql = "SELECT cm.id as id, cm.id as cmid, cm.availability, r.id as rid, r.course as courseid,
                    r.duration, r.emaildelay, r.managersemail,r.manageremaildelay,
					r.referancedate,r.startdelay,r.comanagersemail,r.comanagersemaildelay
                          FROM {versereminder} r
                    INNER JOIN {course_modules} cm on cm.instance = r.id
                          JOIN {modules} m on m.id = cm.module
                         WHERE m.name = 'versereminder' AND (r.thirdpartyemails <= 0 || r.thirdpartyemails > $timenow )
                      ORDER BY r.id ASC";
    $versereminders = $DB->get_recordset_sql($versereminderssql);
	
	if (!$versereminders->valid()) {
        mtrace("No versereminder instances found - nothing to do :)");
        return true;
    }
	foreach ($versereminders as $verseremindercm) {
        $startusers = versereminder_get_startusers($verseremindercm);
        $versereminderinprogress = new stdClass();
        $versereminderinprogress->versereminder = $verseremindercm->rid;
        $versereminderinprogress->completiontime = $timenow + $verseremindercm->duration;
		$versereminderinprogress->emailtime = $timenow;
        $userlist = array_keys($startusers);
        $newripcount = count($userlist); 
		if($verseremindercm->managersemail){
			$versereminderinprogress->manageremailtime = $timenow + $verseremindercm->manageremaildelay;
		}else{
			$versereminderinprogress->manageremailtime  = 0;
		}
		if($verseremindercm->comanagersemail){
			$versereminderinprogress->comanageremailtime = $timenow + $verseremindercm->comanagersemaildelay;
		}else{
			$versereminderinprogress->comanageremailtime = 0;
		}
        if (debugging('', DEBUG_DEVELOPER) || ($newripcount && debugging('', DEBUG_ALL))) {
            mtrace("Adding $newripcount versereminders-in-progress to versereminderid " . $verseremindercm->rid);
        }
        foreach ($userlist as $userid) {
            $versereminderinprogress->userid = $userid;
            $DB->insert_record('versereminder_inprogress', $versereminderinprogress);
        }
    }
    $versereminders->close();
	$versereminderssql = "SELECT r.id as id, 
							cm.id as cmid,
							r.emailcontent, 
							r.emailcontentformat, 
							r.emailsubject,cm.completion,
                            r.thirdpartyemails,
							r.emailuser, r.name, 
							r.suppresstarget, 
							r.verseremindercount,
							r.manageremailcontent,
							r.managersemail, 
							r.manageremailfrequency, 
							r.comanagersemail, 
							r.comanagersemailfrequency, 
							c.shortname as courseshortname,
                            c.fullname as coursefullname, 
							c.id as courseid, 
							r.emailrecipient, 
							r.emaildelay
                          FROM {versereminder} r
                    INNER JOIN {course_modules} cm ON cm.instance = r.id
                    INNER JOIN {course} c ON cm.course = c.id
                          JOIN {modules} m on m.id = cm.module
                         WHERE m.name = 'versereminder' AND (r.thirdpartyemails <= 0 || r.thirdpartyemails > $timenow )
                      ORDER BY r.id DESC";
	$versereminders = $DB->get_records_sql($versereminderssql);
	
	foreach($versereminders as $versereminder){
		$inprogresssql = 'SELECT ri.*
                        FROM {versereminder_inprogress} ri
                        JOIN {user} u ON u.id = ri.userid
                       WHERE ri.versereminder = :rid AND ri.emailtime <= :time AND u.deleted = 0 AND u.suspended = 0 AND ri.completed = 0';
		$inprogresses = $DB->get_recordset_sql($inprogresssql, array('rid'=>$versereminder->id,'time'=> $timenow));
		$course = $DB->get_record('course', ['id' => $versereminder->courseid]);
		$completion = new \completion_info($course);
		foreach ($inprogresses as $inprogress) {
			$send = true;
			$cm = get_fast_modinfo($versereminder->courseid)->get_cm($versereminder->cmid);
			$ainfomod = new \core_availability\info_module($cm);
			$information = '';
			if (!$ainfomod->is_available($information, false, $inprogress->userid)){
				$send = 0;
			}
			$context = context_module::instance($versereminder->cmid);
			if (!is_enrolled($context, $inprogress->userid, 'mod/versereminder:startversereminder', true)) {
				$DB->delete_records('versereminder_inprogress', array('id' => $inprogress->id));
				$send = 0;
			}
			if ($versereminder->suppresstarget && versereminder_check_target_completion($inprogress->userid, $versereminder->suppresstarget)) {
				$send = 0;
				$inprogress->completed = 1;
				if($versereminder->completion){
					$completion_record = $DB->get_record('course_modules_completion', array('coursemoduleid' => $versereminder->cmid,'userid' => $inprogress->userid));
					if($completion_record){
						$completion_record->completionstate = $versereminder->completion;
						$completion_record->timemodified = $timenow;
						$result = $DB->update_record('course_modules_completion', $completion_record);
					}else{
						$activitycompletion = new stdClass();
						$activitycompletion->coursemoduleid = $versereminder->cmid;
						$activitycompletion->completionstate = $versereminder->completion;
						$activitycompletion->timemodified = $timenow;
						$activitycompletion->userid = $inprogress->userid;
						$DB->insert_record('course_modules_completion', $activitycompletion);
					}				
				}
			}
			
			if($completion->is_course_complete($inprogress->userid)){
				$send = 0;
				$inprogress->completed = 1;
			}
			$inprogress->issend = $send;
			$DB->update_record('versereminder_inprogress', $inprogress);
		}
		versereminder_email_user($versereminder);
	}
	return true;
}

	
function versereminder_email_user($versereminder) {
    global $DB, $SITE, $CFG;
	
	$time = time();
	$timesend = time()+900;
    $istotara = true;
	$inprogress_sql = "SELECT u.id as userid,c.fullname,
						c.shortname,
						c.id as courseid,
						u.firstname,
						u.lastname,
						u.institution,
						u.city,
						u.department,
						u.email,
						um1.data as manager_email,
						um2.data as manager2_email,
						ri.*
					FROM {versereminder_inprogress} ri
					JOIN {versereminder} r ON ri.versereminder = r.id
					JOIN {course} c ON r.course = c.id
                    INNER JOIN {user} u ON ri.userid = u.id
                    JOIN {user_info_data} um1 ON um1.fieldid = 1 AND um1.userid = u.id
                    JOIN {user_info_data} um2 ON um2.fieldid = 2 AND um2.userid = u.id
                        WHERE ri.completed = 0 AND ri.issend = 1 AND u.suspended = 0 AND u.deleted = 0 AND ri.emailtime <= '$timesend'
						 AND ri.emailsent < $versereminder->verseremindercount AND ri.versereminder = $versereminder->id ORDER BY ri.id DESC";
	$inprogress_users = $DB->get_records_sql($inprogress_sql);
	$send_result = [];
	foreach($inprogress_users as $users){
		if($users->comanageremailtime <= $timesend && $users->comanageremailtime > 1 && $versereminder->comanagersemail == 1 && $users->manager2_email != NULL){
			$send_result['manager2'][strtolower($users->manager2_email)][]= $users;
		}elseif($users->manageremailtime <= $timesend && $users->manageremailtime > 1 && $versereminder->managersemail == 1 && $users->manager_email != NULL){
			$send_result['manager1'][strtolower($users->manager_email)][]= $users;
		}else{
			$send_result['self'][]= $users;
		}
	}
	if(isset($send_result['manager1']) && count($send_result['manager1'])){
		foreach($send_result['manager1'] as $key => $users){
			if($key != NULL){
				$cc = [];
				foreach($users as $user){
					$cc[]=$user->email;
				}
				$templateddetails = versereminder_template_variables($versereminder, $user);
				$plaintext = html_to_text($templateddetails['manageremailcontent']);
				if(sendEmailVerse($key,$SITE->shortname,$templateddetails['emailsubject'],$plaintext,$templateddetails['manageremailcontent'],$cc)){
					foreach($users as $user){
						$inprogress = $DB->get_record('versereminder_inprogress', ['id' => $user->id]);
						if($inprogress){
							$inprogress->manageremailtime = $time - 120 + $versereminder->manageremailfrequency;
							$inprogress->manageremailsent +=  1;
							$inprogress->emailtime = $time - 120 + $versereminder->emaildelay;
							$inprogress->emailsent += 1;
							$DB->update_record('versereminder_inprogress', $inprogress);
						}
					}
				}
			}
		}
	}
	if(isset($send_result['manager2']) && count($send_result['manager2'])){
		foreach($send_result['manager2'] as $key => $users){
			if($key != NULL){
				$cc = [];
				foreach($users as $user){
					$cc[]=$user->email;
				}
				$templateddetails = versereminder_template_variables($versereminder, $user);
				$plaintext = html_to_text($templateddetails['manageremailcontent']);
				if(sendEmailVerse($key,$SITE->shortname,$templateddetails['emailsubject'],$plaintext,$templateddetails['manageremailcontent'],$cc)){
					foreach($users as $user){
						$inprogress = $DB->get_record('versereminder_inprogress', ['id' => $user->id]);
						if($inprogress){
							$inprogress->comanageremailtime = $time - 120 + $versereminder->comanagersemailfrequency;
							$inprogress->comanageremailsent +=  1;
							$inprogress->emailtime = $time - 120 + $versereminder->emaildelay;
							$inprogress->emailsent += 1;
							$DB->update_record('versereminder_inprogress', $inprogress);
						}
					}
				}
			}
		}
	}
	if(isset($send_result['self']) && count($send_result['self'])){
		foreach($send_result['self'] as $user){
			$templateddetails = versereminder_template_variables($versereminder, $user);
			$plaintext = html_to_text($templateddetails['emailcontent']);
			if(sendEmailVerse($user->email,$SITE->shortname,$templateddetails['emailsubject'],$plaintext,$templateddetails['emailcontent'])){
				$inprogress = $DB->get_record('versereminder_inprogress', ['id' => $user->id]);
				if($inprogress){
					$inprogress->emailtime = $time - 120 + $versereminder->emaildelay;
					$inprogress->emailsent = $inprogress->emailsent + 1;
					$DB->update_record('versereminder_inprogress', $inprogress);
				}
			}
		}
	}
    return true;
}

function versereminder_template_variables($versereminder, $user) {
    $templatevars = [
        '/%courseshortname%/' => $versereminder->courseshortname,
        '/%coursefullname%/' => $versereminder->coursefullname,
        '/%courseid%/' => $versereminder->courseid,
        '/%userfirstname%/' => $user->firstname,
        '/%userlastname%/' => $user->lastname,
        '/%userid%/' => $user->id,
        '/%usercity%/' => $user->city,
        '/%userinstitution%/' => $user->institution,
        '/%userdepartment%/' => $user->department,
    ];
    $patterns = array_keys($templatevars);
    $replacements = array_values($templatevars);
    $replacementfields = ['emailsubject', 'emailcontent', 'manageremailcontent'];
    $results = array();
	foreach ($replacementfields as $field) {
        $results[$field] = preg_replace($patterns, $replacements, $versereminder->$field);
    }
    return $results;
}

function versereminder_get_participants($versereminderid) {
    return false;
}

function versereminder_scale_used($versereminderid, $scaleid) {
    $return = false;

    return $return;
}

function versereminder_scale_used_anywhere($scaleid) {
    return false;
}

function versereminder_install() {
    return true;
}

function versereminder_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'versereminderheader', get_string('modulenameplural', 'versereminder'));
	$mform->addElement('checkbox', 'reset_versereminder_current_data', get_string('deletelogs', 'versereminder'));
}

function versereminder_reset_course_form_defaults($course) {
    return array('reset_versereminder_current_data' => 0);
}


function versereminder_reset_userdata($data) {
    global $DB;
    $componentstr = get_string('modulenameplural', 'versereminder');
    $status = array();
    if (!empty($data->reset_versereminder_current_data)) {
        $verseremindersql = "SELECT ch.id
                       FROM {versereminder} ch
                       WHERE ch.course=?";
        $DB->delete_records_select('versereminder_inprogress', "versereminder IN ($verseremindersql)", array($data->courseid));
        $status[] = array('component' => $componentstr, 'item' => get_string('removeresponses', 'versereminder'), 'error' => false);
    }
    return $status;
}

function versereminder_get_startusers($versereminder) {
    global $DB;
    $context = context_module::instance($versereminder->cmid);
    $startusers = get_enrolled_users($context, 'mod/versereminder:startversereminder', 0, 'u.*', null, 0, 0, true);
	if($versereminder->referancedate){
		if($versereminder->referancedate == USERENROLL){
			$enrolluserim = implode(",",array_keys($startusers));
			if($enrolluserim){
				$enrollsql ="SELECT uem.*
				FROM {enrol} ue
				LEFT JOIN {user_enrolments} uem ON ue.id = uem.enrolid
				WHERE ue.courseid = :courseid
				AND uem.userid IN($enrolluserim)";
				$enrollment = $DB->get_records_sql($enrollsql, array('courseid' => $versereminder->courseid));
				foreach($enrollment as $en){
					$referencedate = $en->timecreated;
					if($en->timestart){
						$referencedate = $en->timestart;
					}
					if($referencedate + $versereminder->startdelay > time()){
						unset($startusers[$en->userid]);
					}
				}
			}
		}elseif($versereminder->referancedate == COURSESTART){
			$cmsql ="SELECT c.*
				FROM {course} c
				WHERE id = :courseid";
				$cm = $DB->get_records_sql($cmsql, array('courseid' => $versereminder->courseid));
				$referencedate = 0;
				if($cm->startdate){
					$referencedate = $cm->startdate;
				}
				if($referencedate + $versereminder->startdelay > time()){
					$startusers = [];
				}
		}
	}
	$alreadysql = "SELECT userid, userid as junk
                   FROM  {versereminder_inprogress}
                   WHERE versereminder = :rid";
    $alreadyusers = $DB->get_records_sql($alreadysql, array('rid' => $versereminder->rid));
	foreach ($alreadyusers as $auser) {
        if (isset($startusers[$auser->userid])) {
            unset($startusers[$auser->userid]);
        }
    }
	$cm = get_fast_modinfo($versereminder->courseid)->get_cm($versereminder->cmid);
    $ainfomod = new \core_availability\info_module($cm);
    foreach ($startusers as $startcandidate) {
        $information = '';
        if(empty($startcandidate->confirmed)) {
            unset($startusers[$startcandidate->id]);
            continue;
        }
        if (!$ainfomod->is_available($information, false, $startcandidate->id)){
            unset($startusers[$startcandidate->id]);
        }
    }
	foreach($startusers AS $activeuser){
		if($user->suspended || versereminder_check_target_completion($activeuser->id, $versereminder->suppresstarget)){
			unset($startusers[$activeuser->id]);
		}
	}
    return $startusers;
}


/**
 * Return the list of Moodle features this module supports
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function versereminder_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}

/**
 * Process an arbitary number of seconds, and prepare to display it as X minutes, or Y hours or Z weeks.
 *
 * @param int $duration FEATURE_xx constant for requested feature
 * @return array
 */
function versereminder_get_readable_duration($duration) {
    if ($duration < 300) {
        $period = 60;
        $periodcount = 5;
    } else {
        $periods = array(604800, 86400, 3600, 60);
        foreach ($periods as $period) {
            if ((($duration % $period) == 0) || ($period == 60)) {
                // Duration divides exactly into periods, or have reached the min. sensible period.
                $periodcount = floor((int)$duration / (int)$period);
                break;
            }
        }
    }
    return array($periodcount, $period); // Example 5, 60 is 5 minutes.
}

/**
 * Check if user has completed the named course moduleid
 * @param int $userid idnumber of the user to be checked.
 * @param int $targetcmid the id of the coursemodule we should be checking.
 * @return bool true if user has completed the target activity, false otherwise.
 */
 // function sendEmail($user, $from, $subject, $messagetext = '', $messagehtml = '', $cclist=array(), $bcclist=array()){
	 // return true;
 // }
function sendEmailVerse($toemail, $from, $subject, $messagetext = '', $messagehtml = '', $cclist=[], $bcclist=[]){
	
	global $CFG, $PAGE, $SITE;
	 $usetrueaddress = true;
	 $replyto = '';
	 $replytoname = '';
	 $attachment = '';
	 $attachname = '';
	 $usetrueaddress = true;
	 $replyto = '';
	 $replytoname = '';
	 $wordwrapwidth = 79;
    if ($toemail == NULL) {
        debugging('Can not send email to user without email', DEBUG_DEVELOPER);
        return false;
    }

    if (defined('BEHAT_SITE_RUNNING')) {
        return true;
    }
    if (!empty($CFG->noemailever)) {
        // Hidden setting for development sites, set in config.php if needed.
        debugging('Not sending email due to $CFG->noemailever config setting', DEBUG_NORMAL);
        return true;
    }

    if (email_should_be_diverted($toemail)) {
        $subject = "[DIVERTED {$toemail}] $subject";
        $toemail = $CFG->divertallemailsto;
    }


    if (!validate_email($toemail)) {
        debugging("email_to_user: Email ($toemail) is invalid! Not sending.");
        return false;
    }

    // TLD .invalid  is specifically reserved for invalid domain names.
    // For More information, see {@link http://tools.ietf.org/html/rfc2606#section-2}.
    if (substr($toemail, -8) == '.invalid') {
        debugging("email_to_user: User email domain ($toemail) is invalid! Not sending.");
        return true; // This is not an error.
    }

    // If the user is a remote mnet user, parse the email text for URL to the
    // wwwroot and modify the url to direct the user's browser to login at their
    // home site (identity provider - idp) before hitting the link itself.
    
	$mail = get_mailer();

    if (!empty($mail->SMTPDebug)) {
        echo '<pre>' . "\n";
    }

    $temprecipients = array();
    $tempreplyto = array();

    // Make sure that we fall back onto some reasonable no-reply address.
    $noreplyaddressdefault = 'noreply@' . get_host_from_url($CFG->wwwroot);
    $noreplyaddress = empty($CFG->noreplyaddress) ? $noreplyaddressdefault : $CFG->noreplyaddress;

    if (!validate_email($noreplyaddress)) {
        debugging('email_to_user: Invalid noreply-email '.s($noreplyaddress));
        $noreplyaddress = $noreplyaddressdefault;
    }

    // Make up an email address for handling bounces.
    $mail->Sender = $noreplyaddress;

    // Make sure that the explicit replyto is valid, fall back to the implicit one.
    if (!empty($replyto) && !validate_email($replyto)) {
        debugging('email_to_user: Invalid replyto-email '.s($replyto));
        $replyto = $noreplyaddress;
    }

    if (is_string($from)) { // So we can pass whatever we want if there is need.
        $mail->From     = $noreplyaddress;
        $mail->FromName = $from;
    // Check if using the true address is true, and the email is in the list of allowed domains for sending email,
    // and that the senders email setting is either displayed to everyone, or display to only other users that are enrolled
    // in a course with the sender.
    } else if ($usetrueaddress ) {
        if (!validate_email($toemail)) {
            debugging('email_to_user: Invalid from-email '.s($toemail).' - not sending');
            // Better not to use $noreplyaddress in this case.
            return false;
        }
        $mail->From = $from->email;
        $fromdetails = new stdClass();
        $fromdetails->name = fullname($from);
        $fromdetails->url = preg_replace('#^https?://#', '', $CFG->wwwroot);
        $fromdetails->siteshortname = format_string($SITE->shortname);
        $fromstring = $fromdetails->name;
        if ($CFG->emailfromvia == EMAIL_VIA_ALWAYS) {
            $fromstring = get_string('emailvia', 'core', $fromdetails);
        }
        $mail->FromName = $fromstring;
        if (empty($replyto)) {
            $tempreplyto[] = array($from->email, fullname($from));
        }
    } else {
        $mail->From = $noreplyaddress;
        $fromdetails = new stdClass();
        $fromdetails->name = fullname($from);
        $fromdetails->url = preg_replace('#^https?://#', '', $CFG->wwwroot);
        $fromdetails->siteshortname = format_string($SITE->shortname);
        $fromstring = $fromdetails->name;
        if ($CFG->emailfromvia != EMAIL_VIA_NEVER) {
            $fromstring = get_string('emailvia', 'core', $fromdetails);
        }
        $mail->FromName = $fromstring;
        if (empty($replyto)) {
            $tempreplyto[] = array($noreplyaddress, get_string('noreplyname'));
        }
    }

    if (!empty($replyto)) {
        $tempreplyto[] = array($replyto, $replytoname);
    }
	
    $temprecipients[] = array($toemail, '');

    // Set word wrap.
    $mail->WordWrap = $wordwrapwidth;

    if (!empty($from->customheaders)) {
        // Add custom headers.
        if (is_array($from->customheaders)) {
            foreach ($from->customheaders as $customheader) {
                $mail->addCustomHeader($customheader);
            }
        } else {
            $mail->addCustomHeader($from->customheaders);
        }
    }
    if (ini_get('mail.add_x_header')) {

        $stack = debug_backtrace(false);
        $origin = $stack[0];

        foreach ($stack as $depth => $call) {
            if ($call['function'] == 'message_send') {
                $origin = $call;
            }
        }

        $originheader = $CFG->wwwroot . ' => ' . gethostname() . ':'
             . str_replace($CFG->dirroot . '/', '', $origin['file']) . ':' . $origin['line'];
        $mail->addCustomHeader('X-Moodle-Originating-Script: ' . $originheader);
    }

    if (!empty($from->priority)) {
        $mail->Priority = $from->priority;
    }

    $renderer = $PAGE->get_renderer('core');
    $context = array(
        'sitefullname' => $SITE->fullname,
        'siteshortname' => $SITE->shortname,
        'sitewwwroot' => $CFG->wwwroot,
        'subject' => $subject,
        'to' => $toemail,
        'toname' => '',
        'from' => $mail->From,
        'fromname' => $mail->FromName,
    );
    if (!empty($tempreplyto[0])) {
        $context['replyto'] = $tempreplyto[0][0];
        $context['replytoname'] = $tempreplyto[0][1];
    }

    $context['body'] = $messagetext;
    $mail->Subject = $renderer->render_from_template('core/email_subject', $context);
    $mail->FromName = $renderer->render_from_template('core/email_fromname', $context);
    $messagetext = $renderer->render_from_template('core/email_text', $context);

    // Autogenerate a MessageID if it's missing.
    if (empty($mail->MessageID)) {
        $mail->MessageID = generate_email_messageid();
    }

   $mail->isHTML(true);
   $mail->Encoding = 'quoted-printable';
   $mail->Body    =  $messagehtml;
   $mail->AltBody =  "\n$messagetext\n";
    if ($attachment && $attachname) {
        if (preg_match( "~\\.\\.~" , $attachment )) {
            // Security check for ".." in dir path.
            $supportuser = core_user::get_support_user();
            $temprecipients[] = array($supportuser->email, fullname($supportuser, true));
            $mail->addStringAttachment('Error in attachment.  User attempted to attach a filename with a unsafe name.', 'error.txt', '8bit', 'text/plain');
        } else {
            require_once($CFG->libdir.'/filelib.php');
            $mimetype = mimeinfo('type', $attachname);

            $attachmentpath = $attachment;

            // Before doing the comparison, make sure that the paths are correct (Windows uses slashes in the other direction).
            $attachpath = str_replace('\\', '/', $attachmentpath);
            // Make sure both variables are normalised before comparing.
            $temppath = str_replace('\\', '/', realpath($CFG->tempdir));

            // If the attachment is a full path to a file in the tempdir, use it as is,
            // otherwise assume it is a relative path from the dataroot (for backwards compatibility reasons).
            if (strpos($attachpath, $temppath) !== 0) {
                $attachmentpath = $CFG->dataroot . '/' . $attachmentpath;
            }

            $mail->addAttachment($attachmentpath, $attachname, 'base64', $mimetype);
        }
    }

    // Check if the email should be sent in an other charset then the default UTF-8.
    if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {

        // Use the defined site mail charset or eventually the one preferred by the recipient.
        $charset = $CFG->sitemailcharset;
        
        // Convert all the necessary strings if the charset is supported.
        $charsets = get_list_of_charsets();
        unset($charsets['UTF-8']);
        if (in_array($charset, $charsets)) {
            $mail->CharSet  = $charset;
            $mail->FromName = core_text::convert($mail->FromName, 'utf-8', strtolower($charset));
            $mail->Subject  = core_text::convert($mail->Subject, 'utf-8', strtolower($charset));
            $mail->Body     = core_text::convert($mail->Body, 'utf-8', strtolower($charset));
            $mail->AltBody  = core_text::convert($mail->AltBody, 'utf-8', strtolower($charset));

            foreach ($temprecipients as $key => $values) {
                $temprecipients[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }
            foreach ($tempreplyto as $key => $values) {
                $tempreplyto[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }
        }
    }
    if(!empty($cclist) && is_array($cclist)){
		foreach($cclist as $cc){
			if(is_array($cc)){
				$email = isset($cc['email'])?$cc['email']:'';
				if(!empty($email)){
					$mail->addAddress($email);
				}               
			}else{
				if($cc != NULL){
					$mail->addCC($cc);
				}  
			}
		}
		foreach ($temprecipients as $values) {
			$mail->addAddress($values[0]);
		}
	}else{
		foreach ($temprecipients as $values) {
			$mail->addAddress($values[0]);
		}
	}
    foreach ($tempreplyto as $values) {
        $mail->addReplyTo($values[0], $values[1]);
    }
	
    if ($mail->send()) {
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return true;
    } else {
        // Trigger event for failing to send email.
        $event = \core\event\email_failed::create(array(
            'context' => context_system::instance(),
            'userid' => $from->id,
            'relateduserid' => 0,
            'other' => array(
                'subject' => $subject,
                'message' => $messagetext,
                'errorinfo' => $mail->ErrorInfo
            )
        ));
        $event->trigger();
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_user(): '.$mail->ErrorInfo);
        }
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return false;
    }
}
function versereminder_check_target_completion($userid, $targetcmid) {
    global $DB;
    // This versereminder is focused on getting people to do a particular (ie targeted) activity.
    // Behaviour of the module changes depending on whether the target activity is already complete.
    $conditions = array('userid' => $userid, 'coursemoduleid' => $targetcmid);
    $activitycompletion = $DB->get_record('course_modules_completion', $conditions);
    if ($activitycompletion) {
        // There is a target activity, and completion is enabled in that activity.
        $userstate = $activitycompletion->completionstate;
			
        if (in_array($userstate, array(COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS, COMPLETION_COMPLETE_FAIL))) {
            return true;
        }
    }
    return false;
}
