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
		// get list of projects user has access to
		$userid = USERID;
		
		if (gettype($userid) != 'string') {
			return "<p>Unable to determine USERID -- can't retrieve list of projects. Email carl.w.reed@vumc.org</p>";
		}
		
		$sql = "SELECT p.online_offline, p.project_id, p.two_factor_exempt_project, p.project_note, p.project_name,	p.app_title, p.status, p.draft_mode, p.surveys_enabled, p.date_deleted, p.repeatforms
			FROM redcap_user_rights u, redcap_projects p
			WHERE u.project_id = p.project_id and u.username = \"$userid\"
			ORDER BY p.project_id";
		$q = db_query($sql);
		$projects = [];
		while ($row = db_fetch_array($q)) {
			$projects[] = [
				"project_id" => $row['project_id'],
				// "project_name" => $row['project_name'],
				"app_title" => $row['app_title'],
				"project_note" => $row['project_note'],
			];
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
		
		ob_start();
		print_r($projects);
		$temp = ob_get_contents();
		ob_end_clean();
		fwrite($this->log, $temp);
		
		// add table data (project buttons & info) to table
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
		$html = "<table></table>";
		return $html;
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