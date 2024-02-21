<?php
require_once('../../config.php');

global $USER, $PAGE, $OUTPUT, $CFG, $DB;
// Start setting up the page
require_login();


$pagetitle = 'Appearance settings';
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/local/participant/appearancesetting.php', $params);
$PAGE->set_pagetype('appearancesetting');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');

echo $OUTPUT->header();
$systemcontext = context_system::instance();

require_capability('local/participant:iomadviewall', $systemcontext);
// Get the user ID
$data = array(
    'logos' => $CFG->wwwroot . '/admin/settings.php?section=logos',
    'coursecolors' => $CFG->wwwroot . '/admin/settings.php?section=coursecolors',
    'navigation' => $CFG->wwwroot . '/admin/settings.php?section=navigation',
    'coursecontact' => $CFG->wwwroot . '/admin/settings.php?section=coursecontact',
    'tag' => $CFG->wwwroot . '/tag/manage.php',
    
     'themesettings' => $CFG->wwwroot . '/admin/settings.php?section=themesettings',
      'themeindex' => $CFG->wwwroot . '/theme/index.php',
);
$renderer = $PAGE->get_renderer('c