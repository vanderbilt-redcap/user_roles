<?php
namespace Vanderbilt\DataCore\UserRoles;

class UserRoles extends \ExternalModules\AbstractExternalModule {
	// public function __construct() {
		// parent::__construct();
		// // Other code to run when object is instantiated
	// }
	
	public function getDashboardItems($page_index=1) {
		// get or mock items
		$arr = [];
		for ($i = 0; $i < 25; $i++){
			array_push($arr, "Dashboard Item " . ($i+1));
		}
		
		// return page worth of items
		$a = ($page_index - 1) * 10;
		return array_slice($arr, $a, 10);
	}
	
	public function getReportItems($page_index=1) {
		// get or mock items
		$arr = [];
		for ($i = 0; $i < 25; $i++){
			array_push($arr, "Report Item " . ($i+1));
		}
		
		// return page worth of items
		$a = ($page_index - 1) * 10;
		return array_slice($arr, $a, 10);
	}
}