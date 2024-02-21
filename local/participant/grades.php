<?php
require_once('../../config.php');

global $USER, $PAGE, $OUTPUT, $CFG, $DB;

// Start setting up the page
require_login();
$pagetitle = 'grades setting';
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/local/participant/grades.php', $params);
$PAGE->set_pagetype('my-grades');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');

echo $OUTPUT->header();

// Get the user ID
$data = array(
    'gradessettings' => $CFG->wwwroot . '/admin/settings.php?section=gradessettings',
    'gradecategorysettings' => $CFG->wwwroot . '/admin/settings.php?section=gradecategorysettings',
    'gradeitemsettings' => $CFG->wwwroot . '/admin/settings.php?section=gradeitemsettings',
    'scale' => $CFG->wwwroot . '/grade/edit/scale/index.php',
    'grade' => $CFG->wwwroot . '/grade/edit/letter/index.php',
    'gradereportgrader' => $CFG->wwwroot . '/admin/settings.php?section=gradereportgrader',
    'gradereporthistory' => $CFG->wwwroot . '/admin/settings.php?section=gradereporthistory',
    'gradereportoverview' => $CFG->wwwroot . '/admin/settings.php?section=gradereportoverview',
    'gradereportuser' => $CFG->wwwroot . '/admin/settings.php?section=gradereportuser',
);

$renderer = $PAGE->get_renderer('core');
echo $renderer->render_from_template('local_participant/grades', $data);
echo $OUTPUT->footer();
?>