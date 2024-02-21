<?php
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * Page external functions
 *
 * @package    mod_page
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_hvp_external extends external_api {
	
	public static function view_hvp_parameters() {
        return new external_function_parameters(
            array(
                'hvpid' => new external_value(PARAM_INT, 'hvp instance id')
            )
        );
    }
	
}