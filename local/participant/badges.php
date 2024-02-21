<?php
require_once('../../config.php');

global $USER, $PAGE, $OUTPUT, $CFG, $DB;
// Start setting up the page
require_login();
$pagetitle = 'user badges';
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/local/participant/badges.php', $params);
$PAGE->set_pagetype('my-badges');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');

echo $OUTPUT->header();
// Get the user ID
$user_id = $USER->id;

$sql = "SELECT bi.uniquehash,b.id, b.name, b.description
        FROM {badge} AS b
        JOIN {badge_issued} AS bi ON b.id = bi.badgeid
        WHERE bi.userid = :user_id";
$params = ['user_id' => $user_id];
$badges = $DB->get_records_sql($sql, $params);

// Check if the user has earned any badges
if (empty($badges)) {
    // Display an error message using Moodle's notification() function
    $successMessage = get_string('no_badges', 'local_participant');
    echo $OUTPUT->notification($successMessage, 'notifysuccess');
} else {
    // Display the badges on the front-end
    $badges_demo = array();
    foreach ($badges as $badge) {
        $imageurl = moodle_url::make_pluginfile_url(context_system::instance()->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
        $badges_demo[] = [
            'img' => $imageurl,
            'name' => $badge->name,
            'url' => $CFG->wwwroot . '/badges/shared_badges.php?hash=' . $badge->uniquehash . '',
        ];
    }

    $data = array(
        'badges' => $badges_demo,
    );
    $renderer = $PAGE->get_renderer('core');
    echo $renderer->render_from_template('local_participant/badges', $data);
}

echo $OUTPUT->footer();
?>