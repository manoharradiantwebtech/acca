<?php

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/lib/tablelib.php');
require_once($CFG->dirroot.'/local/query/locallib.php');
require_once($CFG->dirroot.'/local/query/lib.php');
$request_id   = optional_param('reqid', 0, PARAM_INT);
global $CFG, $OUTPUT, $USER, $SITE, $PAGE;
require_login();
$PAGE->set_pagelayout('admin');
if ($CFG->branch >= 25) {
    $context = context_system::instance();
} else {
    $context = get_system_context();
}
$ticket = $DB->get_record_sql("SELECT * FROM {query} WHERE  random_id = :req",['req'=> $request_id]);
if(!$ticket){
	print_error('nopermissiontoviewpage');
}
$result = get_user_role_id($USER->id);
if (is_siteadmin() || $result->roleid == '3' || $result->roleid == '4') {
	$ticket->is_rply =1;
}else{
	$ticket->is_rply =0;
}
$formdata = data_submitted();
if($formdata){
	if (!confirm_sesskey()) {
        print_error('nopermissiontoviewpage');
    }
	if($formdata->description != NULL){
		local_query_answers($formdata,$ticket);
	}
}
$title = get_string('pluginname', 'local_query');
$url = new moodle_url('/local/query/view.php',['reqid'=>$request_id]);
$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_title($title);
$PAGE->set_heading($title);
if(is_siteadmin()){
	admin_externalpage_setup('local_query');
}
$OUTPUT = $PAGE->get_renderer('local_query');
echo $OUTPUT->header();
$renderable = new \local_query\output\reply_query($request_id,$ticket,$url);
echo $OUTPUT->render($renderable);
echo $OUTPUT->footer();