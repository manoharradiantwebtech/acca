<?php


namespace local_query\privacy;

defined('MOODLE_INTERNAL') || die();


class provider implements \core_privacy\local\metadata\null_provider {

   use \core_privacy\local\legacy_polyfill;
   
    public static function get_reason() : string {
        return 'privacy:metadata';
    }
}