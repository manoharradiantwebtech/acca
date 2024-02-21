<?php

defined('MOODLE_INTERNAL') || die;

class local_nse_tablense extends table_sql {

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
			$this->define_columns(['courseid','bhav','bhav_type','nse_response','createddate','updateddate','isactive']);
			$this->define_headers(
				[
					get_string('courseid', 'local_nse'),
					get_string('bhav', 'local_nse'),
					get_string('bhav_type', 'local_nse'),
					get_string('nse_response', 'local_nse'),
					get_string('createddate', 'local_nse'),
					get_string('updateddate', 'local_nse'),
					get_string('isactive', 'local_nse'),
				]);
		}else{
			$this->define_columns(['courseid','bhav','bhav_type','createddate','updateddate','isactive','actionid']);
			$this->define_headers(
				[
					get_string('courseid', 'local_nse'),
					get_string('bhav', 'local_nse'),
					get_string('bhav_type', 'local_nse'),
					get_string('createddate', 'local_nse'),
					get_string('updateddate', 'local_nse'),
					get_string('isactive', 'local_nse'),
					get_string('actionid', 'local_nse'),
				]);
		}
        $this->collapsible(false);
        $this->sortable(false);
        $this->pageable(true);
    }
	
	public function table_setup() {
        global $DB;
        $params = [];
		$params['isactive'] = $this->filterparams->action;
		$table = '{nse_course} as nse LEFT JOIN {course} as course ON(nse.courseid=course.id)';
		$where = '1=1';
		if($this->filterparams->courseid){
			$where = 'course.id=:courseid';
			$params['courseid'] = $this->filterparams->courseid;
		}
	   if (!$this->filterparams->is_download) {
            $total = $DB->count_records_sql('SELECT COUNT(1) FROM '.$table.' WHERE '.$where, $params);
			$this->totalrows = $total;
            $this->pagesize($this->pagesize, $total);
        } else {
            $this->pageable(false);
        }
		$select = [];
		foreach($this->columns as $col => $value){
			if($col == 'courseid'){
				$select[]= "course.fullname as ".$col;
			}elseif($col == 'actionid'){
				$select[]= "nse.id as ".$col;
			}else{
				$select[]= "nse.".$col;
			}
		}
		$this->sql = new stdClass();
        $this->sql->from = $table;
		$this->sql->where = "$where GROUP BY id ORDER BY nse.updateddate DESC";
		$this->sql->fields = 'nse.id,'.implode(',',$select);
		$this->sql->params = $params;
		$this->countsql = 'SELECT COUNT(1) FROM '.$table.' WHERE '.$where;
		$this->countparams = $params;
		
    }
	
	public function col_createddate($event) {
		 if($event->createddate > 1){
			 return userdate($event->createddate, get_string('datetimeformat', 'local_nse'));
		 }
		 return '';
        
    }
	public function col_updateddate($event) {
		 if($event->updateddate > 1){
			 return userdate($event->updateddate, get_string('datetimeformat', 'local_nse'));
		 }
        return '';
    }
	public function col_isactive($event) {
		 if($event->isactive){
			 return "Yes";
		 }
        return 'No';
    }
	public function col_actionid($event){
		return html_writer::link(new moodle_url('/local/nse/edit.php',['id'=>$event->actionid]), "View");
	}
}
?>