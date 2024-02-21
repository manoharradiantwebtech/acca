<?php
/**
 * Paradiso LMS is powered by Paradiso Solutions LLC
 *
 * This package includes all core features handled by Paradiso LMS Platform
 *
 *
 * @package local_paradisolms
 * @author Paradiso Solutions LLC
 */
$services = array(
    // the name of the web service
    'Local Participant User Creation' => array(
        // web service functions of this service
        'functions' => array(
            'local_participant_create_users',
            'local_participant_manual_enrol_users',
            'local_userlist_data',
            'local_manual_enrollment',
            'user_email_verification',
            'local_user_login',
        ),

        // if set, the web service user need this capability to access any function of this service. For example: 'some/capability:specified'
        'requiredcapability' => '',
        // if enabled, the Moodle administrator must link some user to this service into the administration
        'restrictedusers' => 0,
        // if enabled, the service can be reachable on a default installation
        'enabled' => 0
    )
);

$functions = array(

    // web service function name
    'local_participant_create_users' => array(

        // class containing the external function
        'classname' => 'local_participant_ws_user',

        // external function name
        'methodname' => 'create_users',

        // file containing the class/external function
        'classpath' => 'local/participant/ws/user.php',

        // human readable description of the web service function
        'description' => 'Create user.',

        // database rights of the web service function (read, write)
        'type' => 'write'
    ),

    // web service function name
    'local_participant_manual_enrol_users' => array(

        // class containing the external function
        'classname' => 'local_participant_ws_enrol',

        // external function name
        'methodname' => 'enrol_users',

        // file containing the class/external function
        'classpath' => 'local/participant/ws/enrol.php',

        // human readable description of the web service function
        'description' => 'Manual enrol users.',

        // database rights of the web service function (read, write)
        'type' => 'write'
    ),


    'local_userlist_data' => array(

        // class containing the external function
        'classname' => 'local_participant_ws_user',

        // external function name
        'methodname' => 'userlist_data',

        // file containing the class/external function
        'classpath' => 'local/participant/ws/user.php',

        // human readable description of the web service function
        'description' => 'user list.',

        // database rights of the web service function (read, write)
        'type' => 'write'
    ),

    'local_manual_enrollment' => array(
        // class containing the external function
        'classname' => 'local_participant_ws_user',

        // external function name
        'methodname' => 'manual_enrollment',

        // file containing the class/external function
        'classpath' => 'local/participant/ws/user.php',

        // human readable description of the web service function
        'description' => 'enroll user into course.',

        // database rights of the web service function (read, write)
        'type' => 'write'
    ),

    'user_email_verification' => array(
        // class containing the external function
        'classname' => 'local_participant_ws_user',

        // external function name
        'methodname' => 'email_verification',

        // file containing the class/external function
        'classpath' => 'local/participant/ws/user.php',

        // human readable description of the web service function
        'description' => 'To check is user exist in lms or not using email address.',

        // database rights of the web service function (read, write)
        'type' => 'write'
    ),
    
    'local_user_login' => array(
        // class containing the external function
        'classname' => 'local_participant_ws_user',

        // external function name
        'methodname' => 'user_login',

        // file containing the class/external function
        'classpath' => 'local/participant/ws/user.php',

        // human readable description of the web service function
        'description' => 'To get the user info if user exist.',

        // database rights of the web service function (read, write)
        'type' => 'write'
    ),
);