<?php
require_once('../../config.php');

global $PAGE, $OUTPUT, $CFG, $DB;
// Start setting up the page
require_login();
$pagetitle = 'course_view';
$params = array();
$userid = optional_param('id', 0, PARAM_INT);
$context = context_user::instance($userid);


$PAGE->set_context($context);
$PAGE->set_url('/local/participant/course_view.php', $params);
$PAGE->set_pagetype('my-course_view');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');
$PAGE->requires->js('/local/participant/js/jquery-3.5.1.min.js');
$PAGE->requires->js('/local/participant/js/custom.js');
$PAGE->requires->css('/local/participant/css/style.css');

echo $OUTPUT->header();
// Get all the courses that the current user is enrolled in
$userid = $userid;
$enrolled_courses = enrol_get_users_courses($userid);

//Initialize arrays to store enrolled and completed courses
$enrolled_courses_data = array();
$completed_courses_data = array();
$limit = 2;
foreach ($enrolled_courses as $course) {
	
    $progress = \core_completion\progress::get_course_progress_percentage($course,$userid);
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
    $sql = 'SELECT DISTINCT ue.timeend
                        FROM {user_enrolments} ue
                        JOIN {enrol} ue2 ON ue.enrolid = ue2.id
                        JOIN {course} c ON ue2.courseid = c.id
                        WHERE ue.userid = :userid AND c.id = :courseid';
    $enrollment_end_date = $DB->get_field_sql($sql, array('userid' => $userid,'courseid' => $course->id));
    if($enrollment_end_date == 0) {
        $days_until_expiration = false;
    } else {
        $days_until_expiration = date('d-M-Y', $enrollment_end_date);
    }

    // Check if the course is completed
    $completion = new completion_info($course);
    // $course_completion = block_course_records_build_progress_course_format($course);
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
            'course_completion' => ['completion_percentage'=>$progress]
        );
    } else {
        $paramsql = array('userid' => $userid, 'courseid' => $course->id);
        $imageurl = get_course_image_url($course->id); // Get the course image URL
        $sql = "SELECT rai.*, m.name AS modname
                 FROM {block_recentlyaccesseditems} rai
                 JOIN {course_modules} cm ON rai.cmid = cm.id
                 JOIN {modules} m ON cm.module = m.id
                  WHERE rai.userid = :userid AND rai.courseid = :courseid
                  ORDER BY rai.timeaccess DESC LIMIT 1;";
        $records = $DB->get_record_sql($sql, $paramsql);
        if (!empty($records)) {
            $viewurl= (new moodle_url('/mod/'.$records->modname.'/view.php',
                array('id' => $records->cmid)));
        }

        $enrolled_courses_data[] = array(
            'course_image' => $imageurl,
            'viewurl' => $viewurl,
            'course_url' => $CFG->wwwroot . '/course/view.php?id=' . $course->id,
            'count' => $count,
            'expireday' => $days_until_expiration,
            'userid' => $userid,
            'courseid' => $course->id,
            'coursename' => $course->fullname,
            'percentage' => $progress,
            'course_completion' => ['completion_percentage'=>$progress],
            'isresume' => ($viewurl)?1:0
        );
    }
}

// Define the data to be passed to the template
$data = array(
    'enrolled_courses' => $enrolled_courses_data,
    'completed_courses' => $completed_courses_data,
);
$renderer = $PAGE->get_renderer('core');

echo $renderer->render_from_template('local_participant/usercourses', $data);
echo $OUTPUT->footer();
?>