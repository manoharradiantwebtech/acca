<?php
defined('MOODLE_INTERNAL') || die;
class local_query_renderer extends plugin_renderer_base {
	
	public function render_local_query_renderable(local_query_renderable $querylog) {
        debugging('Do not call this method. Please call $renderer->render($querylog) instead.', DEBUG_DEVELOPER);
        return $this->render($querylog);
    }
	
	protected function render_local_query(local_query_renderable $querylog) {

        if ($querylog->showselectorform) {
            $this->local_selector_form($querylog);
        }
        if ($querylog->showquery){
            $querylog->tablequery->out($querylog->perpage,'{query}', true);
        }
    }
	
	
	public function local_selector_form(local_query_renderable $querylog) {
        echo html_writer::start_tag('form', array('class' => 'queryselecform', 'action' => $querylog->url, 'method' => 'get'));
        echo html_writer::start_div();
        echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'choosequery', 'value' => '1'));
        echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'showusers', 'value' => $querylog->showusers));
        $users = $querylog->get_user_list();
        if ($querylog->showusers) {
            echo html_writer::label(get_string('selctauser'), 'menuuser', false, array('class' => 'accesshide'));
            echo html_writer::select($users, "user", $querylog->userid, get_string("allparticipants"));
        } else {
            $users = array();
            if (!empty($querylog->userid)) {
                $users[$querylog->userid] = $querylog->get_selected_user_fullname();
            } else {
                $users[0] = get_string('allparticipants');
            }
            echo html_writer::label(get_string('selctauser'), 'menuuser', false, array('class' => 'accesshide'));
            echo html_writer::select($users, "user", $querylog->userid, false);
            $a = new stdClass();
            $a->url = new moodle_url('/local/query/index.php', array('chooselog' => 0, 'user' => $querylog->userid,
                'id' => $selectedcourseid, 'date' => $querylog->date, 'showusers' => 1));
            $a->url = $a->url->out(false);
            echo html_writer::start_span('mx-1');
            // print_string('querytoomanyusers', 'moodle', $a);
            echo html_writer::end_span();
        }

        $dates = $querylog->get_date_options();
        echo html_writer::label(get_string('date'), 'menudate', false, array('class' => 'accesshide'));
        echo html_writer::select($dates, "date", $querylog->date, get_string("alldays"));


        $actions = $querylog->get_action_options();
		echo html_writer::label(get_string('action'), 'action', false, array('class' => 'accesshide'));
        echo html_writer::select($actions, "action", $querylog->action, false);

       echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('gettheselquery','local_query'), 'class' => 'btn btn-secondary'));
        echo html_writer::end_div();
        echo html_writer::end_tag('form');
    }
}

