<?php
namespace Vanderbilt\DataCore\UserRoles;

class UserRoles extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
		// Other code to run when object is instantiated
		$this->log = fopen("log.txt", "w");
		fwrite($this->log, "starting log...\n");
	}
	
	public function __destruct() {
		fwrite($this->log, "Closing log");
		fclose($this->log);
	}
	
	public function getDashboardItems() {
		// mock data
		$arr = [];
		for ($i = 0; $i < 25; $i++){
			array_push($arr, "Dashboard Item " . ($i+1));
		}
		
		// // return page worth of items
		// $a = ($page_index - 1) * 10;
		// return array_slice($arr, $a, 10);
		
		// return all
		return $arr;
	}
	
	public function getReportItems($page_index=1) {
		// get or mock items
		$arr = [];
		for ($i = 0; $i < 16; $i++){
			array_push($arr, "Report Item " . ($i+1));
		}
		
		// // return page worth of items
		// $a = ($page_index - 1) * 10;
		// return array_slice($arr, $a, 10);
		
		// return all
		return $arr;
	}
	
	public function makeProjectsTable() {
		$userid = "carl";
		if (gettype($userid) != 'string') {
			return "<p>Unable to determine USERID -- can't retrieve list of projects. Email carl.w.reed@vumc.org</p>";
		}
		
		// get list of projects user has access to
		$projects = [];
		$pids = [];
		$sql = file_get_contents($this->getUrl("sql/getProjects.sql"));
		$sql = str_replace("[USERID]", $userid, $sql);
		$query = db_query($sql);
		while ($row = db_fetch_array($query)) {
			$pids[] = $row['project_id'];
			$pid = (int) $row['project_id'];
			if (gettype($pid) != "integer") continue;
			$projects[$pid] = [
				"project_id" => $pid,
				"app_title" => $row['app_title'],
				"project_note" => $row['project_note'],
				"roles" => [],
				"dags" => []
			];
		}
		
		// get roles
		$sql = file_get_contents($this->getUrl("sql/getRoles.sql"));
		$sql = str_replace("[PID_LIST]", implode(", ", $pids), $sql);
		$query = db_query($sql);
		while ($row = db_fetch_array($query)) {
			$pid = (int) $row['project_id'];
			if (gettype($pid) == "integer") {
				if (isset($projects[$pid])) {
					$projects[$pid]["roles"][] = $row["role_name"];
				}
			}
		}
		
		// get dags
		$sql = file_get_contents($this->getUrl("sql/getDags.sql"));
		$sql = str_replace("[PID_LIST]", implode(", ", $pids), $sql);
		$query = db_query($sql);
		while ($row = db_fetch_array($query)) {
			$pid = (int) $row['project_id'];
			if (gettype($pid) == "integer") {
				if (isset($projects[$pid])) {
					$projects[$pid]["dags"][] = $row["group_name"];
				}
			}
		}
		
		// construct and return html -- search bar, table with project buttons
		$searchbar = "
			<nav style=\"flex-wrap:nowrap\" class=\"navbar navbar-light bg-light\">
				<form class=\"form-inline\">
					<input class=\"form-control mr-sm-2\" type=\"search\" placeholder=\"search for role...\" aria-label=\"Search\">
					<button class=\"btn btn-primary\" type=\"button\"><i class=\"fas fa-search\"></i></button>
				</form>
			</nav>";
		
		$table = "
			<table>
				<thead>
					<tr>
						<th scope=\"col\">PID</th>
						<th scope=\"col\">Title</th>
						<th scope=\"col\">Notes</th>
					</tr>
				</thead>
				<tbody>";
			//  </tbody>
		// </table>
		
		// // log?
		// ob_start();
		// print_r($projects);
		// $temp = ob_get_contents();
		// ob_end_clean();
		// fwrite($this->log, $temp);
		
		// add table data (project/dag/role buttons & info) to table
		foreach ($projects as $index => $project) {
			$pid = $project['project_id'];
			$title = $project['app_title'];
			$note = $project['project_note'];
			$table .= "\n<tr>";
			$table .= "\n<td><span>$pid</span></td>";
			$table .= "\n<td><button id=\"projectButton$index\" class=\"btn btn-sm\" type=\"button\" onclick=\"UserRoles.toggleButton(this)\">$title</button></td>";
			$table .= "\n<td><span>$note</span></td>";
			$table .= "\n</tr>";
		}
		$table .= "\n</tbody>";
		$table .= "\n</table>";
		return $table;
	}
	
	public function makeRolesTable() {
		// search bar
		// button for create new role
		// button for delete role
		// table start
			// role 1
			// role 2...
		// table end
			
	}
	
	public function makeSelectTable($which) {
		$name = "get" . ucfirst($which) . "Items";
		$items = $this->$name();
		
		$htmlTable = <<<'EOD'
<table name="whichTable" class="table">
	<thead>
		<tr>
			<td>
				<button name="selectAllWhichItems" class="btn-primary btn-sm" onclick="UserRoles.selectAll('whichItem')">
					select all
				</button>
			</td>
			<td>
				<button name="selectNoneWhichItems" class="btn-primary btn-sm" onclick="UserRoles.deselectAll('whichItem')">
					select none
				</button>
			</td>
			<td>
			</td>
		</tr>
	</thead>
	<tbody>
EOD;
		$rowCount = ceil(count($items)/3);
		fwrite($this->log, "rowCount: $rowCount\n");
		
		for ($i = 0; $i <= $rowCount; $i++) {
			if (!isset($items[($i*3)])) continue;
			$htmlItem = "<tr>\n";
			for ($j = 0; $j < 3; $j++) {
				$itemsIndex = $i*3 + $j;
				if (!isset($items[$itemsIndex])) continue;
				$htmlItem = $htmlItem . <<<'EOD'
	<td>
		<button name="ITEM_NAME" type="button" class="btn" onclick="UserRoles.toggleButton(this)">
			BUTTON_CONTENTS
		</button>
	</td>
EOD;
				$htmlItem = str_replace("BUTTON_CONTENTS", $items[$itemsIndex], $htmlItem);
				$itemsIndex++;
				$htmlItem = str_replace("ITEM_NAME", "whichItem$itemsIndex", $htmlItem) . "\n";
			}
			$htmlItem = $htmlItem . "</tr>\n";
			$htmlTable = $htmlTable . $htmlItem;
		}

		$htmlTable = $htmlTable . <<<'EOD'
	</tbody>
</table>
EOD;
		$htmlTable = str_replace("which", $which, $htmlTable);
		$htmlTable = str_replace("Which", ucfirst($which), $htmlTable);
		return $htmlTable . "\n";
	}
}