<?php

defined('MOODLE_INTERNAL') || die();
$tasks =[
    [
        'classname' => '\local_nse\task\sync_task',
        'blocking' => 0,
        'minute' => '*/5',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
		'disabled' => 0
   ],
];
