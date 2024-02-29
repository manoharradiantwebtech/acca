<?php
require_once('../../config.php');

global $USER, $PAGE, $OUTPUT, $CFG, $DB;
require_once($CFG->dirroot . '/badges/shared_certificate.php');

// Start setting up the page
require_login();
$pagetitle = 'user certificates';
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/local/participant/user_certificates.php', $params);
$PAGE->set_pagetype('my-certificates');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('coursecategory');
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->css('/badges/share.css');
$PAGE->requires->js('/local/participant/js/jquery-3.6.0.js');
$PAGE->requires->js('/badges/share.js');

echo $OUTPUT->header();
// Get the user ID
$user_id = $USER->id;
// SQL query to retrieve the certificate information for the user
$sql = "SELECT cm.id as cmid,c.name as certificate_name, c.timemodified  as enrolled_date, ci.timecreated as completion_date
FROM {customcert_issues} ci
JOIN {course_modules} cm ON cm.module = (SELECT id FROM {modules} WHERE name = 'customcert')
JOIN {customcert} c ON c.id = ci.customcertid AND c.id = cm.instance
JOIN {modules} md ON md.id = cm.module
WHERE ci.userid = :userid AND cm.deletioninprogress = 0;
";
$params = array('userid' => $user_id);

$result = $DB->get_records_sql($sql, $params);
$count = 1;
if ($result) {
    foreach ($result as $row) {
        $certificate[] = array(
            'cmid' => $row->cmid,
            'userid' => $user_id,
            'count' => $count++,
            'download-url' => $CFG->wwwroot . '/mod/customcert/view.php?id=' . $row->cmid . '&downloadissue=' . $USER->id . '',
            'name' => $row->certificate_name,
            'enrolled_date' => userdate($row->enrolled_date),
            'completion_date' => userdate($row->completion_date),
            'imagepath' => get_pdf_image_path($row->cmid, $user_id)
        );
    }
}

$script = '';
foreach($certificate as $certificateValue)
{
    $script .= '$("#share_button' . $certificateValue['cmid'] . $certificateValue['userid'] . '").jsSocials({
                url: "' . $certificateValue['imagepath'] . '",
                shares: [
                    { share: "facebook", logo: "fa fa-facebook-square" },
                    { share: "twitter", logo: "fa fa-twitter-square" },
                    { share: "linkedin", logo: "fa fa-linkedin-square" },
                ],
                shareIn: "popup",
                showLabel: false,
                showCount: false,
            });';
}

$data = array(
    'completed_certificates' => $certificate,
    'url' => $CFG->wwwroot . '/my'
);

// Embed JavaScript code within PHP
?>
<script>
    $(document).ready(function () {
        <?=$script?>
    });
</script>
<?php

$renderer = $PAGE->get_renderer('core');

echo $renderer->render_from_template('local_participant/usercertificates', $data);
echo $OUTPUT->footer();
?>
