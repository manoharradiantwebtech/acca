<?php

namespace local_participant\task;

defined('MOODLE_INTERNAL') || die();


use core\task\scheduled_task;

class userprogress extends scheduled_task {

    public function get_name() {
        return get_string('userprogresstask', 'local_participant');
    }

    public function execute() {
        global $DB;
        // Ensure the user is logged in
        require_login();

        // Set the batch size to control the number of users processed in each iteration
        $batch_size = 1000; // Adjust this based on your server's capacity

        // Get the total number of users
        $total_users = $DB->count_records('user');

        // Initialize the offset
        $offset = 0;

        while ($offset < $total_users) {
            // Fetch a batch of users
            $users = $DB->get_records_sql("
              SELECT id, username
              FROM {user}
              LIMIT $batch_size
              OFFSET $offset
          ");

            // Process each user in the batch
            foreach ($users as $user) {
                $enrolled_courses = enrol_get_users_courses($user->id);

                foreach ($enrolled_courses as $course) {
                    $progress = \core_completion\progress::get_course_progress_percentage($course);
                    $progress = floor($progress);

                    if (!isset($progress)) {
                        $progress = 0;
                    }
                    $userId = $user->id;
                    $courseId = $course->id;
                    // Check if the record already exists in the custom table.
                    $existingRecord = $DB->get_record('local_user_progress', array('userid' => $userId, 'courseid' => $courseId));
                    if ($existingRecord) {
                        // Update the existing record.
                        $existingRecord->progress = $progress;
                        $existingRecord->timemodified = time();
                        $DB->update_record('local_user_progress', $existingRecord);
                    } else {
                        // Create a new record for the table.
                        $record = new \stdClass();
                        $record->userid = $userId;
                        $record->courseid = $courseId;
                        $record->progress = $progress;
                        $record->timemodified = time();

                        // Insert the new record into your custom table.
                        $DB->insert_record('local_user_progress', $record);
                    }
                }
            }
            // Increment the offset for the next batch
            $offset += $batch_size;
        }
    }
}
