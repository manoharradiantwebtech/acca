<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig){
	$settings = new admin_settingpage( 'local_nse', 'NSE settings' );
	$ADMIN->add( 'localplugins', $settings );
	$settings->add( new admin_setting_configtext(
		'local_nse/clientid',
		'Client ID: Key',
		'',
		'No Key Defined',
		PARAM_TEXT
	) );
	$settings->add( new admin_setting_configtext(
		'local_nse/secret',
		'Secret: Key',
		'',
		'No Key Defined',
		PARAM_TEXT
	) );
	$settings->add( new admin_setting_configtext(
		'local_nse/host',
		'Host URL',
		'',
		'No Key Defined',
		PARAM_TEXT
	) );
	
	$settings->add( new admin_setting_configtext(
		'local_nse/apikey',
		'API Key for get users details',
		'',
		'No Key Defined',
		PARAM_TEXT
	) );
	$settings->add( new admin_setting_configtext(
		'local_nse/secretkey',
		'Secret Key for get users records',
		'',
		'No Key Defined',
		PARAM_TEXT
	) );
	$settings->add( new admin_setting_configtext(
		'local_nse/hosturl',
		'Host URL for get users',
		'',
		'No Key Defined',
		PARAM_TEXT
	) );
	$ADMIN->add('courses', new admin_externalpage('nsecourseadd',
        get_string('nsecourseregister', 'local_nse'), "$CFG->wwwroot/local/nse/view.php"));
}