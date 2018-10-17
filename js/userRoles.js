$(function() {
	$("#roleDetailsCard").hide();
	$("#projectDetailsCard").hide();
	$("#rolesCard").on("click", function(event) {
		// unselect all buttons and hide details window
		if (event.target.tagName == "BUTTON") {
			return
		}
		$("#rolesTable button[checked]").each(function(i, e) {UserRoles.toggleButton(e)})
		$("#roleDetailsCard").hide(250)
	})
	
	// read json data from hidden div into our UserRoles global obj
	UserRoles.roles = JSON.parse($("#rolesData").html())
	console.log(UserRoles.roles.Receptionist.projects[5].dags[0])
	
	// add role buttons to rolesCard
	for (var role in UserRoles.roles) {
		let button = "<tr><td><button class=\"btn\" type=\"button\" onclick=\"UserRoles.roleSelect(this)\">" + role + "</button></td></tr>"
		$("#rolesTable tbody").append(button)
	}
})

var UserRoles = {
	createRole: function(){
		let newRole = "<tr><td><button class=\"btn\" type=\"button\" onclick=\"UserRoles.roleSelect(this)\">New Role</button></td></tr>"
		$("#rolesTable tbody").append(newRole)
	},
	
	deselectAll: function(which) {
		$("[name^='" + which + "'][checked]").each(function() {UserRoles.toggleButton(this)})
	},
	
	getDashboardItems: function(pageIndex = 1){
		// console.log("my ajax url: " + decodeURIComponent(document.cookie.slice(8)) + "\ajax.php");
		// console.log("pageIndex: " + pageIndex);
		$.ajax({
			data: {
				page: "ajax",
				function: "getDashboardItems",
				pageIndex: pageIndex
			},
			success: function(data){
				console.log(data);
			}
		})
	},
	
	roleSelect: function(btn){
		$("#rolesTable button[checked]").each(function(i, e) {UserRoles.toggleButton(e)})
		UserRoles.toggleButton(btn)
		$("#roleDetailsCard").show(250)
		// update interface:
		// update which dag/roles/dashboard/reports are selected
		
		// also update role details panel
		$("#roleDetailsCard div div p").html("")
		let whichRole = $(btn).html()
		// let details = `
			// <table>
				// <tr>
					// <th>Role Name:</th>
					// <td>
						// <h3 id="roleNameHeader"></h3>
					// </td>
				// </tr>
				// <tr>
					// <th>Active:</th>
					// <td>
						// <input type="checkbox" class="form-control-lg" id="roleActiveBox">
					// </td>
				// </tr>
				// <tr>
					// <th>External:</th>
					// <td>
						// <input type="checkbox" id="roleExternalBox">
					// </td>
				// </tr>
			// </table>
		// `
		// $("#roleDetailsCard div div div").html(details)
		$("#roleNameHeader").html(whichRole)
		// console.log("name: " + whichRole + ", active: " + String(UserRoles.roles[whichRole].active) + ", external: " + String(UserRoles.roles[whichRole].external))
		UserRoles.roles[whichRole].active == true ? $("#roleActiveBox").attr('checked', true) : $("#roleActiveBox").attr('checked', null)
		UserRoles.roles[whichRole].external == true ? $("#roleExternalBox").attr('checked', true) : $("#roleExternalBox").attr('checked', null)
	},
	
	selectAll: function(which) {
		$("[name^='" + which + "']:not([checked])").each(function() {UserRoles.toggleButton(this)})
	},
	
	toggleButton: function(btn) {
		if ($(btn).attr("checked")) {
			$(btn).attr("checked", null);
			$(btn).css("font-weight", 400);
			$(btn).removeClass('btn-primary');
			// $(btn).closest('td').animate({backgroundColor: '#fff'}, 500)
		} else {
			$(btn).attr("checked", true);
			$(btn).css("font-weight", 500);
			$(btn).addClass('btn-primary');
			// $(btn).closest('td').animate({backgroundColor: '#e0efff'}, 500)
		}
	}
}