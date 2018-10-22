$(function() {
	// add json data to our global UserRoles object
	// UserRoles.[customRoles, roles, dags, dashboards, reports]
	UserRoles = JSON.parse($("#data").html())
	
	// add role table rows using provided hidden #data
	var falseCheckbox = "<input type=\"checkbox\">"
	var trueCheckbox = "<input type=\"checkbox\" checked>"
	for (var role in UserRoles.customRoles) {
		let tableRow = `
		<tr>
			<td><button type=\"button\" class=\"btn\">${role}</button></td>
			<td>${UserRoles.customRoles[role].active ? trueCheckbox : falseCheckbox}</td>
			<td>${UserRoles.customRoles[role].external ? trueCheckbox : falseCheckbox}</td>
		</tr>
		`
		$("#rolesDiv table tbody").append(tableRow)
	}
	
	// add dashboard and report items
	dashItems = UserRoles.dashboards.map(function(name, index) {return "<li><button dashboardid=\"" + index + "\" class=\"btn\" type=\"button\">" + name + "</button></li>"})
	$("#dashboardsDiv ul").append(dashItems.join(''))
	reportItems = UserRoles.reports.map(function(name, index) {return "<li><button reportid=\"" + index + "\" class=\"btn\" type=\"button\">" + name + "</button></li>"})
	$("#reportsDiv ul").append(reportItems.join(''))
	
	// click handlers
	$("#rolesDiv").on("click", "td:nth-child(1) button", function() {
		// user clicked a role
		console.log("role select")
	})
	
	$("#projectsDiv").on("click", "td:nth-child(2) button", function() {
		// user clicked a project (in projects table) -- either select all in project, or if all selected, unselect all
		// collect all role and dag buttons belonging to this project
		items = []
		$projectRow = $(this).parent().parent()
		$projectRow.find("[roleid], [dagid]").each(function(key, val) {items.push(val)})
		$nextRow = $projectRow.next()
		$possibleProjectID = $nextRow.children(":first-child").html()
		while ($possibleProjectID == "") {
			$nextRow.find("[roleid], [dagid]").each(function(key, val) {items.push(val)})
			$nextRow = $nextRow.next()
			$possibleProjectID = $nextRow.children(":first-child").html()
		}
		
		
		allItemsSelected = true
		$(items).each(function(i, val) {$(val).find('button').hasClass('selected') ? null : allItemsSelected = false})
		allItemsSelected ? $(items).find('button').removeClass('selected') : $(items).find('button').addClass('selected')
	})
	
	// make reports, dashboard items, roles, and dags selectable
	toggle = function() {$(this).hasClass('selected') ? $(this).removeClass('selected') : $(this).addClass('selected')}
	$("#projectsDiv").on("click", "td:nth-child(3) button", toggle)
	$("#projectsDiv").on("click", "td:nth-child(4) button", toggle)
	$("#dashboardsDiv").on("click", "li button", toggle)
	$("#reportsDiv").on("click", "li button", toggle)
})