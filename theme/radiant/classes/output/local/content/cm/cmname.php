<?php

use core_courseformat\base as course_format;
use core_courseformat\output\local\content\cm\cmname as core_cmname;
use core_courseformat\output\local\courseformat_named_templatable;
use section_info;
use cm_info;

/**
 * Overrides the default activity name inplace editable.
 *
 * @package   mytheme
 */
class theme_radiant_cmname extends core_cmname {
    use courseformat_named_templatable;

    /** @var course_format the course format */
    protected $format;

    /** @var section_info the section object */
    private $section;

    /** @var cm_info the course module instance */
    protected $mod;

    /** @var array optional display options */
    protected $displayoptions;

    /** @var string the activity title output class name */
    protected $titleclass;

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     * @param cm_info $mod the course module ionfo
     * @param bool|null $editable if it is editable (not used)
     * @param array $displayoptions optional extra display options
     */
    public function __construct(
        course_format $format,
        section_info $section,
        cm_info $mod,
        ?bool $editable = null,
        array $displayoptions = []
    ) {
        $this->format = $format;
        $this->section = $section;
        $this->mod = $mod;
        $this->displayoptions = $displayoptions;

        // Get the necessary classes.
        $this->titleclass = $format->get_output_classname('content\\cm\\title');
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): array {
        $mod = $this->mod;
        $displayoptions = $this->displayoptions;

        if (!$this->has_name()) {
            // Nothing to be displayed to the user.
            return [];
        }
        $data = [
            'url' => $mod->url,
            'modname' => $mod->modname,
            'pluginname' => get_string('pluginname', 'mod_' . $mod->modname),
            'textclasses' => $displayoptions['textclasses'] ?? '',
            'purpose' => plugin_supports('mod', $mod->modname, FEATURE_MOD_PURPOSE, MOD_PURPOSE_OTHER),
            'activityname' => $this->get_title_data($output),
        ];

        return $data;
    }

    /**
     * Get the title data.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    protected function get_title_data(\renderer_base $output): array {
        $title = new $this->titleclass(
            $this->format,
            $this->section,
            $this->mod,
            $this->displayoptions
        );
        return (array) $title->export_for_template($output);
    }

    /**
     * Return if the activity has a visible name.
     *
     * @return bool if the title is visible.
     */
    public function has_name(): bool {
        return $this->mod->is_visible_on_course_page() && $this->mod->url;
    }
}
