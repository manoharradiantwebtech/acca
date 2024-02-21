<?php
/**
 * Local Feedback Plugin Page
 *
 * @package    local_feedback
 * @category   local
 * @class      page
 * @copyright  Copyright (C) [Year] [Your Name]
 * @license    [License URL]
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/lib/tablelib.php');
//require_once($CFG->dirroot.'/local/feedback/locallib.php'); // Change to the correct path for your plugin.

// Define your parameters here.
$user        = optional_param('user', 0, PARAM_INT);
$date        = optional_param('date', 0, PARAM_INT);
$page        = optional_param('page', '0', PARAM_INT);
$perpage     = optional_param('perpage', 30, PARAM_INT);
$showusers   = optional_param('showusers', false, PARAM_BOOL);
$choosequery = optional_param('choosequery', true, PARAM_BOOL);
$action      = optional_param('action', 0, PARAM_INT);
$queryformat = optional_param('download', '', PARAM_ALPHA);
$params = array();

if ($user !== 0) {
    $params['user'] = $user;
}
if ($date !== 0) {
    $params['date'] = $date;
}

if ($page !== '0') {
    $params['page'] = $page;
}
if ($perpage !== 30) {
    $params['perpage'] = $perpage;
}
if ($showusers) {
    $params['showusers'] = $showusers;
}
if ($choosequery) {
    $params['choosequery'] = $choosequery;
}
if ($action) {
    $params['action'] = $action;
}
if ($queryformat !== '') {
    $params['download'] = $queryformat;
}

// Initialize Moodle.
global $CFG, $OUTPUT, $USER, $SITE, $PAGE;
require_login();

// Set up your plugin information.
$pluginname = get_string('pluginname', 'local_feedback'); // Adjust for your plugin's language file.
$title = $pluginname;
$heading = get_string('heading', 'local_feedback'); // Adjust for your plugin's language file.

// Set up the page.
$homeurl = new moodle_url('/');
$url = new moodle_url('/local/feedback/index.php', $params); // Change to your plugin's index page.
$context = context_system::instance();
//require_capability('local/feedback:capability', $context); // Change capability as needed.

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($heading);
// Debugging statement
admin_externalpage_setup('local_query');

// Create your plugin-specific object (e.g., local_feedback_renderable).
$feedback = new local_feedback_renderable($user, $showusers, $choosequery, true, $url, $date, $action, $queryformat, $page, $perpage, 'updatedon DESC');
$output = $PAGE->get_renderer('local_feedback');

// Check if choosequery is not empty.
if (!empty($choosequery)) {
    // Setup your table or do any other actions specific to your plugin.
    $feedback->setup_table();
    $feedback->tablequery->table_setup();

    if (empty($queryformat)) {
        echo $output->header();
        echo $output->render($feedback);
    } else {
        $feedback->download();
        exit();
    }
}

echo $OUTPUT->footer();
