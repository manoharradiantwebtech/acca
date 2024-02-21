<?php

namespace local_query\task;
require_once($CFG->dirroot . '/local/query/locallib.php');
defined('MOODLE_INTERNAL') || die();

class escalate_task extends \core\task\scheduled_task {
    public function get_name() {
         return get_string('escalation', 'local_query');
    }

    public function execute() {
        if ($plugin = new \local_escalatealert()) {
            $plugin->alert_escalate();
        }
    }
}
