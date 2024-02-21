<?php


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/lib/tablelib.php');
require_once($CFG->libdir.'/adminlib.php');
global $PAGE;
$id = optional_param('id', 0, PARAM_INT);
require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/view.php');

$download = optional_param('download', '', PARAM_ALPHA);
if ($id) {
    $cm = get_coursemodule_from_id('versereminder', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $reminder = $DB->get_record('versereminder', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($a) {
    $reminder = $DB->get_record('versereminder', array('id' => $a), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $reminder->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('versereminder', $reminder->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}
$table = new table_sql(time());
$table->is_downloading($download, 'Reminder', '');
if (!$table->is_downloading()) {
    $PAGE->set_title('Reminder');
	$PAGE->set_pagelayout('admin');
    $PAGE->set_heading('Reminder Inprogress');
	
    $PAGE->navbar->add('Reminder Inprogress', new moodle_url("$CFG->wwwroot/mod/versereminder/view.php?id=$id"));
    echo $OUTPUT->header();
}
$table->define_columns(['username','email','emailsent','emailtime','manageremailsent','manageremailtime','comanageremailsent','comanageremailtime']);
$table->define_headers(['Fullname','Email','Total Sent Reminder','Next Reminder','Total Sent Reminder (Manager1)','Next Reminder (Manager1)','Total Sent Reminder (Manager2)','Next Reminder (Manager2)']);
$table->set_sql("CONCAT(u.firstname,' ',u.lastname) as username,
			u.email as email,
			rip.emailsent,
			DATE_FORMAT(FROM_UNIXTIME(rip.emailtime),'%d-%M-%Y') as emailtime,
			rip.manageremailsent,
			DATE_FORMAT(FROM_UNIXTIME(rip.manageremailtime),'%d-%M-%Y') as manageremailtime,
			rip.comanageremailsent,
			DATE_FORMAT(FROM_UNIXTIME(rip.comanageremailtime),'%d-%M-%Y') as comanageremailtime",
		'{versereminder_inprogress} rip
		LEFT JOIN {versereminder} r ON r.id = rip.versereminder
        INNER JOIN {user} u ON u.id = rip.userid AND u.suspended = 0
		LEFT JOIN {course_modules_completion} cmc ON cmc.coursemoduleid = r.suppresstarget AND cmc.userid = rip.userid',
		'rip.issend = 1 AND cmc.completionstate IS NULL AND rip.versereminder='.$reminder->id);
$table->define_baseurl("$CFG->wwwroot/mod/versereminder/view.php?id=$id");
$table->out(40, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}