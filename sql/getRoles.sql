SELECT DISTINCT roles.project_id, roles.role_name
FROM redcap_user_roles roles
WHERE roles.project_id IN ([PID_LIST])
ORDER BY roles.project_id