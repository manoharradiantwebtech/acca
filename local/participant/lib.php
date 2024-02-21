<?php
defined('MOODLE_INTERNAL') || die();

function local_participant_extend_navigation(global_navigation $navigation) {
    global $CFG, $DB, $USER;

    if ($CFG->branch >= 25) {
        $context = context_system::instance();
    } else {
        $context = get_system_context();
    }

    $current_theme = $DB->get_record('config', array('name' => 'theme'), 'value');
    $role = $DB->get_record('role_assignments', array('userid' => $USER->id));
    $tenantuser = $DB->get_record('user', array('theme' => 'radiant', 'id' => $USER->id));

    if ($current_theme->value == 'radiant') {
        $capabilityToCheck = 'local/participant:studentview';
        if (!is_siteadmin() && $role && check_user_capability_in_courses($USER, $capabilityToCheck)) {
            $title = 'Dashboard';
            $path = new moodle_url("/my/");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'home',
                new pix_icon('z/dashboard', ''));
            $navigation->add_node($settingsnode);

            $title = 'Event';
            $path = new moodle_url("/calendar/view.php?view=month");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'event',
                new pix_icon('z/event', ''));
            $navigation->add_node($settingsnode);

            $title = 'Certificates';
            $path = new moodle_url("/local/participant/user_certificates.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'certificate',
                new pix_icon('z/certificate', ''));
            $navigation->add_node($settingsnode);

            $title = 'Badges';
            $path = new moodle_url("/local/participant/badges.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'badge',
                new pix_icon('z/badge', ''));
            $navigation->add_node($settingsnode);

            $title = 'User Purchase History';
            $path = new moodle_url("/local/participant/user_purchase-history.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'purchase-history',
                new pix_icon('z/purchase-history', ''));
            $navigation->add_node($settingsnode);

            $title = 'Feedback';
            $path = new moodle_url("/local/participant/user_feedback.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'feedback',
                new pix_icon('z/feedback', ''));
            $navigation->add_node($settingsnode);

            $title = 'Support';
            $path = new moodle_url("/local/participant/support.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'support',
                new pix_icon('z/support', ''));
            $navigation->add_node($settingsnode);

            $title = 'Logout';
            $path = new moodle_url('/login/logout.php', array('sesskey' => sesskey()));
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'logout',
                new pix_icon('z/logout', ''));
            $navigation->add_node($settingsnode);
        } elseif (is_siteadmin()) {
            $title = 'Dashboard';
            $path = new moodle_url("/my/");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'dashboard',
                new pix_icon('z/dashboard', ''));
            $navigation->add_node($settingsnode);

            $title = 'Site Admin';
            $path = new moodle_url("/admin/search.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'settings',
                new pix_icon('z/settings', ''));
            $navigation->add_node($settingsnode);

            $title = 'Multi Tenant';
            $path = new moodle_url("/blocks/iomad_company_admin/index.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'multi-tenant',
                new pix_icon('z/multi-tenant', ''));
            $navigation->add_node($settingsnode);

            $title = 'Users';
            $path = new moodle_url("/local/participant/usersettings.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'user',
                new pix_icon('z/user', ''));
            $navigation->add_node($settingsnode);

            $title = 'Course Settings';
            $path = new moodle_url("/local/participant/course_settings.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'settings',
                new pix_icon('z/settings', ''));
            $navigation->add_node($settingsnode);

            $title = 'Badges';
            $path = new moodle_url("/badges/index.php?type=1");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'badge',
                new pix_icon('z/badge', ''));
            $navigation->add_node($settingsnode);

            $title = 'Appearance';
            $path = new moodle_url("/local/participant/appearancesetting.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'appearance',
                new pix_icon('z/appearance', ''));
            $navigation->add_node($settingsnode);

            $title = 'Query';
            $path = new moodle_url("/local/query/");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'appearance',
                new pix_icon('z/query', ''));
            $navigation->add_node($settingsnode);
        } elseif (has_capability('local/participant:iomadviewall', $context, $USER)) {
            $title = 'Dashboard';
            $path = new moodle_url("/my/");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'dashboard',
                new pix_icon('z/dashboard', ''));
            $navigation->add_node($settingsnode);

            $title = 'Admin Settings';
            $path = new moodle_url("/blocks/iomad_company_admin/index.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'multi-tenant',
                new pix_icon('z/multi-tenant', ''));
            $navigation->add_node($settingsnode);

            $title = 'Course View';
            $path = new moodle_url("/local/participant/course_view.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'courses',
                new pix_icon('z/courses', ''));
            $navigation->add_node($settingsnode);

            $title = 'Event';
            $path = new moodle_url("/calendar/view.php?view=month");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'event',
                new pix_icon('z/event', ''));
            $navigation->add_node($settingsnode);

            $title = 'Badges';
            $path = new moodle_url("/local/participant/badges.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'badge',
                new pix_icon('z/badge', ''));
            $navigation->add_node($settingsnode);

            $title = 'Certificates';
            $path = new moodle_url("/local/participant/user_certificates.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'certificate',
                new pix_icon('z/certificate', ''));
            $navigation->add_node($settingsnode);
        } elseif ($tenantuser && $role && check_user_capability_in_courses($USER, $capabilityToCheck)) {
            $title = 'Dashboard';
            $path = new moodle_url("/my/");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'home',
                new pix_icon('z/dashboard', ''));
            $navigation->add_node($settingsnode);

            $title = 'Event';
            $path = new moodle_url("/calendar/view.php?view=month");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'event',
                new pix_icon('z/event', ''));
            $navigation->add_node($settingsnode);

            $title = 'Badges';
            $path = new moodle_url("/local/participant/badges.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'badge',
                new pix_icon('z/badge', ''));
            $navigation->add_node($settingsnode);

            $title = 'Certificates';
            $path = new moodle_url("/local/participant/user_certificates.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'certificate',
                new pix_icon('z/certificate', ''));
            $navigation->add_node($settingsnode);

            $title = 'Logout';
            $path = new moodle_url('/login/logout.php', array('sesskey' => sesskey()));
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'logout',
                new pix_icon('z/logout', ''));
            $navigation->add_node($settingsnode);
        } else {
            $title = 'Dashboard';
            $path = new moodle_url("/my/");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'home',
                new pix_icon('z/dashboard', ''));
            $navigation->add_node($settingsnode);

            $title = 'Event';
            $path = new moodle_url("/calendar/view.php?view=month");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'event',
                new pix_icon('z/event', ''));
            $navigation->add_node($settingsnode);

            $title = 'Badges';
            $path = new moodle_url("/local/participant/badges.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'badge',
                new pix_icon('z/badge', ''));
            $navigation->add_node($settingsnode);

            $title = 'Certificates';
            $path = new moodle_url("/local/participant/user_certificates.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'certificate',
                new pix_icon('z/certificate', ''));
            $navigation->add_node($settingsnode);

            $title = 'User Purchase History';
            $path = new moodle_url("/local/participant/user_purchase-history.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'purchase-history',
                new pix_icon('z/purchase-history', ''));
            $navigation->add_node($settingsnode);

            $title = 'Feedback';
            $path = new moodle_url("/local/participant/user_feedback.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'feedback',
                new pix_icon('z/feedback', ''));
            $navigation->add_node($settingsnode);

            $title = 'Support';
            $path = new moodle_url("/local/participant/support.php");
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'support',
                new pix_icon('z/support', ''));
            $navigation->add_node($settingsnode);

            $title = 'Logout';
            $path = new moodle_url('/login/logout.php', array('sesskey' => sesskey()));
            $settingsnode = navigation_node::create($title,
                $path,
                navigation_node::TYPE_SETTING,
                null,
                'logout',
                new pix_icon('z/logout', ''));
            $navigation->add_node($settingsnode);
        }
    }
}

