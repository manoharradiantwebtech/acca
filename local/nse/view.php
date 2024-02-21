<?php
require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/lib/tablelib.php');
require_once($CFG->dirroot.'/local/query/locallib.php');
global $CFG, $OUTPUT, $USER, $SITE, $PAGE;
$homeurl = new moodle_url('/');
require_login();
$page        = optional_param('page', '0', PARAM_INT);     // Which page to show.
$perpage     = optional_param('perpage', 30, PARAM_INT); // How many per page.
$action   	 = optional_param('action', 0, PARAM_INT);
$queryformat = optional_param('download', '', PARAM_ALPHA);
$filter   	 = optional_param('filter', '1', PARAM_BOOL);
$choosequery = optional_param('choosequery', true, PARAM_BOOL);
$courseid = optional_param('courseid', false, PARAM_BOOL);

if ($choosequery) {
    $params['choosequery'] = $choosequery;
}
$params = [];
if ($page !== '0') {
    $params['page'] = $page;
}
if ($perpage !== 30) {
    $params['perpage'] = $perpage;
}
if($action){
	$params['action']= $action;
}
if ($filter) {
    $params['filter'] = $filter;
}
if ($queryformat !== '') {
    $params['download'] = $queryformat;
}
if ($CFG->branch >= 25) {
    $context = context_system::instance();
} else {
    $context = get_system_context();
}
$title = get_string('pluginname', 'local_nse');
$heading = get_string('pluginname', 'local_nse');
$url = new moodle_url('/local/nse/view.php',$params);

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($heading);
$localnse = new local_nse_renderable($courseid,$choosequery,$url,$action,$queryformat, $filter,$page, $perpage, 'updateddate DESC');
$output = $PAGE->get_renderer('local_nse');
if (!empty($choosequery)) {
	$localnse->setup_table();
	$localnse->tablense->table_setup();
	if (empty($queryformat)) {
		echo $output->header();
		echo $output->render($localnse);
	}else{
		$localnse->download();
         exit();
	}
}
echo $OUTPUT->footer();
