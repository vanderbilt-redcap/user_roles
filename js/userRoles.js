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
	
	// set UserRoles.roles from data in cookie
	function getCookie(name) {
		var value = "; " + document.cookie
		var parts = value.split("; " + name + "=")
		if (parts.length == 2) return parts.pop().split(";").shift()
	}
	let data = getCookie('customRolesModuleRolesData')
	data = data.replace(/\+/g, ' ')
	data = decodeURIComponent(data)
	UserRoles.roles = JSON.parse(data)
	
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
		$("#roleDetailsCard div div p").html(""))
		let details = `
			<table>
				<tr>
					<th>Role Name:</th>
					<td>
						<h3></h3>
					</td>
				</tr>
				<tr>
					<th>Active:</th>
					<td>
						<input type="checkbox" class="form-control-lg" checked>
					</td>
				</tr>
				<tr>
					<th>External:</th>
					<td>
						<input type="checkbox">
					</td>
				</tr>
			</table>
		`
		$("#roleDetailsCard div div div").html(details)
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