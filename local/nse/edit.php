<?php 
require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('edit_form.php');
require_once("lib.php");
require_login();
$id = optional_param('id', 0, PARAM_INT); 
$returnurl = new moodle_url($CFG->wwwroot . '/local/nse/view.php');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$pageparams = array('id' => $id);
$PAGE->set_url('/local/nse/edit.php', $pageparams);
if($id > 0){
	$initialdata = $DB->get_record('nse_course', ['id'=>$id], '*', MUST_EXIST);
}else{
	$initialdata = new stdClass;
}

$editform = new nse_edit_form(null, $initialdata);
if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()){
	if(addupdate_recordtoDB($data)){
		redirect($returnurl);
	}
}

$PAGE->set_title(get_string('pluginname', 'local_nse'));
$editform->set_data($initialdata);
$pagedesc = (!$id)? "Add new record" : 'Edit Record';
echo $OUTPUT->header();
echo $OUTPUT->heading($pagedesc);
$editform->display();
echo $OUTPUT->footer();