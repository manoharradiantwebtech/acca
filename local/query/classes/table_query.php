<?php

defined('MOODLE_INTERNAL') || die;

class local_query_table_query extends table_sql {

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
			$this->define_columns(['name','email','phone','createdon','updatedon','description']);
			$this->define_headers(
				[
					get_string('name', 'local_query'),
					get_string('email', 'local_query'),
					get_string('phone', 'local_query'),
					get_string('createdon', 'local_query'),
					get_string('updatedon', 'local_query'),
					get_string('description', 'local_query'),
				]);
		} else {
		 /**
                  * Start Changes
                  * Created a new header(column) to query report table.
                  * @package Local_query
                  * @author Radiant Web Tech
                  */
			$this->define_columns(['name','email','phone','page_url','page_name','createdon','updatedon','random_id']);
			$this->define_headers(
				[
					get_string('name', 'local_query'),
					get_string('email', 'local_query'),
					get_string('phone', 'local_query'),
					get_string('page_url', 'local_query'),
					get_string('page_name', 'local_query'),
					get_string('createdon', 'local_query'),
					get_string('updatedon', 'local_query'),
					get_string('action', 'local_query'),
				]);
		}
		//End Changes
        $this->collapsible(false);
        $this->sortable(false);
        $this->pageable(true);
    }
	
	public function table_setup() {
        global $DB, $DB, $USER;
        $joins = array();
        $params = array();

        $result = get_user_role_id($USER->id);
        if (!is_siteadmin() && $result->roleid == '3' || $result->roleid == '4') {
           if($this->filterparams->action != 2){
               $joins[] = "q.is_rply = :is_rply";
               $params['is_rply'] = $this->filterparams->action;
           }
           if(!empty($this->filterparams->userid)) {
               $joins[] = "q.userid = :userid";
               $params['userid'] = $this->filterparams->userid;
           }
           if (!empty($this->filterparams->createdon)) {
               $joins[] = "q.createdon > :date AND q.createdon < :enddate";
               $params['date'] = $this->filterparams->createdon;
               $params['enddate'] = $this->filterparams->createdon + DAYSECS;
           }

            $joins[] = "r.shortname ='editingteacher'";
            $joins[] = "u.id = :userid";
            $params['userid'] = $USER->id;

            if(count($joins)){
                $selector = implode(' AND ', $joins);
            }else{
                $selector = '1=1';
            }


            $table = '{query} AS q
                      INNER JOIN {course} c ON q.page_name = c.shortname
                      INNER JOIN {context} AS ctx ON c.id = ctx.instanceid
                      INNER JOIN {role_assignments} AS ra ON ctx.id = ra.contextid
                      INNER JOIN {user} AS u ON ra.userid = u.id
                      INNER JOIN {role} AS r ON ra.roleid = r.id
                     ';


            if ($this->is_download != 1) {
               $total = $DB->count_records_sql('SELECT COUNT(1) FROM '.$table.' WHERE '.$selector, $params);

               $this->totalrows = $total;
               $this->pagesize($this->pagesize, $total);
           } else {
               $this->pageable(false);
           }
           $select = [];
           foreach($this->columns as $col => $value){
               if($col == 'description'){
                   $select[]= "GROUP_CONCAT(text.$col SEPARATOR '#$#') as description";
               }else{
                   $select[]= 'q.'.$col;
               }
           }
           $this->sql = new stdClass();
           $this->sql->from = $table;
           $this->sql->where = $selector .' GROUP BY q.id ORDER BY q.updatedon DESC';
           $this->sql->fields = 'q.id,'.implode(',',$select);
           $this->sql->params = $params;
           $this->countsql = 'SELECT COUNT(1) FROM '.$table.' WHERE '.$selector;
           $this->countparams = $params;
       } else {
           if($this->filterparams->action != 2){
               $joins[] = "q.is_rply = :is_rply";
               $params['is_rply'] = $this->filterparams->action;
           }
           if(!empty($this->filterparams->userid)) {
               $joins[] = "q.userid = :userid";
               $params['userid'] = $this->filterparams->userid;
           }
           if (!empty($this->filterparams->createdon)) {
               $joins[] = "q.createdon > :date AND q.createdon < :enddate";
               $params['date'] = $this->filterparams->createdon;
               $params['enddate'] = $this->filterparams->createdon + DAYSECS;
           }
           if(count($joins)){
               $selector = implode(' AND ', $joins);
           }else{
               $selector = '1=1';
           }
           $table = '{query} as q LEFT JOIN {query_text} as text ON(q.random_id=text.random_id)';

           if ($this->is_download != 1) {
               $total = $DB->count_records_sql('SELECT COUNT(1) FROM '.$table.' WHERE '.$selector, $params);

               $this->totalrows = $total;
               $this->pagesize($this->pagesize, $total);
           } else {
               $this->pageable(false);
           }
           $select = [];
           foreach($this->columns as $col => $value){
               if($col == 'description'){
                   $select[]= "GROUP_CONCAT(text.$col SEPARATOR '#$#') as description";
               }else{
                   $select[]= 'q.'.$col;
               }
           }
           $this->sql = new stdClass();
           $this->sql->from = $table;
           $this->sql->where = $selector .' GROUP BY q.id ORDER BY q.updatedon DESC';
           $this->sql->fields = 'q.id,'.implode(',',$select);
           $this->sql->params = $params;
           $this->countsql = 'SELECT COUNT(1) FROM '.$table.' WHERE '.$selector;
           $this->countparams = $params;
       }

    }

	public function col_page_url($event) {
       return html_writer::link(new moodle_url($event->page_url, []), get_string('show','local_query'),array('target' => '_blank'));
	}
	public function col_createdon($event) {
         $dateformat = get_string('datetimeformat', 'local_query');
		 if($event->createdon > 1){
			 return userdate($event->createdon, $dateformat);
		 }
		 return '';
    }
	public function col_updatedon($event) {
         $dateformat = get_string('datetimeformat', 'local_query');
		 if($event->updatedon > 1){
			 return userdate($event->updatedon, $dateformat);
		 }
        return '';
    }
	public function col_random_id($event){
		$url = new moodle_url('/local/query/view.php',['reqid'=>$event->random_id]);
		return  html_writer::tag('input','',['type'=>'button','value'=>'Reply','onclick'=>'location.href="'.$url.'"','class'=>'rpl_btn']);
	}
	public function col_description($event) {
        $description = explode("#$#",$event->description);
		return implode("\r\n\r\n ####################################\r\n\r\n",$description);
    }
}
?>