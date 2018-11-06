<?php
namespace Vanderbilt\DataCore\UserRoles;

class UserRoles extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
		
		// create log that is in redcapversioned/ExternalModules
		$this->log = fopen("log.txt", "w");
		fwrite($this->log, "starting log...\n");
		
		$this->dev = true;
	}
	
	public function __destruct() {
		fwrite($this->log, "Closing log");
		fclose($this->log);
	}
	
	function getChoicesFromMetaData($choicesString) {
		if ($choicesString == "") return "";
		// 1) split by \n or "|" depending on which is used
		if(strpos($choicesString,'\n') !== false)
			$choicesArray1 = explode('\n', $choicesString);
		else
			$choicesArray1 = explode('|', $choicesString);

		// 2) split by ","
		$rawToLabel = array();
		foreach ($choicesArray1 as $keyCommaValue) {
			$separteKeyFromValue = explode(",", $keyCommaValue);
			$key = trim($separteKeyFromValue[0]);
			$value = trim($separteKeyFromValue[1]);
			$rawToLabel[$key] = $value;
		}
		return $rawToLabel;
	}
	
	public function deleteAll() {
		$pid = $this->getSystemSetting("master-pid");
		$sql = "DELETE FROM redcap_data WHERE project_id='$pid'";
		db_query($sql);
		fwrite($this->log, "sql: $sql\n");
	}
	
	public function getData() {
		$pid = $this->getSystemSetting("master-pid");
		$configProject = new \Project($pid);
		
		// build json to return
		$data = array(
			"dashboards" => [],
			"reports" => [],
			"roles" => [],
			"dags" => [],
			"projects" => [],
			"customRoles" => [],
		);
		
		// add dashboards
		$choices = $this->getChoicesFromMetaData($configProject->metadata['tab_access']['element_enum']);
		foreach ($choices as $k => $v){
			$data["dashboards"][] = $v;
		}
		
		// add reports
		$choices = $this->getChoicesFromMetaData($configProject->metadata['tab_access_2']['element_enum']);
		foreach ($choices as $k => $v){
			$data["reports"][] = $v;
		}
		
		// get list of projects user has access to
		$sql = file_get_contents($this->getUrl("sql" . DIRECTORY_SEPARATOR . "getProjects.sql"));
		// DEV // $sql = str_replace("[USERID]", USERID, $sql);
		$sql = str_replace("[USERID]", "carl", $sql);
		$query = db_query($sql);
		$pidList = "";
		while ($row = db_fetch_array($query)) {
			$pidList = $pidList . $row['project_id'] . ", ";
			$data["projects"][$row['project_id']] = array(
				"name" => $row['app_title'],
				"roles" => [],
				"dags" => []
			);
		}
		$pidList = substr($pidList, -2) == ", " ? substr($pidList, 0, -2) : $pidList;
		// echo "pidList: $pidList\n\n";
		
		// add roles
		$sql = file_get_contents($this->getUrl("sql" . DIRECTORY_SEPARATOR . "getRoles.sql"));
		$sql = str_replace("[PID_LIST]", $pidList, $sql);
		$query = db_query($sql);
		while ($row = db_fetch_array($query)) {
			// put role in roles list
			$data["roles"][$row['role_id']] = $row['role_name'];
			
			// also put role in appropriate project object
			$data["projects"][$row['project_id']]["roles"][] = $row['role_id'];
		}
		
		// add dags
		$sql = file_get_contents($this->getUrl("sql" . DIRECTORY_SEPARATOR . "getDags.sql"));
		$sql = str_replace("[PID_LIST]", $pidList, $sql);
		$query = db_query($sql);
		while ($row = db_fetch_array($query)) {
			// put role in roles list
			$data["dags"][$row['group_id']] = $row['group_name'];
			
			// also put role in appropriate project object
			$data["projects"][$row['project_id']]["dags"][] = $row['group_id'];
		}
		
		// add custom role info (customRoles)
		$records = \Records::getData($pid);
		foreach ($records as $i => $a){
			$record = $a[key($a)];
			$newRole = array(
				"name" => $record["role_name"],
				"projects" => $record["project_access"],
				"dashboards" => [],
				"reports" => []
			);
			$newRole["active"] = $record["role_active"]==1 ? "true" : "false";
			$newRole["external"] = $record["role_external"]==1 ? "true" : "false";
			$i = 0;
			foreach ($record["tab_access"] as $val){
				$i++;
				if ($val==1) $newRole["dashboards"][] = (string)$i;
			}
			$i = 0;
			foreach ($record["tab_access_2"] as $val){
				$i++;
				if ($val==1) $newRole["reports"][] = (string)$i;
			}
			
			$data["customRoles"][$record["record_id"]] = $newRole;
		}
		
		return json_encode($data);
		
		// dev test/mock data:
		// return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "dev" . DIRECTORY_SEPARATOR . "allData.json");
	}
	
	public function saveData() {
		// build array to send to REDCap::saveData from $_POST
		$pid = $this->getSystemSetting("master-pid");
		$project = new \Project($pid);
		$eid = (int) $project->firstEventId;
		
		// count how many dashboard items and report items there are
		// $sampleDashboardsField = \REDCap::getData(['project_id' => (string) $pid, 'records' => '1', 'fields' => 'tab_access']);
		// $sampleReportsField = \REDCap::getData(['project_id' => (string) $pid, 'records' => '1', 'fields' => 'tab_access_2']);
		preg_match_all("/\d*, ([\w ]*)(?:(?:\\\\n)|(?:$))/", $project->metadata['tab_access']['element_enum'], $matches);
		$dashboardsCount = count($matches[1]);
		preg_match_all("/\d*, ([\w ]*)(?:(?:\\\\n)|(?:$))/", $project->metadata['tab_access_2']['element_enum'], $matches);
		$reportsCount = count($matches[1]);
		
		// // the following is useful to use as test/mock data
		// $data = [];
		// $data[4] = [];
		// $data[4][40] = [];
		// $data[4][40]['record_id'] = "4";
		// $data[4][40]['role_name'] = "test";
		// $data[4][40]['role_active'] = true;
		// $data[4][40]['role_external'] = false;
		// $data[4][40]["my_first_instrument_complete"] = "2";
		// $data[4][40]['project_access'] = '{"1":{"role":"2","dag":null},"3":{"role":null,"dag":null},"5":{"role":null,"dag":null}}';
		// $data[4][40]['tab_access'] = [];
		// $data[4][40]['tab_access'][0] = "1";
		// $data[4][40]['tab_access'][1] = "1";
		// $data[4][40]['tab_access'][2] = "1";
		// $data[4][40]['tab_access'][3] = "0";
		// $data[4][40]['tab_access'][4] = "0";
		// $data[4][40]['tab_access'][5] = "0";
		// $data[4][40]['tab_access'][6] = "0";
		// $data[4][40]['tab_access'][7] = "0";
		// $data[4][40]['tab_access'][8] = "0";
		// $data[4][40]['tab_access'][9] = "0";
		// $data[4][40]['tab_access'][10] = "0";
		// $data[4][40]['tab_access'][11] = "0";
		// $data[4][40]['tab_access_2'] = [];
		// $data[4][40]['tab_access_2'][0] = "1";
		// $data[4][40]['tab_access_2'][1] = "1";
		// $data[4][40]['tab_access_2'][2] = "1";
		// $data[4][40]['tab_access_2'][3] = "0";
		// $data[4][40]['tab_access_2'][4] = "0";
		// $data[4][40]['tab_access_2'][5] = "0";
		// $data[4][40]['tab_access_2'][6] = "0";
		
		$data = [];
		$rid = 0;
		foreach ($_POST as $record_id => $record){
			$rid++;
			$data[$rid] = [];
			$data[$rid][$eid] = [];
			$data[$rid][$eid]["record_id"] = (string) $rid;
			$data[$rid][$eid]["role_name"] = preg_replace('/[[:cntrl:]]/', '', $record['name']);
			$data[$rid][$eid]["role_active"] = $record['active']==="true" ? "1" : "0";
			$data[$rid][$eid]["role_external"] = $record['active']==="true" ? "1" : "0";
			$data[$rid][$eid]["project_access"] = json_encode($record['projects']);
			$data[$rid][$eid]["tab_access"] = [];
			$data[$rid][$eid]["tab_access_2"] = [];
			$data[$rid][$eid]["my_first_instrument_complete"] = "2";
			
			for ($i = 0; $i < $dashboardsCount; $i++) {
				if (in_array($i+1, $record['dashboards'])) {
					$data[$rid][$eid]["tab_access"][$i] = "1";
				} else {
					$data[$rid][$eid]["tab_access"][$i] = "0";
				}
			}
			
			for ($i = 0; $i < $reportsCount; $i++) {
				if (in_array($i+1, $record['reports'])) {
					$data[$rid][$eid]["tab_access_2"][$i] = "1";
				} else {
					$data[$rid][$eid]["tab_access_2"][$i] = "0";
				}
			}
		}
		
		ob_start();
		var_dump($data);
		$txt = ob_get_contents();
		ob_end_clean();
		fwrite($this->log, "data:\n$txt\n\n");
		
		$results = \REDCap::saveData($pid, 'array', $data);
		
		// dry run:
		// $results = \REDCap::saveData($pid, 'array', $data, null, null, null, null, null, null, false);
		
		fwrite($this->log, "\nresults: " . print_r($results, true) . "\n\n");
		fwrite($this->log, "END\n");
	}
}