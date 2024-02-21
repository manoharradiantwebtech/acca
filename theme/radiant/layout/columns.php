<?php

// This file is part of Moodle - http://moodle.org/

//

// Moodle is free software: you can redistribute it and/or modify

// it under the terms of the GNU General Public License as published by

// the Free Software Foundation, either version 3 of the License, or

// (at your option) any later version.

//

// Moodle is distributed in the hope that it will be useful,

// but WITHOUT ANY WARRANTY; without even the implied warranty of

// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

// GNU General Public License for more details.

//

// You should have received a copy of the GNU General Public License

// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**

* The columns layout for the radiant theme.

*

* @package   theme_radiant

* @copyright 2023 radiant

* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

*/

defined('MOODLE_INTERNAL') || die();

global $PAGE, $USER, $DB;

$bodyattributes = $OUTPUT->body_attributes();

$blockspre = $OUTPUT->blocks('side-pre');

$blockspost = $OUTPUT->blocks('side-post');

$PAGE->requires->js('/local/participant/js/jquery-3.6.0.js');

$PAGE->requires->js('/theme/radiant/js/mobile.js');

$PAGE->requires->js('/theme/radiant/js/navinit.js');

$PAGE->requires->css('/local/participant/css/main.css');

if (is_siteadmin()) {
    $PAGE->requires->css('/local/participant/css/style.css');
}

$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);

$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);

$PAGE->set_secondary_navigation(false);

$renderer = $PAGE->get_renderer('core');

$header = $PAGE->activityheader;

$headercontent = $header->export_for_template($renderer);

$role = $DB->get_record('role_assignments', array('userid' => $USER->id));

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockspre,
    'sidepostblocks' => $blockspost,
    'haspreblocks' => $hassidepre,
    'haspostblocks' => $hassidepost,
    'bodyattributes' => $bodyattributes,
    'headercontent' => $headercontent,
];
if (strpos($_SERVER['REQUEST_URI'], '/mod/resource/') !== false) {
    global $DB;
    $cmid = optional_param('id', 0, PARAM_INT); //
    if ($cmid) {
        $cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST); // Get the course module from the database.
        $module = $DB->get_record($cm->modname, array('id' => $cm->instance), '*', MUST_EXIST); // Get the module (resource) from the database.
        $section = $DB->get_record('course_sections', array('id' => $cm->section), '*', MUST_EXIST); // Get the section from the database.
        $filename = $module->name; // Get the filename of the video resource.
        $sectionname = $section->name; // Get the name of the section the resource is in.
        if (empty($sectionname)) {
            // Load the course module
            $coursemodule = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
            // Get the course object
            $course = get_course($coursemodule->course);
            // Create the course sections if they don't exist
            course_create_sections_if_missing($course, array($coursemodule->section));
            // Load the course module info
            $modinfo = get_fast_modinfo($course);
            // Get the section of the course module
            $sectionid = $modinfo->get_section_info($coursemodule->section)->id;
            $section = $modinfo->get_section_info($sectionid, IGNORE_MISSING);
            $sectionname = get_section_name($course, $sectionid);
        }
        $templatecontext['sectionname'] = $sectionname;
        $templatecontext['resources_name'] = $filename;
    }
}
$templatecontext['navigation'] = true;
if (strpos($_SERVER['REQUEST_URI'], '/mod') !== false && !is_siteadmin()) {
?>

    <style>

        .page_full_header {

            margin-left: 0px !important;

        }

       #block-region-side-pre {

            display: none;

        }

        #region-main {

            margin-left: 23px;

        }

        #page-footer {

            display: none;

        }

    </style>

    <?php

    global $COURSE, $CFG, $DB, $USER;

    $role = $DB->get_record('role_assignments', array('userid' => $USER->id));

    $tenantuser = $DB->get_record('user', array('theme' => 'radiant', 'id' => $USER->id));
	$templatecontext['haspreblocks'] = true;
    $templatecontext['navigation'] = false;
    $templatecontext['modpage'] = true;
    $templatecontext['cc'] = false;
    $templatecontext['dashboard_url'] = $CFG->wwwroot . '/my';
    $templatecontext['course_name'] = $COURSE->fullname;
    $templatecontext['course_url'] = $CFG->wwwroot . '/course/view.php?id=' . $COURSE->id . '';
    $courseid = $COURSE->id; // Replace with the ID of your course
    $modinfo = get_fast_modinfo($courseid);
    $sections = $modinfo->get_section_info_all();
    $count = 0;
    foreach ($sections as $section) {
        $sectiondata = array(
            'sectionname' => get_section_name($COURSE, $section),
            'count' => $count++,
        );
        $modinfo = get_fast_modinfo($courseid);

        $activities = get_section_activities($courseid, $section->id);

        foreach ($activities as $activity) {

            $activitydata = array(

                'name' => $activity->name,

                'mod_url' => new moodle_url('/mod/' . $activity->modname . '/view.php', array('id' => $activity->id)),

            );

            $sectiondata['activities'][] = $activitydata;

        }

        $sectionvalues[] = $sectiondata; //
    }
    $templatecontext['section_data'] = $sectionvalues;
}
if (!$role->roleid == '5') {
    $templatecontext['other_users'] = true;
}

echo $OUTPUT->render_from_template('theme_radiant/columns', $templatecontext);
