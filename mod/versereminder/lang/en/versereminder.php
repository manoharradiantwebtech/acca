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
 * Strings for versereminder.
 *
 * @package    mod_versereminder
 * @author     Peter Bulmer <peter.bulmer@catlayst.net.nz>
 * @copyright  2016 Catalyst IT {@link http://www.catalyst.net.nz}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Reminder Verse';
$string['versereminder'] = 'Reminder Verse';
$string['task_verserimeinder'] = ' Verse Reminder';
$string['pluginadministration'] = '';
$string['modulename'] = 'Reminder Verse';
$string['modulenameplural'] = 'versereminders';
$string['modulename_help'] = ' send email versereminder to student and reporting manager`s';

// Alphabetized.
$string['activitycompleted'] = 'This activity has been marked as complete';
$string['afterdelay'] = 'After delay';
$string['completion'] = 'Completion';
$string['completionwillturnon'] = 'Note that adding this activity to the course will enable activity completion.';
$string['completeattimex'] = 'This activity will complete at {$a}';
$string['completiontime'] = 'Completion time';
$string['crontask'] = 'versereminder cron task';
$string['days'] = 'Days';
$string['duration'] = 'Duration';
$string['duration_help'] = '<p>The versereminder duration is the period of time between a user starting a versereminder, and being marked as finished.
The versereminder duration is specified as a period length (eg Weeks) and number of period (eg 7).</p>

<p>This example would mean that a user starting a versereminder period now would be marked as compete in 7 weeks time.</p>
';
$string['thirdpartyemails'] = 'Third-party recipients';
$string['thirdpartyemails_help'] = 'A comma-separated list of email addresses for third-parties that should be receiving an email when the user does.';
$string['emailcontent'] = 'Email content (User)';
$string['manageremailcontent'] = 'Email content (manager)';
$string['emailcontent_help'] = 'When the module sends a user an email, it takes the email content from this field.';
$string['emailcontentthirdparty'] = 'Email content (Third-party)';
$string['emailcontentthirdparty_help'] = 'When the module sends a third-party an email, it takes the email content from this field.';
$string['emailcontentthirdpartydefaultvalue'] = 'This is a versereminder email from course %coursename%, regarding user %userfirstname% %userlastname%.';
$string['emailcontentdefaultvalue'] = '<p>Dear %userfirstname% %userlastname%,</p>
<p>Completion of the E-learning / Evaluation form and Post Course Assessment is/was due on <strong>%duedate%</strong>.</p>
<p><strong><span style="color: #4f2d7f;"><a href="https://ilearn.wcgt.in/course/view.php?id=%courseid%"><span style="color: #4f2d7f;">Click</span></a></span> the following link to launch the e-learning/Evaluation:</strong></p>
<p>You are required to comply with the above by <strong>%duedate%</strong>.</p>
<p>Non-compliance will be tracked and would impact your year end appraisals.</p>
<p>Incase of any issues in accessing the link please contact <a href="mailto:iLearn@in.gt.com"><strong>iLearn@in.gt.com<br /></strong></a></p>
<p>Thank you</p>';
$string['emaildelay'] = 'Email frequency';

$string['emaildelay_help'] = 'When module is set to email users "after delay", this setting controls how long the delay is.';
$string['emailrecipient'] = 'Email recipient(s)';
$string['emailsubjectdefault'] = '%coursefullname% - Evaluation Form and Post Course Assessment.';
$string['emailrecipient_help'] = 'When an email needs to be sent out to prompt a user\'s versereminder with the course, this setting controls if an email is sent to the user, their manager, or both.';
$string['emailsubject'] = 'Email subject (User)';
$string['emailsubject_help'] = 'When the module sends a user an email, it takes the email subject from this field.';
$string['emailsubjectthirdparty'] = 'Email subject (Third-party)';
$string['emailsubjectthirdparty_help'] = 'When the module sends a third-party an email, it takes the email subject from this field.';
$string['emailtime'] = 'Next Email';
$string['emailtouser'] = 'Total';
$string['emailuser'] = 'Email user';
$string['emailuser_help'] = 'When the activity should email users: <ul>
<li>Never: Don\'t email users.</li>
<li>On versereminder completion: Email the user when the versereminder activity is completed.</li>
<li>After Delay: Email the user a set time after they have started the module.</li>
</ul>';
$string['frequencytoohigh'] = 'The maximum versereminder count with the delay period you have set is {$a}.';
$string['periodtoolowfromstudent'] = 'The delay is too low - it must be at least greater than to user.';
$string['periodtoolow'] = 'The delay is too low - it must be at least 5 minutes.';
$string['hours'] = 'Hours';
$string['introdefaultvalue'] = 'This is a versereminder activity.  Its purpose is to enforce a time lapse between the activities which preceed it, and the activities which follow it.';
$string['minutes'] = 'Minutes';
$string['never'] = 'Never';
$string['noemailattimex'] = 'Message scheduled for {$a} will not be sent because you have completed the target activity';
$string['nosuppresstarget'] = 'No target activity selected';
$string['oncompletion'] = 'On versereminder completion';
$string['receiveemailattimex'] = 'Message will be sent on {$a}.';
$string['receiveemailattimexunless'] = 'Message will be sent on {$a} unless you complete target activity.';
$string['versereminder:addinstance'] = 'versereminder:addinstance';
$string['versereminder:startversereminder'] = 'Start versereminder';
$string['versereminder:getnotifications'] = 'Receive notification of versereminder completions';
$string['versereminder:editversereminderduration'] = 'Edit versereminder Duration';
$string['versereminderduration'] = 'versereminder duration';
$string['deletelogs'] = "Delete active versereminder";
$string['reminderversefieldset'] = 'versereminder details';
$string['versereminderintro'] = 'versereminder intro';
$string['verseremindername'] = 'versereminder name';
$string['verseremindersinprogress'] = 'versereminders in progress';
$string['verseremindercount'] = 'Maximum emails';
$string['verseremindercount_help'] = 'This is the number of times an e-mail is sent after each delay period. There are some limits to the values you can use<ul>
<li>less than 24 hrs - limit of 2 versereminders.</li>
<li>less than 5 days - limit of 10 versereminders.</li>
<li>less than 15 days - limit of 26 versereminders.</li>
<li>over 15 days - maximum limit of 40 versereminders.</li></ul>';
$string['search:activity'] = 'versereminder - activity information';
$string['suppressemail'] = 'Suppress email if target activity complete';
$string['suppressemail_help'] = 'This option instructs the activity to suppress emails to users where a named activity is complete.';
$string['suppresstarget'] = 'Target activity.';
$string['suppresstarget_help'] = 'Use this dropdown to choose which activity should be checked for completion before sending the versereminder email.';
$string['userandmanager'] = 'User and Manager';
$string['userenroll'] = 'User enrollment';
$string['coursestart'] = 'Course start';
$string['weeks'] = 'Weeks';
$string['startdelay'] = 'Activity start after';
$string['donothing'] = 'Do Nothing';
$string['referancedate'] = 'Target';
$string['minimumrestriction'] = "User";
$string['manageremaildelay'] = 'Email delay';
$string['manageremailfrequencydelay'] = 'Email frequency';
$string['manageremailcontent'] = 'Email content (Manager)';
$string['manageremailcontent_help'] = 'When the module sends a user\'s manager an email.';
$string['managersemail'] = 'Active';
$string['managersemail_help'] = 'When an email needs to be sent out to prompt a reporting Manager\'s versereminder with the course, this setting controls if an email is sent to the user, their manager cc.';
$string['manageremail'] = "Manager Level 1";
$string['manageremailsubject'] = 'Email subject (Manager)';
$string['manageremailsubject_help'] = 'When the module sends a user\'s manager an email, it takes the email subject from this field.';
$string['manager'] = 'Email to Process Manager';
$string['partner'] = 'Reporting Partner';
$string['partneremail'] = 'Emailed to Manager2';
$string['emailmanager'] = 'Emailed to Manager1';
$string['comanagermail'] = "Manager Level 2";
$string['comanagersemail'] = 'Active';
$string['comanagersemail_help'] = "When an email needs to be sent out to prompt a reporting Partner\'s versereminder with the course, this setting controls if an email is sent to the user, their manager cc.";
$string['comanagersemaildelay'] = 'Email delay';
$string['comanagersemailfrequencydelay'] = 'Email frequency ';
$string['activitythirdparty'] = "Third-party email";
$string['strftimedatetimeshort'] = '%d/%m/%Y %H:%M';
$string['versereminderclosedon'] = 'Close the reminder';