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
	
	public function getAllData(){
		// dev test/mock data:
		return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "dev" . DIRECTORY_SEPARATOR . "allData.json");
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