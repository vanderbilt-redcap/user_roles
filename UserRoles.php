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
	
	
}