<?php
defined('MOODLE_INTERNAL') || die();
$capabilities = array(
    'local/query:reply' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'reply',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
			'student' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'query/query:reply',
    ),
	'local/query:replyall' => array(
        'captype' => 'replyall',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

);
?>