<?php
require_once('../../config.php');

global $USER, $PAGE, $OUTPUT, $CFG, $DB;
// Start setting up the page
require_login();
$pagetitle = 'purchase history';
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/local/participant/user_purchase-history.php', $params);
$PAGE->set_pagetype('my-purchase-history');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');

echo $OUTPUT->header();

$userid = $USER->id;

 $sql = "SELECT c.*
                      FROM {course} AS c
                      JOIN {user_purchase_history} AS uph ON c.id = uph.courseid
                       WHERE uph.userid = :userid";
$enrolled_courses = $DB->get_records_sql($sql, array('userid' => $userid));
//Initialize arrays to store enrolled and completed courses
$enrolled_courses_data = array();
$completed_courses_data = array();
$limit = 2;
$count = 1;
foreach ($enrolled_courses as $course) {
    // Check if the course is completed
    $completion = new completion_info($course);
    $course_completion = block_course_records_build_progress_course_format($course);
    $userpurchase = $DB->get_record('user_purchase_history', array('courseid' => $course->id, 'userid' => $userid));
    if ($completion->is_course_complete($userid)) {
        $completed_courses_data[] = array(
            'count' => $count++,
            'course_url' => $CFG->wwwroot . '/course/view.php?id=' . $course->id,
            'coursename' => $course->fullname,
            'amount' => $userpurchase->amount,
            'course_enrollment' => date('d M, Y', $userpurchase->timemodified),
        );
    } else {
        $userpurchase = $DB->get_record('user_purchase_history', array('courseid' => $course->id, 'userid' => $userid));
        $enrolled_courses_data[] = array(
            'count' => $count++,
            'course_url' => $CFG->wwwroot . '/course/view.php?id=' . $course->id,
            'coursename' => $course->fullname,
            'amount' => $userpurchase->amount,
            'course_enrollment' => date('d M, Y', $userpurchase->timemodified),
        );
    }
}

// Define the data to be passed to the template
$data = array(
    'enrolled_courses' => $enrolled_courses_data,
    'completed_courses' => $completed_courses_data,
    'url' => $CFG->wwwroot . '/my',
);
$renderer = $PAGE->get_renderer('core');
echo $renderer->render_from_template('local_participant/purchase-history', $data);
echo $OUTPUT->footer();
?>