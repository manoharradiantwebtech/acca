<?php

defined('MOODLE_INTERNAL') || die;

class local_feedback_table_query extends table_sql {

    private $filterparams;
    public $countsql = NULL;
    public $countparams = NULL;
    public $sql = NULL;
    public $rawdata = NULL;
    public $is_sortable    = true;
    public $is_collapsible = true;

    public function __construct($uniqueid, $filterparams = null) {
        parent::__construct($uniqueid);

        $this->filterparams = $filterparams;
        if($filterparams->is_download){
            $this->define_columns(['userid', 'feedbacktype','feedback', 'timemodified', 'rating']);
            $this->define_headers(
                [
                    get_string('username', 'local_feedback'),
                    get_string('feedbacktype', 'local_feedback'),
                    get_string('feedback', 'local_feedback'),
                    get_string('timemodified', 'local_feedback'),
                    get_string('rating', 'local_feedback'),
                ]);
        } else {
            /**
             * Start Changes
             * Created a new header(column) to query report table.
             * @package Local_query
             * @author Radiant Web Tech
             */
            $this->define_columns(['userid', 'feedbacktype','feedback', 'timemodified', 'rating']);
            $this->define_headers(
                [
                    get_string('username', 'local_feedback'),
                    get_string('feedbacktype', 'local_feedback'),
                    get_string('feedback', 'local_feedback'),
                    get_string('timemodified', 'local_feedback'),
                    get_string('rating', 'local_feedback'),
                ]);
        }
        //End Changes
        $this->collapsible(false);
        $this->sortable(false);
        $this->pageable(true);
    }

    public function table_setup() {
        global $DB;
        $joins = array();
        $params = array();

        $table = '{local_participant_feedback} as fe';
        if(count($joins)){
            $selector = implode(' AND ', $joins);
        }else{
            $selector = '1=1';
        }
        if ($this->is_download != 1) {
            $total = $DB->count_records_sql('SELECT COUNT(1) FROM '.$table.' WHERE '.$selector, $params);

            $this->totalrows = $total;
            $this->pagesize($this->pagesize, $total);
        } else {
            $this->pageable(false);
        }
        $select = [];
        foreach($this->columns as $col => $value){
            $select[]= 'fe.'.$col;
        }
        $this->sql = new stdClass();
        $this->sql->from = $table;
        $this->sql->where = $selector .' GROUP BY fe.id ORDER BY fe.timemodified DESC';
        $this->sql->fields = 'fe.id,'.implode(',',$select);
        $this->sql->params = $params;
        $this->countsql = 'SELECT COUNT(1) FROM '.$table.' WHERE '.$selector;
        $this->countparams = $params;
    }
    public function col_page_url($event) {
        return html_writer::link(new moodle_url($event->page_url, []), get_string('show','local_query'),array('target' => '_blank'));
    }
    public function col_timemodified($event) {
        $dateformat = get_string('datetimeformat', 'local_feedback');
        if($event->timemodified > 1){
            return userdate($event->timemodified, $dateformat);
        }
        return '';

    }
    public function col_userid($event) {
        global $DB;
        if($event->userid > 1){
            $username = $DB->get_record('user', array('id' => $event->userid));
            return $username->username;
        }
        return '';

    }
    public function col_feedback($event) {
        $description = explode("#$#",$event->feedback);
        return implode("\r\n\r\n ####################################\r\n\r\n",$description);
    }
}
?>