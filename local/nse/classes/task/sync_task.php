<?php
namespace local_nse\task;
require_once($CFG->dirroot . '/local/nse/lib.php');
defined('MOODLE_INTERNAL') || die();

class sync_task extends \core\task\scheduled_task{
	
    public function get_name() {
         return get_string('attributesynctask', 'local_nse');
    }

    public function execute() {
		
        if ($plugin = new \local_nse()) {
            $plugin->sync_user();
        }
    }
}