/**
 * Get course image URL.
 *
 * @param int $courseid
 * @return mixed URL or false if no image
 */
function get_course_image_url($courseid) {
    global $OUTPUT;
    $fs = get_file_storage();
    $context = \context_course::instance($courseid);
    $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0);
    foreach ($files as $file) {
        /*
        if ($file->is_valid_image()) {
            return \moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                null, $file->get_filepath(), $file->get_filename());
        }
        */
    }
    // No image defined, so...
    return $OUTPUT->image_url('courseimage', 'block_iomad_learningpath')->out();
}

/**
 * The course progress builder.
 *
 * @param object $course The course whose progress we want
 * @return string
 */
function block_course_records_build_progress_course_format($course) {
    global $CFG, $USER;
    $button = '';
    require_once($CFG->dirroot.'/grade/querylib.php');
    require_once($CFG->dirroot.'/grade/lib.php');
    $config = get_config('block_course_records');
    if (!defined('BLOCKS_LW_COURSES_SHOWGRADES_NO')) {
        define('BLOCKS_LW_COURSES_SHOWGRADES_NO', '0');
    }
    if ($config->progressenabled == BLOCKS_LW_COURSES_SHOWGRADES_NO) {
        return '';
    }
    $course = get_course($course->id);
    $completionstatus = new stdClass();
    // Get course completion data.
    $coursecompletiondata = new completion_info($course);
    // Don't display if completion isn't enabled!
    if (!$coursecompletiondata->is_enabled()) {
        // Check if user should get a limited/full description of issue - based on viewhiddencourses capabilities.
        $context = context_course::instance($course->id, IGNORE_MISSING);
        // Limited view.
        if (!has_capability('moodle/course:viewhiddencourses', $context)) {
            return html_writer::tag('p', get_string('progressunavail', 'block_course_records'), array('class' => 'progressunavail'));
        } else { // Full view.
            return;
        }
    }
    // INSPIRED BY completionstatus BLOCK.
    // Load criteria to display.
    $completions = $coursecompletiondata->get_completions($USER->id);
    // For aggregating activity completion.
    $activities = array();
    $activitiescompleted = 0;
    // Flag to set if current completion data is inconsistent with what is stored in the database.
    $pendingupdate = false;
    // Loop through course criteria.
    foreach ($completions as $completion) {
        $criteria = $completion->get_criteria();
        $iscomplete = $completion->is_complete();
    
        if (empty($iscomplete)) {
            global $DB;
            $act_compl_sql = "SELECT cmc.* 
                              FROM {course_modules_completion} cmc 
                              JOIN {course_completion_criteria} ccc 
                              ON cmc.coursemoduleid = ccc.moduleinstance 
                              WHERE cmc.userid = ? AND cmc.coursemoduleid = ?";
            $act_compl = $DB->get_record_sql($act_compl_sql, array($USER->id, $criteria->moduleinstance));
            if (!empty($act_compl)) {
                $iscomplete = 1;
            }
        }
        if (!$pendingupdate && $criteria->is_pending($completion)) {
            $pendingupdate = true;
        }
        // Activities are a special case, so cache them and leave them till last.
        if ($criteria->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {
            $activities[$criteria->moduleinstance] = $iscomplete;
            if ($iscomplete) {
                $activitiescompleted++;
            }
            continue;
        }
    }
    $completionpercentage = 0;
    // Aggregate activities.
    if (!empty($activities)) {
        $completionstatus->min = $activitiescompleted;
        $completionstatus->max = count($activities);
        $completionpercentage = intval($completionstatus->min / $completionstatus->max * 100);
    }
    $completionpercentage = round(\core_completion\progress::get_course_progress_percentage($course));
    $activitiesstatus = new stdClass();
    $activitiesstatus->activitiescompleted = $activitiescompleted;
    $activitiesstatus->activities = count($activities);
    $activities = html_writer::start_tag('div', array('class' => 'activitiesstatus'));
    $activities .= html_writer::tag('p', get_string('activitiesstatus', 'block_course_records', $activitiesstatus));
    $activities .= html_writer::end_tag('div');
    $zero_percent = $completionpercentage == 0 ? 'zeropercent' : '';
    $startcourse = $completionpercentage == 0 ? 'start' : 'continue';
    $button .= html_writer::end_tag('a');
    
    // Don't display if completion isn't enabled!
    if (!$coursecompletiondata->is_enabled()) {
        $progress = '';
        $activities = '';
    }
    /**
     * Circle progress bar percentage.
     *
     * Radiant Web Tech
     */
    $dashoffset = 100 - fmod(100, ($completionpercentage + 5));
    return ['progress' => $progress, 'activities' => '', 'button' => $button,'completion_percentage' => $completionpercentage, 'dashoffset' => $dashoffset];
}

function check_user_capability_in_courses($user, $capability) {
    // Get the user's enrolled courses
    $enrolledCourses = enrol_get_users_courses($user->id, true);

    // Loop through each enrolled course
    foreach ($enrolledCourses as $course) {
        // Get the course context
        $context = context_course::instance($course->id);

        // Check if the user has the specified capability in the current course
        if (has_capability($capability, $context, $user)) {
            return true; // User has the capability in at least one course
        }
    }

    return false; // User does not have the capability in any enrolled courses
}