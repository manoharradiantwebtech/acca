<?php
require_once('../../config.php');

global $USER, $PAGE, $OUTPUT, $CFG, $DB;
// Start setting up the page
require_login();
$pagetitle = 'user settings';
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/local/participant/usersettings.php', $params);
$PAGE->set_pagetype('usersettings');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');

echo $OUTPUT->header();
// Get the user ID
$data = array(
    'user' => $CFG->wwwroot . '/admin/user.php',
    'user_bulk' => $CFG->wwwroot . '/admin/user/user_bulk.php',
    'user_editadvanced' => $CFG->wwwroot . '/user/editadvanced.php?id=-1',
    'usermanagement' => $CFG->wwwroot . '/admin/settings.php?section=usermanagement',
    'userdefaultpreferences' => $CFG->wwwroot . '/admin/settings.php?section=userdefaultpreferences',
    'user_profile' => $CFG->wwwroot . '/user/profile/index.php',
     'usercohort' => $CFG->wwwroot . '/cohort/index.php',
    'tool_iomadmerge' => $CFG->wwwroot . '/admin/category.php?category=tool_iomadmerge',
    'uploaduser' => $CFG->wwwroot . '/admin/tool/uploaduser/index.php',
    'uploaduser_picture' => $CFG->wwwroot . '/admin/tool/uploaduser/picture.php',
    
    
    'userpolicies' => $CFG->wwwroot . '/admin/settings.php?section=userpolicies',
    'admin_roles' => $CFG->wwwroot . '/admin/roles/admins.php',
    'admin_roles_manage' => $CFG->wwwroot . '/admin/roles/manage.php',
    'user_assign' => $CFG->wwwroot . '/admin/roles/assign.php?contextid=1',
    
    'user_role_check' => $CFG->wwwroot . '/admin/roles/check.php?contextid=1',
    'user_capability' => $CFG->wwwroot . '/admin/tool/capability/index.php',
    'cohortroles' => $CFG->wwwroot . '/admin/tool/cohortroles/index.php',
    'unsuproles' => $CFG->wwwroot . '/admin/tool/unsuproles/index.php',
     
     'policysettings' => $CFG->wwwroot . '/admin/settings.php?section=policysettings',
    'admin_privacysettings' => $CFG->wwwroot . '/admin/settings.php?section=privacysettings',
     
);
$renderer = $PAGE->get_renderer('core');

echo $renderer->render_from_template('local_participant/user_settings', $data);
echo $OUTPUT->footer();
?>