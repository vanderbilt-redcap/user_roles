<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$rolesData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "dev" . DIRECTORY_SEPARATOR . "devRoles.json");
setcookie("customRolesModuleRolesData", $rolesData, time()+3600);

// prepare then print html header
$header = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "header.html");
$header = str_replace("CSS_PATH-", APP_PATH_CSS, $header);
$header = str_replace("MODULE_CSS_FILE", $module->getUrl('css/stylesheet.css'), $header);
$header = str_replace("JS_PATH-", APP_PATH_JS, $header);
$header = str_replace("MODULE_JS_FILE", $module->getUrl('js/userRoles.js'), $header);
echo $header;

// prepare html body
$body = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "body.html");

	// add roles table
	$roles = "\n".file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "card.html");
	$roles = str_replace("ID", "rolesCard", $roles);
	$roles = str_replace("CARD_HEADER", "Custom User Roles", $roles);
	$roles = str_replace("CARD_DESCRIPTION", "Create a new user role or edit existing roles", $roles);
	$roles = str_replace("CARD_CONTENTS", $module->makeRolesTable(), $roles);
	$body = str_replace("ROLES", $roles, $body);

	// add role details panel
	$details = "\n".file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "card.html");
	$details = str_replace("ID", "roleDetailsCard", $details);
	$details = str_replace("CARD_HEADER", "Role Details", $details);
	$body = str_replace("ROLE_DETAILS", $details, $body);

	// add projects table
	$projects = "\n".file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "card.html");
	$projects = str_replace("ID", "projectsCard", $projects);
	$projects = str_replace("CARD_HEADER", "Project - DAG Access", $projects);
	$projects = str_replace("CARD_DESCRIPTION", "Choose which DAGs of which projects the role should have access to", $projects);
	$projects = str_replace("CARD_CONTENTS", $module->makeProjectsTable(), $projects);
	$body = str_replace("PROJECTS", $projects, $body);

	// add project details panel
	$details = "\n".file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "card.html");
	$details = str_replace("ID", "projectDetailsCard", $details);
	$details = str_replace("CARD_HEADER", "Project Details", $details);
	$body = str_replace("PROJECT_DETAILS", $details, $body);

	// add dashboard select table to body
	$dashboardAccess = "\n" . file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "card.html");
	$dashboardAccess = str_replace("ID", "dashboardCard", $dashboardAccess);
	$dashboardAccess = str_replace("CARD_HEADER", "Dashboard Access", $dashboardAccess);
	$dashboardAccess = str_replace("CARD_DESCRIPTION", "Select the dashboard items this role should have access to", $dashboardAccess);
	$dashboardAccess = str_replace("CARD_CONTENTS", $module->makeSelectTable('dashboard'), $dashboardAccess);
	$body = str_replace("DASHBOARD_ACCESS", $dashboardAccess, $body);

	// add report select table to body
	$reportAccess = "\n" . file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "card.html");
	$reportAccess = str_replace("ID", "reportCard", $reportAccess);
	$reportAccess = str_replace("CARD_HEADER", "Report Access", $reportAccess);
	$reportAccess = str_replace("CARD_DESCRIPTION", "Select the report items this role should have access to", $reportAccess);
	$reportAccess = str_replace("CARD_CONTENTS", $module->makeSelectTable('report'), $reportAccess);
	$body = str_replace("REPORT_ACCESS", $reportAccess, $body);

// print body to doc
echo $body;

// print footer
echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "footer.html");