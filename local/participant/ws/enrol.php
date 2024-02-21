<?php
/**
 *
 * This package includes all core features handled by Radiant LMS Platform
 *
 *
 * @package local_participant
 * @author Radiant Solutions LLC
 */
require_once ("{$CFG->libdir}/externallib.php");


class local_participant_ws_enrol extends external_api
{


    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function enrol_users_parameters()
    {
        global $DB;

        $studentRole = $DB->get_record('role', array(
            'shortname' => 'student'
        ));

        $params = array(
            'enrolments' => new external_multiple_structure(new external_single_structure(array(
                'email' => new external_value(PARAM_EMAIL, 'The user that is going to be enrolled'),
                'idnumber' => new external_value(PARAM_RAW, 'The course to enrol the user role in'),
                'roleid' => new external_value(PARAM_INT, 'Role to assign to the user', VALUE_DEFAULT, $studentRole->id),
                'timestart' => new external_value(PARAM_INT, 'Timestamp when the enrolment start', VALUE_OPTIONAL),
                'timeend' => new external_value(PARAM_INT, 'Timestamp when the enrolment end', VALUE_OPTIONAL),
                'suspend' => new external_value(PARAM_INT, 'set to 1 to suspend the enrolment', VALUE_OPTIONAL)
            )))
        );

        return new external_function_parameters($params);
    }


    /**
     * Enrolment of users.
     *
     * Function throw an exception at the first error encountered.
     *
     * @param array $enrolments An array of user enrolment
     * @since Moodle 2.2
     */
    public static function enrol_users($enrolments)
    {
        global $DB, $CFG;

        require_once ($CFG->libdir . '/enrollib.php');

        $params = self::validate_parameters(self::enrol_users_parameters(), array(
            'enrolments' => $enrolments
        ));

        $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs
        // (except if the DB doesn't support it).

        // Retrieve the manual enrolment plugin.
        $enrol = enrol_get_plugin('manual');
        if(empty($enrol))
        {
            throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }

        foreach($params['enrolments'] as $enrolment)
        {
            // find the course by idnumber

            $conditions = array(
                'idnumber' => $enrolment['idnumber']
            );

            $course = $DB->get_record('course', $conditions);

            if($course)
            {
                $enrolment['courseid'] = $course->id;
                unset($enrolment['idnumber']);
            }
            else
            {
                get_string($identifier);
                throw new moodle_exception('ws_course_byidnumber_does_not_exist', 'local_participant', null, $enrolment['idnumber']);
            }

            // find the user by email
            $conditions = array(
                'email' => $enrolment['email'],
                'deleted' => 0
            );
            $user = $DB->get_record('user', $conditions);
            if($user)
            {
                $enrolment['userid'] = $user->id;
                unset($enrolment['email']);
            }
            else
            {
                throw new moodle_exception('ws_user_by_email_not_found', 'local_participant', null, $enrolment['email']);
            }

            // Ensure the current user is allowed to run this function in the enrolment context.
            $context = context_course::instance($enrolment['courseid'], IGNORE_MISSING);
            self::validate_context($context);

            // Check that the user has the permission to manual enrol.
            require_capability('enrol/manual:enrol', $context);

            // Throw an exception if user is not able to assign the role.
            $roles = get_assignable_roles($context);
            if(! array_key_exists($enrolment['roleid'], $roles))
            {
                $errorparams = new stdClass();
                $errorparams->roleid = $enrolment['roleid'];
                $errorparams->courseid = $enrolment['courseid'];
                $errorparams->userid = $enrolment['userid'];
                throw new moodle_exception('wsusercannotassign', 'enrol_manual', '', $errorparams);
            }

            // Check manual enrolment plugin instance is enabled/exist.
            $instance = null;
            $enrolinstances = enrol_get_instances($enrolment['courseid'], true);
            foreach($enrolinstances as $courseenrolinstance)
            {
                if($courseenrolinstance->enrol == "manual")
                {
                    $instance = $courseenrolinstance;
                    break;
                }
            }
            if(empty($instance))
            {
                $errorparams = new stdClass();
                $errorparams->courseid = $enrolment['courseid'];
                throw new moodle_exception('wsnoinstance', 'enrol_manual', $errorparams);
            }

            // Check that the plugin accept enrolment (it should always the case, it's hard coded in the plugin).
            if(! $enrol->allow_enrol($instance))
            {
                $errorparams = new stdClass();
                $errorparams->roleid = $enrolment['roleid'];
                $errorparams->courseid = $enrolment['courseid'];
                $errorparams->userid = $enrolment['userid'];
                throw new moodle_exception('wscannotenrol', 'enrol_manual', '', $errorparams);
            }

            // Finally proceed the enrolment.
            $enrolment['timestart'] = isset($enrolment['timestart']) ? $enrolment['timestart'] : 0;
            $enrolment['timeend'] = isset($enrolment['timeend']) ? $enrolment['timeend'] : 0;
            $enrolment['status'] = (isset($enrolment['suspend']) && ! empty($enrolment['suspend'])) ? ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;

            $enrol->enrol_user($instance, $enrolment['userid'], $enrolment['roleid'], $enrolment['timestart'], $enrolment['timeend'], $enrolment['status']);
        }

        $transaction->allow_commit();
    }


    /**
     * Returns description of method result value.
     *
     * @return null
     * @since Moodle 2.2
     */
    public static function enrol_users_returns()
    {
        return null;
    }
}