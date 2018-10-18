<?php
$html = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "base.html");

// replace some contents in html head
$html = str_replace("JS_PATH_", APP_PATH_JS . DIRECTORY_SEPARATOR, $html);
$html = str_replace("MODULE_JS_FILE", $module->getUrl('js'. DIRECTORY_SEPARATOR .'userRoles.js'), $html);

// insert json data for roles and projects -- client uses it to initialize interface for user
$html = str_replace("ROLES_DATA", $module->getRolesData(), $html);

// replace identifiers with report list, dashboard list, and project and user role table bodies
$html = str_replace("PROJECTS_TABLE_BODY", $module->getProjectsTableBody(), $html);
$html = str_replace("DASHBOARD_LIST", $module->getDashboardList(), $html);
$html = str_replace("REPORT_LIST", $module->getReportList(), $html);

echo $html;