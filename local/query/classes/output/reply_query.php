<?php

namespace local_query\output;                                                                                                         

use renderable;                                                                                                                     
use renderer_base;                                                                                                                  
use templatable;                                                                                                                    
use stdClass;  
defined('MOODLE_INTERNAL') || die();
class reply_query implements renderable, templatable {                                                                               
    var $req_id = null;                                                                                                           
	var $contex = null;
    public function __construct($req_id,$ticket,$url) {                                                                                        
        $this->req_id = $req_id; 
		$this->ticket = $ticket; 
		$this->url = $url;		
    }                                                                                                                   
    public function export_for_template(renderer_base $output) { 
		$req_id = $this->req_id;
		$data = $this->PrepareForTeamplate($req_id);  
		return $data;                                                                                                    
    }
	
	public function PrepareForTeamplate($req_id){
		global $DB, $CFG,$USER;
		$return =[];
		$requester =  $this->ticket;
		$requester->url= $this->url;
		$requester->sesskey= $USER->sesskey;
		$return['request'] = $requester;
		if($requester){
			$sql_text = 'SELECT text.*,u.firstname,u.lastname FROM {query_text} as text LEFT JOIN {user} as u ON(text.userid = u.id) WHERE  text.random_id = :req ORDER BY text.timecreated ASC';
			$result =  $DB->get_records_sql($sql_text,['req'=> $req_id]);
			foreach($result as $row){
				if($requester->userid == $row->userid){
					$row->is_requester = 1;
					$row->description = nl2br($row->description);
					$return['history'][] = $row;
				}else{
					$row->is_requester = 0;
					$row->description = nl2br($row->description);
					$return['history'][] = $row;
				}
			}
		}
		return $return;
	}
	
}