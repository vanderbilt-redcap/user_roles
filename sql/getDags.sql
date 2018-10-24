SELECT DISTINCT dags.group_id, dags.project_id, dags.group_name
FROM redcap_data_access_groups dags
WHERE dags.project_id IN ([PID_LIST])
ORDER BY dags.project_id