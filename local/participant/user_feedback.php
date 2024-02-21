<?php
require_once('../../config.php');

global $USER, $PAGE, $OUTPUT, $CFG, $DB;
// Start setting up the page
require_login();
$pagetitle = 'user feedback';
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/local/participant/user_feedback.php', $params);
$PAGE->set_pagetype('my-feedback');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');
$PAGE->requires->js('/local/participant/js/jquery-3.6.0.js');
$PAGE->requires->js('/local/participant/js/custom.js');

echo $OUTPUT->header();
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['feedback-option']) && isset($_POST['feedback-text'])) {
    $feedbackSubject = '';
    switch ($_POST['feedback-option']) {
        case 'option1':
            $feedbackSubject = 'Course Feedback';
            break;
        case 'option2':
            $feedbackSubject = 'Product Feedback';
            break;
        case 'option3':
            $feedbackSubject = 'Faculty Feedback';
            break;
        default:
            $feedbackSubject = 'Unknown Feedback';
            break;
    }
    $data = new stdClass();
    $data->userid = $USER->id;
    $data->feedbacktype = $feedbackSubject;
    $data->feedback = $_POST['feedback-text'];
    $data->timemodified = time();
   // $DB->insert_record('local_participant_feedback', $data); // Replace 'your_table_name' with your actual table name
    // Insert record and show success message
    if ($DB->insert_record('local_participant_feedback', $data)) {
        $successMessage = get_string('insert_success', 'local_participant');
        echo $OUTPUT->notification($successMessage, 'notifysuccess');
    } else {
        $errorMessage = get_string('insert_failed', 'local_participant');
        echo $OUTPUT->notification($errorMessage, 'notifyproblem');
    }
}
$renderer = $PAGE->get_renderer('core');
echo $renderer->render_from_template('local_participant/feedback', $context);
echo $OUTPUT->footer();
?>