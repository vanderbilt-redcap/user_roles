$(function() {
	// add json data to our global UserRoles object
	UserRoles.roles = JSON.parse($("#rolesData").html())
	
	// initialize interface with newly stored roles information
	var falseCheckbox = "<input type=\"checkbox\">"
	var trueCheckbox = "<input type=\"checkbox\" checked>"
	for (var role in UserRoles.roles) {
		let tableRow = `
		<tr>
			<td>${role}</td>
			<td>${UserRoles.roles[role].active ? trueCheckbox : falseCheckbox}</td>
			<td>${UserRoles.roles[role].external ? trueCheckbox : falseCheckbox}</td>
		</tr>
		`
		$("#rolesDiv table tbody").append(tableRow)
	}
	
	$("#rolesDiv").on("click", "td:nth-child(1)", function() {
		// user clicked a role
		console.log("role select")
	})
	
	$("#projectsDiv").on("click", "td:nth-child(2)", function() {
		// user clicked a project (in projects table)
		console.log("project select")
	})
	
	$("#projectsDiv").on("click", "td:nth-child(3)", function() {
		// user clicked a role (in projects table)
		console.log("role in projects select")
	})
	
	$("#projectsDiv").on("click", "td:nth-child(4)", function() {
		// user clicked a dag (in projects table)
		console.log("dag select")
	})
	
	$("#dashboardsDiv").on("click", "li", function() {
		// user clicked a dashboard item
		console.log("dashboard select")
	})
	
	$("#reportsDiv").on("click", "li", function() {
		// user clicked a report item
		console.log("report select")
	})
})

var UserRoles = {
	roles: {}
}