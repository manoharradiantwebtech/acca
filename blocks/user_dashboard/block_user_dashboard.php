<?php

defined('MOODLE_INTERNAL') || die();

class block_user_dashboard extends block_base
{

    public function init()
    {
        $this->title = get_string('pluginname', 'block_user_dashboard');
    }

    public function get_content()
    {
        global $USER, $PAGE, $OUTPUT, $CFG, $DB;

        $userid = $USER->id;
        $enrolled_courses = enrol_get_users_courses($userid);

        // Initialize arrays to store enrolled and completed courses
        $enrolled_courses_data = array();
        $completed_courses_data = array();
        $completed_count = 0;
        $enrolled_count = 0;
        foreach ($enrolled_courses as $course) {

            $progress = \core_completion\progress::get_course_progress_percentage($course);
            $hasprogress = false;
            if ($progress === 0 || $progress > 0) {
                $hasprogress = true;
            }
            $progress = floor($progress);
            // Get the first visible activity or resource in the course
            // Query the Moodle database to get the last attempt activity or resources URL
            // Get the course context
            $modinfo = get_fast_modinfo($course->id);
            $course_context = context_course::instance($course->id);
            $activities = $modinfo->get_cms();
            // Get the count of activities in the course
            $activity_count = count($activities);
            // Get the list of resources in the course
            $resources = array_filter($activities, function ($activity) {
                return $activity->modname == 'resource';
            });

            // Get the count of resources in the course
            $resource_count = count($resources);
            $count = $activity_count + $resource_count;
            $today = time();
            $sql = 'SELECT DISTINCT ue.timestart
                        FROM {user_enrolments} ue
                        JOIN {enrol} ue2 ON ue.enrolid = ue2.id
                        JOIN {course} c ON ue2.courseid = c.id
                        WHERE ue.userid = :userid AND c.id = :courseid';
            $enrollment_start_date = $DB->get_record_sql($sql, array('userid' => $USER->id, 'courseid' => $course->id));
            if ($enrollment_start_date->timestart == 0) {
                $enrollment_start_date->timestart = $DB->get_field('course', 'timecreated ', array('id' => $course->id));
            }
            $days_in_seconds = 365 * 24 * 60 * 60;
            $days_elapsed = ($enrollment_start_date->timestart) + $days_in_seconds; // Calculate the number of days elapsed
            $days_until_expiration = date('d-M-Y', $days_elapsed); // Format the expiration timestamp as a date string

            // Check if the course is completed
            $completion = new completion_info($course);
            $course_completion = block_course_records_build_progress_course_format($course);
            if ($completion->is_course_complete($userid)) {
                $completion_info = $DB->get_record('course_completions', array('userid' => $userid, 'course' => $course->id));
                $completiondate = $completion_info->timecompleted;
                // Get the number of activities in the course
                $activities = $completion->get_activities();
                $total_activities = count($activities);

                // Get the number of activities that have been completed
                $completed_activities = 0;
                foreach ($activities as $activity) {
                    $activity_completion = $completion->get_data($activity, false, $userid);
                    if ($activity_completion->completionstate == COMPLETION_COMPLETE) {
                        $completed_activities++;
                    }
                }

                // Calculate the course completion percentage
                $percentage = 0;
                if ($total_activities > 0) {
                    $percentage = round(($completed_activities / $total_activities) * 100);
                }
                $comp_course_image = get_course_image_url($course->id); // Get the course image URL
                $completed_count++;

                // SQL query to retrieve the certificate information for the user
                $sql = "
                SELECT cm.id AS cmid, c.name AS certificate_name, c.timemodified AS enrolled_date, ci.timecreated AS completion_date
                FROM {customcert_issues} ci
                JOIN {course_modules} cm ON cm.module = (SELECT id FROM {modules} WHERE name = 'customcert')
                JOIN {customcert} c ON c.id = ci.customcertid AND c.id = cm.instance
                JOIN {modules} md ON md.id = cm.module
                WHERE ci.userid = :userid AND cm.deletioninprogress = 0 AND c.course = :courseid;
                ";
                $params = array('userid' => $userid, 'courseid' => $course->id);
                $result = $DB->get_record_sql($sql, $params);
                $view_certificate = '';
                if ($result) {
                    $view_certificate = $CFG->wwwroot . '/local/participant/user_certificates.php';
                }
                $completed_courses_data[] = array(
                    'view_certificate' => $view_certificate,
                    'comp_course_image' => $comp_course_image,
                    'course_url' => $CFG->wwwroot . '/course/view.php?id=' . $course->id,
                    'count' => $count,
                    'userid' => $userid,
                    'courseid' => $course->id,
                    'coursename' => $course->fullname,
                    'completiondate' => $date = date("d-M-Y", $completiondate),
                    'percentage' => $progress,
                    'course_completion' => $course_completion
                );
            } else {
                // Get the course object
                $today = time();
                //get enrolled course expiry date
                $sql = 'SELECT DISTINCT ue.timeend
                        FROM {user_enrolments} ue
                        JOIN {enrol} ue2 ON ue.enrolid = ue2.id
                        JOIN {course} c ON ue2.courseid = c.id
                        WHERE ue.userid = :userid AND c.id = :courseid';
                $enrollment_end_date = $DB->get_field_sql($sql, array('userid' => $USER->id, 'courseid' => $course->id));
                if ($enrollment_end_date == 0) {
                    $days_until_expiration = false;
                } else {
                    $days_until_expiration = date('d-M-Y', $enrollment_end_date);
                }
                $userid = $USER->id; // Replace with the ID of the user you want to
                $enrolled_count++;
                $imageurl = get_course_image_url($course->id); // Get the course image URL
                $paramsql = array('userid' => $userid, 'courseid' => $course->id);
                $sql = "SELECT rai.*, m.name AS modname
					FROM {block_recentlyaccesseditems} rai
					JOIN {course_modules} cm ON rai.cmid = cm.id
					JOIN {modules} m ON cm.module = m.id
					WHERE rai.userid = :userid AND rai.courseid = :courseid
					ORDER BY rai.timeaccess DESC LIMIT 1;";
                $records = $DB->get_record_sql($sql, $paramsql);
                // Get array of items by course. Use $order index to keep SQL sorted results.
                $viewurl = '';
                if (!empty($records)) {
                    $viewurl = (new moodle_url('/mod/' . $records->modname . '/view.php', array('id' => $records->cmid, 'course' => $course->id)));
                }
                $course_completion = block_course_records_build_progress_course_format($course);

                $enrolled_courses_data[] = array(
                    'course_image' => $imageurl,
                    'course_url' => $CFG->wwwroot . '/course/view.php?id=' . $course->id,
                    'userid' => $userid,
                    'expireday' => $days_until_expiration,
                    'courseid' => $course->id,
                    'coursename' => $course->fullname,
                    'percentage' => $progress,
                    'viewurl' => $viewurl,
                    'isresume' => ($viewurl) ? 1 : 0,
                    'dashoffset' => $dashoffset,
                    'course_completion' => $course_completion,
                );
            }
        }

        $sql = "SELECT cm.id as cmid,c.name as certificate_name, c.timemodified  as enrolled_date, ci.timecreated as completion_date
                FROM {customcert_issues} ci
                JOIN {course_modules} cm ON cm.module = (SELECT id FROM {modules} WHERE name = 'customcert')
                JOIN {customcert} c ON c.id = ci.customcertid AND c.id = cm.instance
                JOIN {modules} md ON md.id = cm.module
                WHERE ci.userid = :userid AND cm.deletioninprogress = 0;
                 ";
        $params = array('userid' => $USER->id);
        $certificates = $DB->get_records_sql($sql, $params);
        // Define the data to be passed to the template
        $data = array(
            'certificates' => count($certificates),
            'enrolled_course_view' => $CFG->wwwroot . '/local/participant/course_view.php',
            'certificate_page' => $CFG->wwwroot . '/local/participant/user_certificates.php',
            'completed_count' => $completed_count,
            'enrolled_count' => $enrolled_count,
            'enrolled_courses' => $enrolled_courses_data,
            'completed_courses' => $completed_courses_data,
            'iconPath' => $OUTPUT->image_url('totalcourse', 'blocks_user_dashboard'),
        );

        $renderer = $PAGE->get_renderer('core');
        $this->content = new stdClass();
        $this->content->text = $renderer->render_from_template('block_user_dashboard/userdashboard', $data);
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats()
    {
        return array('all' => true);
    }
    // Add any other necessary methods or functions here

}
