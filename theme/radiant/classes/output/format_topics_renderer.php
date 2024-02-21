<?php


require_once($CFG->dirroot . '/course/format/topics/renderer.php');

class theme_radiant_format_topics_renderer extends format_topics_renderer
{
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

}