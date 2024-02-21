<?php
defined('MOODLE_INTERNAL') || die;
class local_nse_renderer extends plugin_renderer_base {
	
	public function render_local_nse_renderable(local_nse_renderable $nselog) {
        debugging('Do not call this method. Please call $renderer->render($nselog) instead.', DEBUG_DEVELOPER);
        return $this->render($nselog);
    }
	
	protected function render_local_nse(local_nse_renderable $nselog) {
		if ($nselog->showselectorform) {
            $nselog->local_selector_form($nselog);
        }
        if ($nselog->shownserecord) {
            $nselog->tablense->out($nselog->perpage,'{nse_course}', true);
        }
    }
}

