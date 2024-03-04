<?php
class coursereport_table extends table_sql {

    function __construct($uniqueid) {
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('num', 'name','videos', 'mcq', 'rightmcq', 'total');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array('S No.', 'Unit Module', 'Videos', 'MCQS', '✔️', 'Total');
        $this->define_headers($headers);
    }

    function col_num($values) {
        
        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return $values->num;
        } else {
            return $values->num;
        }
    }
    
    function col_name($values) {
        if (!isset($values->name)) {
            $values->name = 'Topic' . ' '. $values->num;
        }
        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return $values->name;
        } else {
            return $values->name;
        }
    }
    
    function col_videos($values) {
        global $DB;
        $moduleids = array_filter(explode(',', $values->sequence));
        $modulecontext = [];
        foreach($moduleids as $moduleid) {
            $modulecontext[] = context_module::instance($moduleid)->id;
        }
        $count = 0;
        if ($modulecontext) {
            $sql = "SELECT COUNT(*) as count FROM {files} AS f WHERE f.contextid in (".implode(',', $modulecontext).") AND f.mimetype LIKE 'video%'";
            $count = $DB->get_record_sql($sql)->count;
        }
        return ($count/ count($moduleids)) * 100 . '%';
    }
    
    function col_mcq($values) {

        global $DB, $CFG;
        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return'';
        } else {
            return '';
        }
    }

    function col_rightmcq($values) {
        
        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return $values->anacoins;
        } else {
            return $values->anacoins;
        }
    }

    function col_total($values) {
        global $CFG, $USER;
        $moduleids = array_filter(explode(',', $values->sequence));

        require_once($CFG->libdir . '/completionlib.php');
        $completion = new \completion_info(get_course($values->course));
        if (!$completion->is_enabled()) {
            return '0.00%';
        } else {
            $modules = $completion->get_activities();

            $completed = 0;
            foreach ($modules as $module) {
                if (in_array($module->id, $moduleids)) {
                    $data = $completion->get_data($module, true, $USER->id);
                    if (($data->completionstate == COMPLETION_INCOMPLETE) || ($data->completionstate == COMPLETION_COMPLETE_FAIL)) {
                        $completed += 0;
                    } else {
                        $completed += 1;
                    };
                }
            }
            $percentage = number_format(($completed / count($moduleids)) * 100, 2). '%';
            return $percentage;
        }
        
    }
}
