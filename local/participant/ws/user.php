<?php
/**
 * Paradiso LMS is powered by Paradiso Solutions LLC
 *
 * This package includes all core features handled by Paradiso LMS Platform
 *
 *
 * @package local_participant
 * @author Paradiso Solutions LLC
 */
require_once("{$CFG->libdir}/externallib.php");


class local_participant_ws_user extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
   /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function create_users_parameters()
    {
        global $CFG, $DB;
        $studentRole = $DB->get_record('role', array(
            'shortname' => 'student'
        ));
        return new external_function_parameters(array(
            'firstname' => new external_value(core_user::get_property_type('firstname'), 'The first name(s) of the user'),
            'lastname' => new external_value(core_user::get_property_type('lastname'), 'The family name of the user'),
            'email' => new external_value(core_user::get_property_type('email'), 'A valid and unique email address'),
            'username' => new external_value(core_user::get_property_type('username'),
                'Username policy is defined in Moodle security config.', 'username'),
            'auth' => new external_value(core_user::get_property_type('auth'), 'Auth plugins include manual, ldap, etc',
                VALUE_DEFAULT, 'manual', core_user::get_property_null('auth')),
            'password' => new external_value(core_user::get_property_type('password'),
                'Plain text password consisting of any characters', 'user password'),
            'roleid' => new external_value(PARAM_INT, 'Role to assign to the user', VALUE_DEFAULT, $studentRole->id),
        ));
    }

    public static function create_users($firstname, $lastname, $email, $username, $auth, $password, $roleid)
    {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/lib/weblib.php");
        require_once($CFG->dirroot . "/user/lib.php");
        require_once($CFG->dirroot . "/user/profile/lib.php"); // Required for customfields related function.

        // Ensure the current user is allowed to run this function.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:create', $context);

        $availableauths = core_component::get_plugin_list('auth');
        unset($availableauths['mnet']); // These would need mnethostid too.
        unset($availableauths['webservice']); // We do not want new webservice users for now.

        $availablethemes = core_component::get_plugin_list('theme');
        $availablelangs = get_string_manager()->get_list_of_translations();

        $transaction = $DB->start_delegated_transaction();
        $enrol = enrol_get_plugin('manual');
        if (empty($enrol)) {
            throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }
        $userids = array();

        $user = array();
        $user['firstname'] = $firstname;
        $user['lastname'] = $lastname;
        $user['email'] = strtolower($email);
        $user['username'] = $username;
        $user['auth'] = $auth;
        $user['password'] = $password;
        $user['idnumber'] = '';
        $user['roleid'] = $roleid;

        $password = $user['password'];

        // Make sure auth is valid.
        if (empty($availableauths[$user['auth']])) {
            throw new invalid_parameter_exception('Invalid authentication type: ' . $user['auth']);
        }

        $user['confirmed'] = true;
        $user['mnethostid'] = $CFG->mnet_localhost_id;

        // Start of user info validation.
        // Make sure we validate current user info as handled by current GUI. See user/editadvanced_form.php func validation().
        if (!validate_email($user['email'])) {
            throw new moodle_exception('invalid_email_address', 'local_participant', null, array(
                'username' => $user['email']
            ));
        } elseif (
        $DB->record_exists('user', array(
            'username' => $user['username'],
            'mnethostid' => $CFG->mnet_localhost_id
        ))) {
            $user = $DB->get_record('user', array('username' => $user['username']));
            $data = [
                'usercreated' => false,
                'userid' => $user->id,
                'username' => $user->username,
                'name' => $user->firstname . ' ' . $user->lastname,
                'email' => $user->email,
                'deleted' => $user->deleted,
                'suspended' => $user->suspended,
                'institution' => $user->institution,
                'department' => $user->department,
                'phone' => $user->phone1,
                'country' => $user->country,
                'city' => $user->city,
                'address' => $user->address,
            ];

// Return success response with user data
            return [
                'code' => 200,
                'status' => 'success',
                'message' => 'User already there',
                'data' => $data,
            ];
        } else {
            // Create the user data now!
            $user['id'] = user_create_user($user, true, false);

            // Custom fields.
            if (!empty($user['customfields'])) {
                foreach ($user['customfields'] as $customfield) {
                    // Profile_save_data() saves profile file it's expecting a user with the correct id,
                    // and custom field to be named profile_field_"shortname".
                    $user["profile_field_" . $customfield['type']] = $customfield['value'];
                }
                profile_save_data((object)$user);
            }

            // Trigger event.
            \core\event\user_created::create_from_userid($user['id'])->trigger();

            // Preferences.
            if (!empty($user['preferences'])) {
                foreach ($user['preferences'] as $preference) {
                    set_user_preference($preference['type'], $preference['value'], $user['id']);
                }
            }

            // send email to the user
            {
                $to = array();
                $to[] = $user['email'];

                // user message.
                $messagehtml = <<<xxx
        <b>Here is the information to login to your account</b><br><br>
        Url: <a href="{$CFG->wwwroot}">{$CFG->wwwroot}</a><br>
        Username: {$user['email']}<br>
        Password: $password<br>
        xxx;
                $mail = get_mailer();
                $supportuser = core_user::get_support_user();
                $mail->Sender = $supportuser->email;
                $mail->From = $supportuser->email;
                $mail->FromName = 'Gta Academy';
                $mail->Subject = 'User account';
                $mail->isHTML(true);
                $mail->Encoding = 'quoted-printable';
                $mail->Body = $messagehtml;

                foreach ($to as $emil)
                    $mail->AddAddress($emil);
                $mail->send();
            }
            $user = $DB->get_record('user', array('id' => $user['id']));
            $transaction->allow_commit();

          // Return success response with user data
            $data = [
                'usercreated' => true,
                'userid' => $user->id,
                'username' => $user->username,
                'name' => $user->firstname . ' ' . $user->lastname,
                'email' => $user->email,
                'deleted' => $user->deleted,
                'suspended' => $user->suspended,
                'institution' => $user->institution,
                'department' => $user->department,
                'phone' => $user->phone1,
                'country' => $user->country,
                'city' => $user->city,
                'address' => $user->address,
            ];
            
          // Return success response with user data
            return [
                'code' => 200,
                'status' => 'success',
                'message' => 'User created',
                'data' => $data,
            ];
        }
    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function create_users_returns()
    {
        return new external_single_structure([
            'code' => new external_value(PARAM_INT, 'HTTP response status code'),
            'status' => new external_value(PARAM_TEXT, 'Status of the API response (success/error)'),
            'message' => new external_value(PARAM_TEXT, 'Status of the API response (success/error)'),
            'data' => new external_single_structure([
                'usercreated' => new external_value(PARAM_BOOL, 'Flag indicating if user exists'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'username' => new external_value(PARAM_TEXT, 'Username'),
                'name' => new external_value(PARAM_TEXT, 'User name'),
                'email' => new external_value(PARAM_TEXT, 'User email address'),
                'deleted' => new external_value(PARAM_INT, 'User deleted'),
                'suspended' => new external_value(PARAM_INT, 'User suspended'),
                'institution' => new external_value(PARAM_TEXT, 'User ID'),
                'department' => new external_value(PARAM_TEXT, 'User institution'),
                'phone' => new external_value(PARAM_TEXT, 'User phone'),
                'country' => new external_value(PARAM_TEXT, 'User ID'),
                'city' => new external_value(PARAM_TEXT, 'User country'),
                'address' => new external_value(PARAM_TEXT, 'User address'),
            ]),
        ]);
    }




    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function userlist_data_parameters() {
        global $CFG, $DB;
        return new external_function_parameters(
            array(
                'date' => new external_value(PARAM_RAW, 'get the userlist after that date'),
            )
        );
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function userlist_data($date) {
        global $CFG, $DB;

        // Convert date string to datetime object
        try {
            $datetime = new DateTime($date);
        } catch (Exception $e) {
            return [
                'code' => 400,
                'status' => 'error',
                'message' => 'Invalid date format',
                'message_code' => 'invalid_date_format',
                'data' => null
            ];
        }
        // Query the Moodle database for user data created after the specified date
        $users = $DB->get_records_sql("
        SELECT *
        FROM {user}
        WHERE deleted = 0 AND timecreated > :date
         ", ['date' => $datetime->getTimestamp()]);

        // Convert the query results to an array of user data

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'userid' => $user->id,
                'username' => $user->username,
                'email-address' => $user->email,
                'phone-number' => $user->phone1,
                'password' =>  $user->password,
            ];
        }

        // Return success response with user data
        return [
            'code' => 200,
            'status' => 'success',
            'message' => 'Record found',
            'message_code' => 'data_found',
            'data' => $data
        ];
}



    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function userlist_data_returns() {
        // Return response structure
        return new external_single_structure([
            'code' => new external_value(PARAM_INT, 'HTTP response status code'),
            'status' => new external_value(PARAM_TEXT, 'Status of the API response (success/error)'),
            'message' => new external_value(PARAM_TEXT, 'Message describing the API response'),
            'message_code' => new external_value(PARAM_TEXT, 'Code describing the message'),
            'data' => new external_multiple_structure(new external_single_structure([
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'username' => new external_value(PARAM_TEXT, 'Username'),
                'email-address' => new external_value(PARAM_TEXT, 'Email address'),
                'phone-number' => new external_value(PARAM_TEXT, 'Email address'),
                'password' => new external_value(PARAM_TEXT, 'Email address'),
            ]), 'List of user data'),
        ]);

    }



     public static function manual_enrollment_parameters()
    {
        global $DB;

        $studentRole = $DB->get_record('role', array(
            'shortname' => 'student'
        ));
        return new external_function_parameters(array(
            'email' => new external_value(PARAM_EMAIL, 'The user that is going to be enrolled'),
            'idnumbers' => new external_multiple_structure(
                new external_value(PARAM_INT, 'The course IDs for enrolling the user')
            ),
            'roleid' => new external_value(PARAM_INT, 'Role to assign to the user', VALUE_DEFAULT, $studentRole->id),
            'timestart' => new external_value(PARAM_INT, 'Timestamp when the enrolment start', VALUE_OPTIONAL),
            'timeend' => new external_value(PARAM_INT, 'Timestamp when the enrolment end', VALUE_OPTIONAL),
            'suspend' => new external_value(PARAM_INT, 'set to 1 to suspend the enrolment', VALUE_OPTIONAL),
            'amount' => new external_value(PARAM_INT, 'The user that is going to be enrolled'),
        ));
    }


    /**
     * Enrolment of users.
     *
     * Function throw an exception at the first error encountered.
     *
     * @param array $enrolments An array of user enrolment
     * @since Moodle 2.2
     */
    public static function manual_enrollment($email, $idnumbers, $roleid, $amount)
    {
        global $DB, $CFG;
        require_once($CFG->libdir . '/enrollib.php');

        $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs (except if the DB doesn't support it).
        $timestart = time();

        // Find the user by email.
        $conditions = array(
            'email' => $email,
            'deleted' => 0
        );
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            $data = array(
                'enrollmenttext' => 'user does not exist.',
                'enrollmentstatus' => false,
                'courseid' => null,
                'userid' => $user->id,
                'username' => $user->username,
                'name' => $user->firstname,
                'email' => $user->email,
                'deleted' => $user->deleted,
                'suspended' => $user->suspended,
                'institution' => $user->institution,
                'department' => $user->department,
                'phone' => $user->phone1,
                'country' => $user->country,
                'city' => $user->city,
                'address' => $user->address,
            );
            // Return success response with user data
            return array(
                'code' => 400,
                'status' => 'failed',
                'data' => array($data)
            );
            exit();
        }

        // Ensure the current user is allowed to run this function in the enrolment context.
        $context = context_system::instance();
        self::validate_context($context);

        // Check that the user has the permission to manual enrol.
        require_capability('enrol/manual:enrol', $context);

        // Iterate over the course IDs and enroll the user into each course.
        $enrollments = array();
        foreach ($idnumbers as $idnumber) {
            // Find the course by ID number.
            $course = $DB->get_record('course', array('id' => $idnumber));

            if (!$course) {
                $data = array(
                    'enrollmenttext' => 'course does not exist.',
                    'enrollmentstatus' => false,
                    'courseid' => null,
                    'userid' => $user->id,
                    'username' => $user->username,
                    'name' => $user->firstname . ' ' . $user->lastname,
                    'email' => $user->email,
                    'deleted' => $user->deleted,
                    'suspended' => $user->suspended,
                    'institution' => $user->institution,
                    'department' => $user->department,
                    'phone' => $user->phone1,
                    'country' => $user->country,
                    'city' => $user->city,
                    'address' => $user->address,
                );
                // Return failed response with course data
                $enrollments[] = $data;
                continue;
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

            if (empty($instance)) {
                $data = array(
                    'enrollmenttext' => 'manual enrolment plugin instance is not enabled/exist.',
                    'enrollmentstatus' => false,
                    'courseid' => $course->id,
                    'userid' => $user->id,
                    'username' => $user->username,
                    'name' => $user->firstname . ' ' . $user->lastname,
                    'email' => $user->email,
                    'deleted' => $user->deleted,
                    'suspended' => $user->suspended,
                    'institution' => $user->institution,
                    'department' => $user->department,
                    'phone' => $user->phone1,
                    'country' => $user->country,
                    'city' => $user->city,
                    'address' => $user->address,
                );
                // Return failed response with instance data
                $enrollments[] = $data;
                continue;
            }

            // Check that the plugin accepts enrolment.
            $enrol = enrol_get_plugin('manual');
            if (!$enrol->allow_enrol($instance)) {

                $data = array(
                    'enrollmenttext' => 'Plugin does not accept enrollment',
                    'enrollmentstatus' => false,
                    'courseid' => $course->id,
                    'userid' => $user->id,
                    'username' => $user->username,
                    'name' => $user->firstname . ' ' . $user->lastname,
                    'email' => $user->email,
                    'deleted' => $user->deleted,
                    'suspended' => $user->suspended,
                    'institution' => $user->institution,
                    'department' => $user->department,
                    'phone' => $user->phone1,
                    'country' => $user->country,
                    'city' => $user->city,
                    'address' => $user->address,
                );
                // Return failed response with enrolment data
                $enrollments[] = $data;
                continue;
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
            // Finally, proceed with the enrolment.
            $timestart = time();
            $timeend = isset($timeend) ? $timeend : 0;
            $status = (isset($suspend) && !empty($suspend)) ? ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;
            if ($user_enrollment->enrollment_end_date != 0 && $user_enrollment->enrollment_end_date < time()) {
                    // User's enrollment has expired, re-enroll them
                    $enrol->enrol_user($instance, $user->id, $roleid, $timestart, $timeend, ENROL_USER_ACTIVE);
                    $data = array(
                        'enrollmenttext' => 'Enrollment succesfull',
                        'enrollmentstatus' => true,
                        'courseid' => $course->id,
                        'userid' => $user->id,
                        'username' => $user->username,
                        'name' => $user->firstname . ' ' . $user->lastname,
                        'email' => $user->email,
                        'deleted' => $user->deleted,
                        'suspended' => $user->suspended,
                        'institution' => $user->institution,
                        'department' => $user->department,
                        'phone' => $user->phone1,
                        'country' => $user->country,
                        'city' => $user->city,
                        'address' => $user->address,
                    );

                    $enrollments[] = $data;

                    //Insert value to user purchase history
                    $data = new stdClass();
                    $data->userid = $user->id;
                    $data->courseid = $course->id;
                    $data->amount = $amount;
                    $data->usertype = 'web';
                    $data->timemodified = time();
                    // Insert data into "user_purchase_history" table
                    $DB->insert_record('user_purchase_history', $data);
                } else if(empty($user_enrollment)) {

                $enrol->enrol_user($instance, $user->id, $roleid, $timestart, $timeend, $status);
                $data = array(
                    'enrollmenttext' => 'Enrollment succesfull',
                    'enrollmentstatus' => true,
                    'courseid' => $course->id,
                    'userid' => $user->id,
                    'username' => $user->username,
                    'name' => $user->firstname . ' ' . $user->lastname,
                    'email' => $user->email,
                    'deleted' => $user->deleted,
                    'suspended' => $user->suspended,
                    'institution' => $user->institution,
                    'department' => $user->department,
                    'phone' => $user->phone1,
                    'country' => $user->country,
                    'city' => $user->city,
                    'address' => $user->address,
                );

                $enrollments[] = $data;

                //Insert value to user purchase history
                $data = new stdClass();
                $data->userid = $user->id;
                $data->courseid = $course->id;
                $data->amount = $amount;
                $data->usertype = 'web';
                $data->timemodified = time();
                // Insert data into "user_purchase_history" table
                $DB->insert_record('user_purchase_history', $data);
            } else {
                    $data = array(
                        'enrollmenttext' => 'User is already enrolled into course',
                        'enrollmentstatus' => false,
                        'courseid' => $course->id,
                        'userid' => $user->id,
                        'username' => $user->username,
                        'name' => $user->firstname . ' ' . $user->lastname,
                        'email' => $user->email,
                        'deleted' => $user->deleted,
                        'suspended' => $user->suspended,
                        'institution' => $user->institution,
                        'department' => $user->department,
                        'phone' => $user->phone1,
                        'country' => $user->country,
                        'city' => $user->city,
                        'address' => $user->address,
                    );

                    $enrollments[] = $data;

                }

        }

        $transaction->allow_commit();

        // Return success response with user enrollments
        return array(
            'code' => 200,
            'status' => 'success',
            'data' => $enrollments
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return null
     * @since Moodle 2.2
     */
    public static function manual_enrollment_returns()
    {
        return new external_single_structure([
            'code' => new external_value(PARAM_INT, 'HTTP response status code'),
            'status' => new external_value(PARAM_TEXT, 'Status of the API response (success/error)'),
            'data' => new external_multiple_structure(new external_single_structure([
                'enrollmenttext' => new external_value(PARAM_TEXT, 'enrollment text'),
                'enrollmentstatus' => new external_value(PARAM_BOOL, 'enrollment status'),
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'username' => new external_value(PARAM_TEXT, 'User name'),
                'name' => new external_value(PARAM_TEXT, 'User full name'),
                'email' => new external_value(PARAM_TEXT, 'User email address'),
                'deleted' => new external_value(PARAM_INT, 'User deleted'),
                'suspended' => new external_value(PARAM_INT, 'User suspended'),
                'institution' => new external_value(PARAM_TEXT, 'User ID'),
                'department' => new external_value(PARAM_TEXT, 'User institution'),
                'phone' => new external_value(PARAM_TEXT, 'User phone'),
                'country' => new external_value(PARAM_TEXT, 'User ID'),
                'city' => new external_value(PARAM_TEXT, 'User country'),
                'address' => new external_value(PARAM_TEXT, 'User address'),
            ]), 'List of user data'),
        ]);
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
     public static function email_verification_parameters() {
        return new external_function_parameters(array(
            'email' => new external_value(PARAM_TEXT, 'The username of the user to autologin'),
        ));
    }
    public static function email_verification($email) {
        global $DB;

        // Validate the email parameter
        $params = self::validate_parameters(
            self::email_verification_parameters(),
            array('email' => $email)
        );

        // Retrieve the user from the Moodle database based on the email
        $user = $DB->get_record('user', array('email' => $params['email']));
        if (!empty($user)) {
            $data = [
                'existing_user' => true,
                'userid' => $user->id,
                'username' => $user->username,
                'name' => $user->firstname . ' ' . $user->lastname,
                'email' => $user->email,
                'deleted' => $user->deleted,
                'suspended' => $user->suspended,
                'institution' => $user->institution,
                'department' => $user->department,
                'phone' => $user->phone1,
                'country' => $user->country,
                'city' => $user->city,
                'address' => $user->address,
            ];

            // Return success response with user data
            return [
                'code' => 200,
                'status' => 'success',
                'message' => 'User found',
                'data' => $data,
            ];
        } else {
            $data = [
                'existing_user' => false,
                'userid' => $user->id,
                 'username' => $user->username,
                'name' => $user->firstname,
                'email' => $user->email,
                'deleted' => $user->deleted,
                'suspended' => $user->suspended,
                'institution' => $user->institution,
                'department' => $user->department,
                'phone' => $user->phone1,
                'country' => $user->country,
                'city' => $user->city,
                'address' => $user->address,
            ];
            return [
                'code' => 400,
                'status' => 'user not exist',
                'message' => 'Invalid user',
                'data' => $data,
            ];
        }
    }


    public static function email_verification_returns() {
        return new external_single_structure([
            'code' => new external_value(PARAM_INT, 'HTTP response status code'),
            'status' => new external_value(PARAM_TEXT, 'Status of the API response (success/error)'),
            'message' => new external_value(PARAM_TEXT, 'Status of the API response (success/error)'),
            'data' => new external_single_structure([
                'existing_user' => new external_value(PARAM_BOOL, 'Flag indicating if user exists'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'username' => new external_value(PARAM_TEXT, 'Username'),
                'name' => new external_value(PARAM_TEXT, 'User name'),
                'email' => new external_value(PARAM_TEXT, 'User email address'),
                'deleted' => new external_value(PARAM_INT, 'User deleted'),
                'suspended' => new external_value(PARAM_INT, 'User suspended'),
                'institution' => new external_value(PARAM_TEXT, 'User ID'),
                'department' => new external_value(PARAM_TEXT, 'User institution'),
                'phone' => new external_value(PARAM_TEXT, 'User phone'),
                'country' => new external_value(PARAM_TEXT, 'User ID'),
                'city' => new external_value(PARAM_TEXT, 'User country'),
                'address' => new external_value(PARAM_TEXT, 'User address'),
            ]),
        ]);
    }
    
     public static function user_login_parameters() {
        return new external_function_parameters(array(
            'username' => new external_value(PARAM_TEXT, 'The username of the user to login'),
            'password' => new external_value(PARAM_TEXT, 'The password of the user to login'),
        ));
    }
   public static function user_login($username, $password) {
    global $DB;
    // Validate the email parameter
    $params = self::validate_parameters(
        self::user_login_parameters(),
        array('username' => $username, 'password' => $password)
    );
    
    $user = $DB->get_record('user', array('username' => $username)); // Closing parenthesis added
    
    if (empty($user)) {
        $data = [
            'userfound' => false,
            'userid' => $user->id,
             'username' => $user->username,
            'name' => $user->firstname,
            'email' => $user->email,
            'deleted' => $user->deleted,
            'suspended' => $user->suspended,
            'institution' => $user->institution,
            'department' => $user->department,
            'phone' => $user->phone1,
            'country' => $user->country,
            'city' => $user->city,
            'address' => $user->address,
        ];
        
        return [
            'code' => 400,
            'status' => 'please provide correct username name',
            'message' => 'Invalid user name',
            'data' => $data,
        ];
    } else {
        $user = authenticate_user_login($username, $password);
        
        if (!$user) {
            // The authentication failed
            $data = [
                'userfound' => false,
                'userid' => $user->id,
                'username' => $user->username,
                'name' => $user->firstname,
                'email' => $user->email,
                'deleted' => $user->deleted,
                'suspended' => $user->suspended,
                'institution' => $user->institution,
                'department' => $user->department,
                'phone' => $user->phone1,
                'country' => $user->country,
                'city' => $user->city,
                'address' => $user->address,
            ];
            
            return [
                'code' => 400,
                'status' => 'please provide correct username password',
                'message' => 'Invalid user credential',
                'data' => $data,
            ];
        }
    }

    // Log in the user
    $login_result = complete_user_login($user);
    \core\session\manager::apply_concurrent_login_limit($user->id, session_id());

    if ($login_result) {
        // Return success response with user data
        $data = [
            'userfound' => true,
            'userid' => $user->id,
             'username' => $user->username,
            'name' => $user->firstname . ' ' . $user->lastname,
            'email' => $user->email,
            'deleted' => $user->deleted,
            'suspended' => $user->suspended,
            'institution' => $user->institution,
            'department' => $user->department,
            'phone' => $user->phone1,
            'country' => $user->country,
            'city' => $user->city,
            'address' => $user->address,
        ];

        // Return success response with user data
        return [
            'code' => 200,
            'status' => 'success',
            'message' => 'User found',
            'data' => $data,
        ];
    }
}

    public static function user_login_returns() {
        return new external_single_structure([
            'code' => new external_value(PARAM_INT, 'HTTP response status code'),
            'status' => new external_value(PARAM_TEXT, 'Status of the API response (success/error)'),
            'message' => new external_value(PARAM_TEXT, 'Status of the API response (success/error)'),
            'data' => new external_single_structure([
                'userfound' => new external_value(PARAM_BOOL, 'Flag indicating if user exists'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'username' => new external_value(PARAM_TEXT, 'Username'),
                'name' => new external_value(PARAM_TEXT, 'User name'),
                'email' => new external_value(PARAM_TEXT, 'User email address'),
                'deleted' => new external_value(PARAM_INT, 'User deleted'),
                'suspended' => new external_value(PARAM_INT, 'User suspended'),
                'institution' => new external_value(PARAM_TEXT, 'User ID'),
                'department' => new external_value(PARAM_TEXT, 'User institution'),
                'phone' => new external_value(PARAM_TEXT, 'User phone'),
                'country' => new external_value(PARAM_TEXT, 'User ID'),
                'city' => new external_value(PARAM_TEXT, 'User country'),
                'address' => new external_value(PARAM_TEXT, 'User address'),
            ]),
        ]);
    }


}