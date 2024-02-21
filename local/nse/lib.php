<?php
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->libdir . '/enrollib.php');
function sync_user(){
	global $DB;
	raise_memory_limit(MEMORY_HUGE);
	$config = get_config('local_nse');
	if($access_token = createJWTToken('Assist.Gtacademy@IN.GT.COM',$config)){
		if($users_object = getuser($access_token,$config)){
			UpdateORCreateUsers($users_object->profiles);
			if($users_object->total > 20){
				if($users_object = getuser($access_token,$config,$users_object->total-20,20)){
					UpdateORCreateUsers($users_object->profiles);
				}
			}
		}
	}

	if($users = getAllNseUsersFromMoodle()){
		foreach($users as $user){
			if($access_token = createJWTToken($user->email,$config)){
				$asignments = getAssignmentinfo($access_token,$config);
				if($asignments){
					foreach($asignments as $asignment){
						$assig_record = $DB->get_record('nse_assignment', ['assignable_id' => $asignment->assignable_id,'nse_userid'=>$asignment->user_id,'nse_enroll_id'=>$asignment->id]);
						$sql = "SELECT c.id as courseid,nse.cohortid
									  FROM {nse_course} nse
									  LEFT JOIN {course} c ON (nse.courseid=c.id)
									WHERE c.fullname= :name LIMIT 1";
						$sql_result = $DB->get_records_sql($sql,['name'=>$asignment->title]);
						foreach($sql_result as $row){
							$object = (object)[
								'assignable_id' => $asignment->assignable_id,
								'created_at'=> strtotime($asignment->created_at),
								'updated_at'=> strtotime($asignment->updated_at),
								'state' => $asignment->state,
								'due_at'=> strtotime($asignment->due_at),
								'assigned_date' => strtotime($asignment->assigned_date),
								'user_gtid' => $user->id,
								'cohortid' => $row->cohortid,
								'nse_userid' => $asignment->user_id,
								'nse_enroll_id' => $asignment->id,
							];
							if($assig_record){
								$object->id = $assig_record->id;
								$DB->update_record('nse_assignment', $object);
							}else{
								$DB->insert_record('nse_assignment',$object);
							}
							if($object){
								manual_enrollment($user, $row->courseid,$object->assigned_date,$object->due_at);
							}
							// if($row->cohortid && !$DB->get_record('cohort_members', ['userid' => $user->id,'cohortid'=>$row->cohortid])){
								// $object = (object)[
									// 'cohortid' => $row->cohortid,
									// 'userid'=>$user->id,
								// ];
								// $DB->insert_record('cohort_members',$object);
							// }
						}
					}
				}
			}
		}
	}
}
function UpdateORCreateUsers($nse_users){
	global $CFG;
	foreach($nse_users as $nse_user){
		if($nse_user->email && filter_var($nse_user->email, FILTER_VALIDATE_EMAIL) !== false){
			$moodle_user = getUserFromMoodle($nse_user->email,$nse_user->userId);
			if($moodle_user){
				$usernew = (object)[
					'id'		=> $moodle_user->id,
					'username'	=> strtolower($nse_user->email),
					'email'		=>	$nse_user->email,
					'idnumber'	=>  $nse_user->userId,
					'institution'=> 'NSE',
					'firstname'	=> $nse_user->firstName,
					'lastname'	=> $nse_user->lastName,
					'suspended'=> ($nse_user->status == 'active')?0:1,
					'deleted' => 0
				];
				user_update_user($usernew, false);
			}else{
				$usernew = (object)[
					'auth' 	=> 'saml2',
					'username'	=> strtolower($nse_user->email),
					'email'		=>	$nse_user->email,
					'mnethostid'=> $CFG->mnet_localhost_id,
					'suspended'	=>	0,
					'mnethostid'=> $CFG->mnet_localhost_id,
					'secret' 	=> random_string(15),
					'confirmed' => 1,
					'idnumber'	=>  $nse_user->userId,
					'institution'=> 'NSE',
					'firstname'	=> $nse_user->firstName,
					'lastname'	=> $nse_user->lastName,
					'deleted' => 0
				];
				user_create_user($usernew, false,true);
			}
		}
	}
}

function getUserFromMoodle($email,$idnumber){
	global $DB;
	$result = $DB->get_records_sql("SELECT * FROM {user} WHERE email = :mail OR idnumber = :idnumber", ['mail'=>$email,'idnumber'=>$idnumber]);
	foreach($result as $key => $val){
		$user = $val;
	}
	return $user;
}
function getAllNseUsersFromMoodle(){
	global $DB;
	return $DB->get_records_sql("SELECT * FROM {user} WHERE institution='NSE' AND suspended = 0");
}
function createJWTToken($email,$config){
	$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
	$payload = json_encode(['email' => $email]);
	$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
	$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
	$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $config->secretkey, true);
	$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
	$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => $config->hosturl.'/api/developer/v5/auth',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => [
			'X-API-KEY: '.$config->apikey,
			'X-AUTH-TOKEN: '.$jwt
		],
	]);
	$response = json_decode(curl_exec($curl));
	curl_close($curl);
	if($response && isset($response->jwt_token) && $response->jwt_token){
		return $response->jwt_token;
	}
	return false;
}
function addupdate_recordtoDB($data){
	global $DB;
	if(is_array($data->description)){
		$data->description = $data->description['text']; 
	}
	$data->updateddate  = !empty($data->updateddate) ? $data->updateddate : time();
	if(!$data->courseid){
		throw new moodle_exception("course not found");
	}
	if($data->id){
		if($DB->update_record('nse_course', $data)){
			Update_to_nse($data->id);
			return true;
		}
	}else{
		unset($data->id);
		$data->createddate  = !empty($data->createddate) ? $data->createddate : time();
		if($id = $DB->insert_record('nse_course', $data)){
			Update_to_nse($id);
			return true;
		}
	}
	return false;
}

