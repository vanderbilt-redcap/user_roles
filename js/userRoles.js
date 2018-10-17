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
})

var UserRoles = {}