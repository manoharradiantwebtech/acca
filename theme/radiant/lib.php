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

/**
 * radiant theme callbacks.
 *
 * @package   theme_radiant
 * @copyright 2023 radiant
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_radiant_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    $scss .= file_get_contents($CFG->dirroot . '/theme/radiant/scss/classic/pre.scss');
    if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_radiant', 'preset', 0, '/', $filename))) {
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/radiant/scss/preset/default.scss');
    }
    $scss .= file_get_contents($CFG->dirroot . '/theme/radiant/scss/classic/post.scss');

    return $scss;
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_radiant_get_pre_scss($theme) {
    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
        'brandcolor' => ['primary'],
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            continue;
        }
        array_map(function($target) use (&$scss, $value) {
            $scss .= '$' . $target . ': ' . $value . ";\n";
        }, (array) $targets);
    }

    // Prepend pre-scss.
    if (!empty($theme->settings->scsspre)) {
        $scss .= $theme->settings->scsspre;
    }

    return $scss;
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_radiant_get_extra_scss($theme) {
    global $CFG;
    $content = '';

    // Set the page background image.
    $imageurl = $theme->setting_file_url('backgroundimage', 'backgroundimage');
    if (!empty($imageurl)) {
        $content .= '$imageurl: "' . $imageurl . '";';
        $content .= file_get_contents($CFG->dirroot .
            '/theme/radiant/scss/classic/body-background.scss');
    }

    // Sets the login background image.
    $loginbackgroundimageurl = $theme->setting_file_url('loginbackgroundimage', 'loginbackgroundimage');
    if (!empty($loginbackgroundimageurl)) {
        $content .= 'body.pagelayout-login #page { ';
        $content .= "background-image: url('$loginbackgroundimageurl'); background-size: cover;";
        $content .= ' }';
    }

    if (!empty($theme->settings->navbardark)) {
        $content .= file_get_contents($CFG->dirroot .
            '/theme/radiant/scss/classic/navbar-dark.scss');
    } else {
        $content .= file_get_contents($CFG->dirroot .
            '/theme/radiant/scss/classic/navbar-light.scss');
    }
    if (!empty($theme->settings->scss)) {
        $content .= $theme->settings->scss;
    }
    return $content;
}

/**
 * Get compiled css.
 *
 * @return string compiled css
 */
function theme_radiant_get_precompiled_css() {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/radiant/style/moodle.css');
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_radiant_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'backgroundimage' || $filearea === 'loginbackgroundimage')) {
        $theme = theme_config::load('radiant');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}


/**
 * Returns the course module information for the given section.
 *
 * @param int $sectionid The ID of the section.
 * @return array The course module information for the section.
 */
function get_section_activities($courseId, $sectionId) {
    global $DB;
    $activities = array();

	$sql = "SELECT cm.id, cm.module, m.name as modname, cm.instance, cm.section
					FROM  {course_sections} cs
				LEFT JOIN {course_modules} cm ON FIND_IN_SET(cm.id,cs.sequence)
				JOIN {modules} m ON cm.module = m.id
				WHERE cs.course = $courseId AND cs.id = $sectionId AND cm.deletioninprogress = 0 AND cm.visible = 1
				GROUP BY cm.id ORDER BY FIND_IN_SET(cm.id,cs.sequence)";
    $activity_records = $DB->get_records_sql($sql);
    foreach ($activity_records as $activity_record) {
        $module = $DB->get_record($activity_record->modname, array('id' => $activity_record->instance));
        $activity = new stdClass();
        $activity->id = $activity_record->id;
        $activity->name = $module->name;
        $activity->modname = $activity_record->modname;
        $activities[] = $activity;
    }

    return $activities;
}

function get_module_type($modname) {
    global $DB;
    $module = $DB->get_record('modules', array('name' => $modname));
    return $module ? $module->name : 'Unknown';
}

function get_content_hash_by_image_name($imagehash = ''){
    global $DB;
    $sql = "SELECT contenthash,
                   filename,
                   mimetype
            FROM {files}
            WHERE filename LIKE '%".$imagehash."%'";

    $file = $DB->get_record_sql($sql);
    if( $file instanceof stdClass ){
        list( $hash, $extension ) = explode( '.', $file->contenthash );
        return $hash;
    }else{
        return false;
    }
}

function move_badge_img_file($img_name){

    global $DB,$CFG;

    if (!file_exists($CFG->dirroot.'/badges/public_badge_imgs')) {
        mkdir($CFG->dirroot.'/badges/public_badge_imgs', 0777, true);
    }

    $sql = "SELECT contenthash,
                   contextid,
                   component,
                   filearea,
                   itemid,
                   filepath,
                   filename,
                   mimetype
            FROM {files}
            WHERE filename LIKE '%".$img_name."%'";

    $fileInfo = $DB->get_record_sql($sql);
    $firsPath = substr($fileInfo->contenthash,0,2);
    $secondPath = substr($fileInfo->contenthash,2,2);

    $src = $CFG->dataroot.'/filedir'.'/'.$firsPath.'/'.$secondPath.'/'.$fileInfo->contenthash;
    $dest = $CFG->dirroot.'/badges/public_badge_imgs'.'/'.$fileInfo->contenthash.'.png';

    copy($src,$dest);

    return $fileInfo->contenthash;

}

/**
 * Improve flat navigation menu
 *
 * @param flat_navigation $flatnav
 */
function theme_radiant_rebuildcoursesections(\flat_navigation $flatnav) {
    global $PAGE;

    $participantsitem = $flatnav->find(  'participants', \navigation_node::TYPE_CONTAINER);

    if (!$participantsitem) {
        return;
    }

    if ($PAGE->course->format != 'singleactivity') {
        $coursesectionsoptions = [
            'text' => get_string('coursesections', 'theme_moove'),
            'shorttext' => get_string('coursesections', 'theme_moove'),
            'icon' => new pix_icon('t/viewdetails', ''),
            'type' => \navigation_node::COURSE_CURRENT,
            'key' => 'course-sections',
            'parent' => $participantsitem->parent
        ];

        $coursesections = new \flat_navigation_node($coursesectionsoptions, 0);

        foreach ($flatnav as $item) {
            if ($item->type == \navigation_node::TYPE_SECTION) {
                $coursesections->add_node(new \navigation_node([
                    'text' => $item->text,
                    'shorttext' => $item->shorttext,
                    'icon' => $item->icon,
                    'type' => $item->type,
                    'key' => $item->key,
                    'parent' => $coursesections,
                    'action' => $item->action
                ]));
            }
        }

        $flatnav->add($coursesections, $participantsitem->key);
    }

    $mycourses = $flatnav->find('mycourses', \navigation_node::NODETYPE_LEAF);

    if ($mycourses) {
        $flatnav->remove($mycourses->key);

        $flatnav->add($mycourses, 'privatefiles');
    }
}

/**
 * Remove items from navigation
 *
 * @param flat_navigation $flatnav
 */
function theme_radiant_delete_menuitems(\flat_navigation $flatnav)
{
    $itemstodelete = [
        'coursehome', 'home'
    ];
    foreach ($flatnav as $item) {
        if (in_array($item->key, $itemstodelete)) {
            $flatnav->remove($item->key);
            continue;
        }

        if (isset($item->parent->key) && $item->parent->key == 'mycourses' &&
            isset($item->type) && $item->type == \navigation_node::TYPE_COURSE) {
            $flatnav->remove($item->key);
            continue;
        }
    }
}
