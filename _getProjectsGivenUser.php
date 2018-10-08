<?php

header('content-type: text/plain');

if (defined('USERID')) {
	$userid = USERID;
	$sql = "SELECT p.online_offline, p.project_id, p.two_factor_exempt_project, p.project_note, p.project_name,	p.app_title, p.status, p.draft_mode, p.surveys_enabled, p.date_deleted, p.repeatforms
			FROM redcap_user_rights u, redcap_projects p
			WHERE u.project_id = p.project_id and u.username = \"$userid\"
			ORDER BY p.project_id";
	$q = db_query($sql);
	while ($row = db_fetch_array($q)) {
		print_r($row['app_title'] . "\n");
		$row = NULL;
	}
	echo "\nend\n";
} else {
	echo "Unabled to determine which user to query for -- USERID constant not defined\n";
	echo "This is not a NOAUTH page, if you are not logged in, please log in to REDCap and refresh this page\n";
}

exit;