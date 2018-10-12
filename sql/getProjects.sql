SELECT DISTINCT p.project_id, p.app_title, p.project_note
FROM redcap_projects p
WHERE p.project_id IN (SELECT projects.project_id
	FROM redcap_projects projects, redcap_user_rights users
	WHERE users.username = "[USERID]" and users.project_id = projects.project_id)
ORDER BY p.project_id