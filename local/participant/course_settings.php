<?php
require_once('../../config.php');

global $USER, $PAGE, $OUTPUT, $CFG, $DB;
// Start setting up the page
require_login();
$pagetitle = 'course setting';
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/local/participant/course_settings.php', $params);
$PAGE->set_pagetype('course_settings');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');

echo $OUTPUT->header();
// Get the user ID
$data = array(
    'course_management' => $CFG->wwwroot . '/course/management.php',
    'course_customfield' => $CFG->wwwroot . '/course/customfield.php',
    'course_editcategory' => $CFG->wwwroot . '/course/editcategory.php?parent=0',
    'course_edit' => $CFG->wwwroot . '/course/edit.php?category=0',
    'course_backup' => $CFG->wwwroot . '/backup/restorefile.php?contextid=1',
    'course_settings' => $CFG->wwwroot . '/admin/settings.php?section=coursesettings',
    'course_downloadcoursecontent' => $CFG->wwwroot . '/admin/settings.php?section=downloadcoursecontent',
    'course_courserequest' => $CFG->wwwroot . '/admin/settings.php?section=courserequest',
    'course_pending' => $CFG->wwwroot . '/course/pending.php',
    'course_uploadcourse' => $CFG->wwwroot . '/admin/tool/uploadcourse/index.php',
    'activitychoosersettings' => $CFG->wwwroot . '/admin/settings.php?section=activitychoosersettings',
    'recommendations' => $CFG->wwwroot . '/course/recommendations.php',
    'backupgeneralsettings' => $CFG->wwwroot . '/admin/settings.php?section=backupgeneralsettings',
    'importgeneralsettings' => $CFG->wwwroot . '/admin/settings.php?section=importgeneralsettings',
    'automated' => $CFG->wwwroot . '/admin/settings.php?section=automated',
    'restoregeneralsettings' => $CFG->wwwroot . '/admin/settings.php?section=restoregeneralsettings',
    'asyncgeneralsettings' => $CFG->wwwroot . '/admin/settings.php?section=asyncgeneralsettings',

);
$renderer = $PAGE->get_renderer('core');

echo $renderer->render_from_template('local_participant/course_settings', $data);
echo $OUTPUT->footer();
?>