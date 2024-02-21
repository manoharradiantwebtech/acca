<?php
require_once('../../config.php');
require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_acca'));
$PAGE->set_heading(get_string('pluginname', 'local_acca'));

echo $OUTPUT->header();

// Your plugin content goes here

echo $OUTPUT->footer();
