<?php

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/lib/tablelib.php');
require_once($CFG->dirroot.'/local/query/locallib.php');
require_once($CFG->dirroot.'/local/query/lib.php');

$user        = optional_param('user', 0, PARAM_INT); // User to display.
$date        = optional_param('date', 0, PARAM_INT); // Date to display.
$page        = optional_param('page', '0', PARAM_INT);     // Which page to show.
$perpage     = optional_param('perpage', 30, PARAM_INT); // How many per page.
$showusers   = optional_param('showusers', false, PARAM_BOOL); // Whether to show users if we're over our limit.
$choosequery = optional_param('choosequery', true, PARAM_BOOL);
$action   	 = optional_param('action', 0, PARAM_INT);
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
if($action){
	$params['action']= $action;
}
if ($queryformat !== '') {
    $params['download'] = $queryformat;
}
global $CFG, $OUTPUT, $USER, $SITE, $PAGE, $DB;
$homeurl = new moodle_url('/');
require_login();

$title = get_string('pluginname', 'local_query');
$heading = get_string('heading', 'local_query');
$url = new moodle_url('/local/query/index.php',$params);
if ($CFG->branch >= 25) {
    $context = context_system::instance();
} else {
    $context = get_system_context();
}
$result = get_user_role_id($USER->id);
if (is_siteadmin() || $result->roleid == '3' || $result->roleid == '4') {
    $PAGE->set_pagelayout('admin');
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_title($title);
    $PAGE->set_heading($heading);
    $localquery = new local_query_renderable($user, $showusers, $choosequery, true, $url, $date, $action, $queryformat, $page, $perpage, 'updatedon DESC');
    $output = $PAGE->get_renderer('local_query');
    if (!empty($choosequery)) {
        $localquery->setup_table();
        $localquery->tablequery->table_setup();
        if (empty($queryformat)) {
            echo $output->header();
            echo $output->render($localquery);
        } else {
            $localquery->download();
            exit();
        }
    }
    echo $OUTPUT->footer();
}