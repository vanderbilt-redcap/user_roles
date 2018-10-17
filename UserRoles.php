<?php
namespace Vanderbilt\DataCore\UserRoles;

class UserRoles extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
		
		// create log that is in redcapversioned/ExternalModules
		$this->log = fopen("log.txt", "w");
		fwrite($this->log, "starting log...\n");
	}
	
	public function __destruct() {
		fwrite($this->log, "Closing log");
		fclose($this->log);
	}
	
	public function getProjectsData(){
		return "";
	}
	
	public function getRolesData(){
		// dev test/mock data:
		return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "dev" . DIRECTORY_SEPARATOR . "rolesData.json");
	}
}