<?php
$fx = $_GET['function'];

switch ($fx) {
	case "getDashboardItems":
		ob_start();
		print_r($module->getDashboardItems($_GET['pageIndex']));
		$stringData = ob_get_contents();
		ob_end_clean();
		
		fwrite($module->log, "$stringData\n");
		echo $stringData;
		
		break;
}