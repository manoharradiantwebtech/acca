<?php


defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Query';
$string['gettheselquery'] = 'Show Result';
$string['query:reply'] = 'Can reply';
$string['query:replytoall'] = 'Can reply to all';
$string['name'] = 'Name';
$string['heading'] = 'User Query';
$string['email'] = 'Email';
$string['phone'] = 'Phone';
$string['page_url'] = 'Page';
$string['page_name'] = 'Page Name';
$string['show'] = 'View';
$string['createdon'] = 'opening date';
$string['updatedon'] = 'Last updated on';
$string['action'] ="Action";
$string['replyed'] = "Action completed";
$string['pending'] = "Action required";
$string['description'] ='Description';
$string['all'] ='All';
$string['datetimeformat'] ='%d %B %Y, %I:%M %p';
$string['question'] = "Question";
$string['answer'] = "Answer";
$string['escalation'] = "Escalation";
$string['escalateemail'] = "Query Escalation";
$string['reply_email'] = '<p>Hi,</p>
						<p>Thank you for writing to us with your concerns. The same has been answered in the query portal on the LMS.</p>
						<p>You can follow the below mentioned link as well.</p>
						<p>{$a->replyurl}</p>
						<p>Thank you and please let us know in case of any concerns. </p>';
$string['admin_email'] = '<p>Hi,</p>
						<p>Requester name - {$a->requesterfullname}</p>
						<p>{$a->description}</p>
						<p>{$a->replyurl}</p>';
$string['escalate_email'] = 	'<p>Hi,</p>
							<p>Escalate</p>
							<p>escalation</p>
							<p>footer</p>';