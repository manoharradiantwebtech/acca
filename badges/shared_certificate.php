<?php
// This file is part of the customcert module for Moodle - http://moodle.org/
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
 * Handles viewing a customcert.
 *
 * @package    mod_customcert
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');
global $CFG, $DB;
require_once($CFG->libdir."/completionlib.php");


$id = required_param('id', PARAM_INT);
$userid = required_param('sid', PARAM_INT);
$downloadown = optional_param('downloadown', false, PARAM_BOOL);
$downloadtable = optional_param('download', null, PARAM_ALPHA);
$downloadissue = optional_param('downloadissue', 0, PARAM_INT);
$deleteissue = optional_param('deleteissue', 0, PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', \mod_customcert\certificate::CUSTOMCERT_PER_PAGE, PARAM_INT);

$cm = get_coursemodule_from_id('customcert', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$customcert = $DB->get_record('customcert', array('id' => $cm->instance), '*', MUST_EXIST);
$template = $DB->get_record('customcert_templates', array('id' => $customcert->templateid), '*', MUST_EXIST);

// Load the completion_info class
$url = new moodle_url('/badges/shared_certificate.php?', array('id' => $id, 'userid' => $userid , 'downloadown' => 1));
// Check that we are not downloading a certificate PDF.
$CFG->additionalhtmlhead .= '<meta property="og:url" content="'.$url.'" />';
// Check that we are not downloading a certificate PDF.

if ($userid) { // Output to pdf.
    if ($downloadown) {
        // Create new customcert issue record if one does not already exist.
        if (!$DB->record_exists('customcert_issues', array('userid' => $userid, 'customcertid' => $customcert->id))) {
            \mod_customcert\certificate::issue_certificate($customcert->id, $userid);
        }

        // Set the custom certificate as viewed.
        $completion = new \completion_info($course);
        $completion->set_module_viewed($cm);
    } else if ($downloadissue) {
        $userid = $downloadissue;
    }

    \core\session\manager::write_close();

    // Now we want to generate the PDF.
    $template = new \mod_customcert\template($template);
    $template->generate_pdf(false, $userid);
    exit();
}
