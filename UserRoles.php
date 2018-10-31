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
				"projects" => $record["project_role"],
				"dashboards" => [],
				"reports" => []
			);
			$newRole["active"] = $record["role_active"]==1 ? "true" : "false";
			$newRole["external"] = $record["affiliation"]==1 ? "true" : "false";
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
			
			$data["customRoles"][$record["role_name"]] = $newRole;
		}
		
		
		return json_encode($data);
		
		// dev test/mock data:
		// return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "dev" . DIRECTORY_SEPARATOR . "allData.json");
	}
	
	public function getProjectsTableBody(){
		$sql = file_get_contents($this->getUrl('sql' . DIRECTORY_SEPARATOR . 'getProjects.sql'));
		$sql = str_replace("[USERID]", $this->dev ? "carl" : USERID, $sql);
		$query = db_query($sql);
		$projects = [];
		while ($row = db_fetch_array($query)) {
			$projects[] = [$row["project_id"], $row["app_title"]];
		}
		
		// test for 0 projects
		if (count($projects) == 0) {
			return "<p>There are no projects associated with your user id: " . USERID . "</p>";
		}
		
		// the following replaces [PID_LIST] identifier in sql queries to follow -- it looks like "5, 13, 97, ..." where 5 is a pid
		$pidList = implode(", ", array_column($projects, 0));
		// fwrite($this->log, "\$pidList: $pidList\n");
		
		// get roles for each project
		$sql = file_get_contents($this->getUrl('sql' . DIRECTORY_SEPARATOR . 'getRoles.sql'));
		$sql = str_replace("[PID_LIST]", $pidList, $sql);
		$query = db_query($sql);
		$roles = [];
		while ($row = db_fetch_array($query)) {
			$pid = $row["project_id"];
			
			!isset($roles[$pid]) ? $roles[$pid] = [] : "";
			$roles[$pid][] = [$row["role_id"], $row["role_name"]];
		}
		
		// get dags for each project
		$sql = file_get_contents($this->getUrl('sql' . DIRECTORY_SEPARATOR . 'getDags.sql'));
		$sql = str_replace("[PID_LIST]", $pidList, $sql);
		$query = db_query($sql);
		$dags = [];
		while ($row = db_fetch_array($query)) {
			$pid = $row["project_id"];
			
			!isset($dags[$pid]) ? $dags[$pid] = [] : "";
			$dags[$pid][] = [$row["group_id"], $row["group_name"]];
		}
		
		// construct html table
		$table = "";		// actually just table body
		foreach ($projects as $project) {
			$pid = $project[0];
			$title = $project[1];
			$projectRoles = $roles[$pid];
			$projectDags = $dags[$pid];
			$maxRows = max(count($projectRoles), count($projectDags));
			
			for ($i=0; $i<$maxRows; $i++) {
				$td1 = $i==0 ? "<td>$pid</td>" : "<td></td>";
				$td2 = $i==0 ? "<td><button type=\"button\" class=\"btn\">$title</button></td>" : "<td></td>";
				$td3 = isset($projectRoles[$i]) ? "<td roleid=\"" . $projectRoles[$i][0] . "\"><button type=\"button\" class=\"btn\">" . $projectRoles[$i][1] . "</button></td>" : "<td></td>";
				$td4 = isset($projectDags[$i]) ? "<td dagid=\"" . $projectDags[$i][0] . "\"><button type=\"button\" class=\"btn\">" . $projectDags[$i][1] . "</button></td>" : "<td></td>";
				$table .= "<tr>
					$td1
					$td2
					$td3
					$td4
				</tr>";
			}
		}
		
		// // dev only logging:
		// ob_start();
		// echo "\$projects:\n";
		// print_r($projects);
		// echo "\$roles:\n";
		// print_r($roles);
		// echo "\$dags:\n";
		// print_r($dags);
		// fwrite($this->log, ob_get_contents());
		// ob_end_clean();
		
		return $table;
	}
}