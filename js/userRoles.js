$(function() {
	// add json data to our global UserRoles object
	UserRoles.roles = JSON.parse($("#rolesData").html())
	
	// initialize interface with newly stored roles information
	for (var role in UserRoles.roles) {
		let tableRow = ""
		$("#rolesTable tbody").append(button)
	}
})

var UserRoles = {}