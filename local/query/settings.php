<?php


defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	// $settings = new admin_settingpage('local_query', get_string('escalateemail', 'local_query'));
	// $settings->add(new admin_setting_configcheckbox('local_query/active', get_string('activate', 'local_query'), get_string('Active', 'local_query'), 0));
	// $settings->add(new admin_setting_configduration('local_query/duration', get_string('duration', 'local_query'), '',432000));
	// $settings->add(new admin_setting_confightmleditor('local_query/alerttext', get_string('alerttext', 'local_query'), get_string('avalablefield', 'local_query'), get_string('email', 'local_query')));
	
	// $ADMIN->add('email', $settings);
	$ADMIN->add('email', new admin_externalpage('local_query',
            get_string('pluginname', 'local_query'),
            new moodle_url('/local/query/')
    ));

}
