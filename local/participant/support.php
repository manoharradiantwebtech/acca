<?php
require_once('../../config.php');

global $USER, $PAGE, $OUTPUT, $CFG, $DB;
// Start setting up the page
require_login();
$pagetitle = 'support page';
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/local/participant/support.php', $params);
$PAGE->set_pagetype('support_page');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');

echo $OUTPUT->header();
$courses = enrol_get_users_courses($USER->id);
$course_data = new stdClass();
$data = [];

foreach ($courses as $course) {
    $modinfo = get_fast_modinfo($course->id);
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
    $days_until_expiration = floor(($course->enddate - $today) / 86400);

    $data[] = [
        'course_url' => $CFG->wwwroot . '/course/view.php?id=' . $course->id,
        'course_name' => $course->fullname,
        'count' => $count,
        'expireday' => $days_until_expiration,
        'completed_courses' => $completed_courses_name,
        'completed_on' => $completed_on
    ];
}
$course_data->contents = $data;
$renderer = $PAGE->get_renderer('core');
$course_data->url = $CFG->wwwroot . '/my';

echo $renderer->render_from_template('local_participant/user_support', $course_data);
echo $OUTPUT->footer();
?>