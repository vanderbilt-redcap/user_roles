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
	
	public function getDashboardItemsTable() {
		$items = $this->getDashboardItems();
		
		$htmlTable = <<<'EOD'
		<table class="table mb-5 table-responsive-xl">
			<thead>
				<tr class="d-flex">
					<th class="col">Dashboard Items</th>
				</tr>
			</thead>
			<tbody>
				<tr class="d-flex">
					<td class="col" scope="row">
EOD;
		for ($i = 0; $i < count($items); $i++) {
			$htmlItem = <<<'EOD'
				<tr class="d-flex">
					<td class="col" scope="row">
						<button name="ITEM_NAME" type="button" class="btn" onclick="UserRoles.toggleButton(this)">
							BUTTON_CONTENTS
						</button>
					</td>
				</tr>
EOD;
			$htmlItem = str_replace("ITEM_NAME", "dashboardItem$i", $htmlItem);
			$htmlItem = str_replace("BUTTON_CONTENTS", $items[$i], $htmlItem);
			$htmlTable = $htmlTable . $htmlItem;
		}

		$htmlTable = $htmlTable . <<<'EOD'
			</tbody>
		</table>
EOD;
	}
	
	public function getReportItems($page_index=1) {
		// get or mock items
		$arr = [];
		for ($i = 0; $i < 25; $i++){
			array_push($arr, "Report Item " . ($i+1));
		}
		
		// // return page worth of items
		// $a = ($page_index - 1) * 10;
		// return array_slice($arr, $a, 10);
		
		// return all
		return $arr;
	}
}