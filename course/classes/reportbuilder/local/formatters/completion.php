<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

declare(strict_types=1);

namespace core_course\reportbuilder\local\formatters;

use core_completion\progress;
use core_reportbuilder\local\helpers\format;
use stdClass;

/**
 * Formatters for the course completion entity
 *
 * @package     core_course
 * @copyright   2022 David Matamoros <davidmc@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion {

    /**
     * Return completion progress as a percentage
     *
     * @param string|null $value
     * @param stdClass $row
     * @return string
     */
    /**
       * Start Changes.
       * Write the logic to get the user
       * @author Radiant Web Tech
       */
    public static function completion_progress(?string $value, stdClass $row): string {
        global $CFG, $DB;
        require_once($CFG->libdir . '/completionlib.php');

        // Do not show progress if there is no userid.
        if (!$row->userid) {
            return '';
        }
        $courseid = (int) $row->courseid;
        $progress = $DB->get_record_sql("SELECT up.progress FROM {local_user_progress} up WHERE userid = :userid AND courseid = :courseid", [
            'userid' => $row->userid,
            'courseid' => $courseid,
        ]);

        return $progress->progress;
    //End
    }

    /**
     * Return number of days for methods daystakingcourse and daysuntilcompletion
     *
     * @param int|null $value
     * @param stdClass $row
     * @return int|null
     */
    public static function get_days(?int $value, stdClass $row): ?int {
        // Do not show anything if there is no userid.
        if (!$row->userid) {
            return null;
        }
        return $value;
    }
}