function Update_to_nse($id){
	global $DB,$CFG;
	$sql = "SELECT nse.id,
			nse.courseid,
			nse.bhav_type as currency,
			nse.bhav as amount,
			nse.description as description,
			nse.content_type as content_type,
			nse.courseduration as duration,
			nse.isactive as isactive,
			c.fullname as coursename
          FROM {nse_course} nse
		  LEFT JOIN {course} c ON (nse.courseid=c.id)
         WHERE nse.id= :id LIMIT 1";
	$sql_result = $DB->get_records_sql($sql,['id'=>$id]);
	if(isset($sql_result[$id])){
		$access_token = getToken();
		if($access_token){
			$response = call_request($access_token,[
				'name'=> $sql_result[$id]->coursename,
				'description'=> $sql_result[$id]->description,
				'content_type'=> $sql_result[$id]->content_type,
				'duration_sec'=> $sql_result[$id]->duration,
				'url' => $CFG->wwwroot.'/course/view.php?id='. $sql_result[$id]->courseid,
				'external_id' => 'GTlms_'.$sql_result[$id]->courseid,
				'prices_data' =>[['amount'=>$sql_result[$id]->amount,'currency'=>$sql_result[$id]->currency]],
				'status'=> ($sql_result[$id]->isactive == 1)?'active':'archived',
			]);
			if($response){
				$row = $DB->get_record('nse_course',['id'=>$sql_result[$id]->id]);
				$row->updateddate=time();
				$row->nse_response = $response;
				if($DB->update_record('nse_course', $row)){
					return true;
				}
			}
		}
	}
}

function getToken(){
	$config = get_config('local_nse');
	if($config){
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $config->host.'/api/oauth/token?client_id='.$config->clientid.'&Secret='.$config->secret.'&grant_type=client_credentials',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => 'client_id='.$config->clientid.'&client_secret='.$config->secret.'&grant_type=client_credentials',
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/x-www-form-urlencoded'
			],
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return isset(json_decode($response)->access_token)?json_decode($response)->access_token:false;
	}
	return false;
}
function call_request($tocken,$data){
	
	$config = get_config('local_nse');
	$curl = curl_init();
	curl_setopt_array($curl, [
			CURLOPT_URL =>$config->host."/api/developer/v2/courses.json",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
				'Authorization: Bearer '.$tocken,
			],
		]);
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
}

function getuser($token,$config,$limit = 20,$offset=0){
	
	$filer = http_build_query(["limit"=>$limit,'from_date'=>date('d/m/Y', strtotime("-100 days")),'offset'=>$offset]);
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $config->hosturl.'/api/developer/v5/profiles?'.$filer,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => array(
		  'X-API-KEY: '.$config->apikey,
		  'X-ACCESS-TOKEN: '.$token
		 ),
	));
	$response = json_decode(curl_exec($curl));
	if($response->total){
		return $response;
	}
}
function getAssignmentinfo($token,$config){
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => $config->hosturl.'/api/developer/v5/assignments',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => [
			'X-API-KEY: '.$config->apikey,
			  'X-ACCESS-TOKEN: '.$token,
			  'Accept: application/json',
			  'Content-Type: application/json',
		],
	]);

$response = json_decode(curl_exec($curl));
curl_close($curl);
return $response->assignments;
}
function manual_enrollment($user, $idnumber, $timestart=0,$timeend = 0){
	global $DB, $CFG;
	if(!$timestart){
		$timestart = time();
	}
    if (!$user) {
        return false;
    }
    $context = context_system::instance();
	
    $enrollments = [];
    $course = $DB->get_record('course', array('id' => $idnumber));
	if (!$course) {
        return false;
    }
	// Check if the manual enrolment plugin instance is enabled/exist.
    $instance = null;
    $enrolinstances = enrol_get_instances($course->id, true);
    foreach ($enrolinstances as $courseenrolinstance) {
        if ($courseenrolinstance->enrol == "manual") {
            $instance = $courseenrolinstance;
            break;
        }
    }
	if (empty($instance)){
		return false;
	}
	// Check that the plugin accepts enrolment.
    $enrol = enrol_get_plugin('manual');
    if (!$enrol->allow_enrol($instance)) {
        return false;
    }
    $sql = "SELECT
          c.id  AS course_id,
          c.fullname AS course_name,
          u.id AS user_id,
          u.username AS username,
         ue.status AS enrollment_status,
         ue.timeend AS enrollment_end_date
         FROM
         {course} c
         JOIN {enrol} e ON e.courseid = c.id
          JOIN {user_enrolments} ue ON ue.enrolid = e.id
        JOIN {user} u ON u.id = ue.userid
        WHERE
         c.id = $course->id 
        AND u.id = $user->id";
        // Execute the query with placeholders
        $user_enrollment = $DB->get_record_sql($sql);
        $status = (isset($suspend) && !empty($suspend)) ? ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;
        if(!$user_enrollment) {
            $enrol->enrol_user($instance, $user->id, $roleid = 0, $timestart, $timeend, $status);
        }
        return true;
    }
?>