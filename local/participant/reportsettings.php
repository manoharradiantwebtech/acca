<?php
require_once('../../config.php');

global $USER, $PAGE, $OUTPUT, $CFG, $DB;

// Start setting up the page
require_login();
$pagetitle = 'Report Setting';
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/local/participant/reportsettings.php', $params);
$PAGE->set_pagetype('reportsettings');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');

echo $OUTPUT->header();

// Get the user ID
$data = array(
    'report_configlog' => $CFG->wwwroot . '/report/configlog/index.php',
    'report_coursestats' => $CFG->wwwroot . '/report/coursestats/index.php',
    'report_eventlist' => $CFG->wwwroot . '/report/eventlist/index.php',
    'report_log' => $CFG->wwwroot . '/report/log/index.php?id=0',
    'report_loglive' => $CFG->wwwroot . '/report/loglive/index.php',
    'report_reportbuilder' => $CFG->wwwroot . '/reportbuilder/index.php',
);

$renderer = $PAGE->get_renderer('core');
echo $renderer->render_from_template('local_participant/report_settings', $data);
echo $OUTPUT->footer();
?>