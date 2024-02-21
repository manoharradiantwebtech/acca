<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_query\task\escalate_task',
        'blocking' => 0,
        'minute' => 0,
        'hour' => 0,
        'day' => '*/1',
        'month' => '*',
        'dayofweek' => '*'
    ]
];