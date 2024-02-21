<?php

use core_completion\progress;

require_once('../../config.php');

global $USER, $PAGE, $OUTPUT, $CFG, $DB;
// Start setting up the page
require_login();
$pagetitle = 'dashboard view';
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/local/participant/dashboard.php', $params);
$PAGE->set_pagetype('dashboard');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');

echo $OUTPUT->header();
$userid = $USER->id;
$enrolled_courses = enrol_get_users_courses($userid);

// Initialize arrays to store enrolled and completed courses
$enrolled_courses_data = array();
$completed_courses_data = array();
$completed_count=0;
$enrolled_count = 0;
foreach ($enrolled_courses as $course) {
    // Get the course context
    $progress = \core_completion\progress::get_course_progress_percentage($course);
    $dashoffset = 100 - fmod(100, ($progress + 5) );
    $hasprogress = false;
    if ($progress === 0 || $progress > 0) {
        $hasprogress = true;
    }
    $progress = floor($progress);
    $modinfo = get_fast_modinfo($course->id);
    $course_context = context_course::instance($course->id);

    // Check if the course is completed
    $completion = new completion_info($course);

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
        $completed_count++;
        $completed_courses_data[] = array(
            'course_url' => $CFG->wwwroot . '/course/view.php?id=' . $course->id,
            'coursename' => $course->fullname,
        );
    } else {
        // Get the course object

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


// Get array of items by course. Use $order index to keep sql sorted results.
        if (!empty($records)) {
            $viewurl= (new moodle_url('/mod/'.$records->modname.'/view.php',
                array('id' => $records->cmid)));
        }
        $course_completion = block_course_records_build_progress_course_format($course);
        $enrolled_courses_data[] = array(
            'course_image' => $imageurl,
            'course_url' => $CFG->wwwroot . '/course/view.php?id=' . $course->id,
            'userid' => $userid,
            'courseid' => $course->id,
            'coursename' => $course->fullname,
            'percentage' => $progress,
            'viewurl' => $viewurl,
            'dashoffset' => $dashoffset,
            'course_completion' => $course_completion,
        );
    }
}
$certificates = $DB->count_records('customcert_issues', array('userid' => $USER->id));
// Define the data to be passed to the template
$data = array(
    'certificates' => $certificates,
    'enrolled_course_view' => $CFG->wwwroot . '/local/participant/course_view.php',
    'certificate_page' => $CFG->wwwroot . '/local/participant/user_certificates.php',
    'completed_count' => $completed_count,
    'enrolled_count' => $enrolled_count,
    'enrolled_courses' => $enrolled_courses_data,
    'completed_courses' => $completed_courses_data,
);
$renderer = $PAGE->get_renderer('core');

echo $renderer->render_from_template('local_participant/userdashboard', $data);
echo $OUTPUT->footer();