var UserRoles = {}

$(function() {
	// add json data to our global UserRoles object
	// UserRoles.[customRoles, roles, dags, dashboards, reports]
	UserRoles = JSON.parse($("#data").html())
	
	UserRoles.templates = {
		projectRow:`
			<tr>
				<td></td>
				<td>
					<div class="dd-container">
						<button onclick="$(this).siblings('[class*=dd-content').toggle(100)" class="dd-header project-dd btn">(Unassigned)<i style='padding-left: 8px' class='fas fa-caret-down'></i></button>
						<div class="dd-content">
						</div>
					</div>
				</td>
				<td>
					<div class="dd-container">
						<button onclick="$(this).siblings('[class*=dd-content').toggle(100)" class="dd-header role-dd btn">(Unassigned)<i style='padding-left: 8px' class='fas fa-caret-down'></i></button>
						<div class="dd-content">
							<span>(Unassigned)</span>
						</div>
					</div>
				</td>
				<td>
					<div class="dd-container">
						<button onclick="$(this).siblings('[class*=dd-content').toggle(100)" class="dd-header dag-dd btn">(Unassigned)<i style='padding-left: 8px' class='fas fa-caret-down'></i></button>
						<div class="dd-content">
							<span>(Unassigned)</span>
						</div>
					</div>
				</td>
			</tr>`,
		projectList: ""
	}
	
	UserRoles.addRole = function(record_id) {
		if (!record_id) {
			// console.log(UserRoles.customRoles)
			var newRole = {
				name: "New Role",
				projects: {},
				dashboards: [],
				reports: [],
				active: "false",
				external: "false"
			}
			var flag = false
			var index = 1
			while (!flag) {
				if (UserRoles.customRoles[index] == undefined) {
					flag = true
					record_id = index
				}
				index += 1
			}
			UserRoles.customRoles[record_id] = newRole
			// console.log(UserRoles.customRoles)
		}
		
		var role = UserRoles.customRoles[record_id]
		var falseCheckbox = "<input type=\"checkbox\">"
		var trueCheckbox = "<input type=\"checkbox\" checked>"
		let tableRow = `
		<tr>
			<td><button record_id=\"${record_id}\" type=\"button\" class=\"btn roleButton\">${role.name}</button></td>
			<td>${role.active=="true" ? trueCheckbox : falseCheckbox}</td>
			<td>${role.external=="true" ? trueCheckbox : falseCheckbox}</td>
		</tr>
		`
		$("#rolesDiv table tbody").append(tableRow)
		$("#rolesDiv tbody tr:last td:eq(1) input").addClass('activeCheckbox')
		$("#rolesDiv tbody tr:last td:eq(2) input").addClass('externalCheckbox')
		
		// enable save changes button
		$("#rolesDiv button:eq(3)").show(100)
	}
	
	UserRoles.deleteRole = function() {
		// removed entry from UserRoles.customRoles
		delete UserRoles.customRoles[String($(".roleButton.selected").attr('record_id'))]
		
		// remove row from user roles table
		$(".roleButton.selected").closest('tr').remove()
		
		// hide delete and rename buttons
		$("#rolesDiv button:eq(1)").hide(100)
		$("#rolesDiv button:eq(2)").hide(100)
		
		// remove project access table rows and untoggle report/dashboard items
		$(".btn").removeClass("selected")
		$("#projectsDiv tbody").html("")
	}
	
	UserRoles.renameRole = function() {
		$(".roleButton.selected").html("<input id='newRoleName' type='text'>")
		$(".roleButton.selected input").focus()
		$(".roleButton.selected input").focusout(function(e) {
			var newName = $(".roleButton.selected input").val()
			$(".roleButton.selected").html(newName)
			UserRoles.customRoles[String($(".roleButton.selected").attr('record_id'))].name = newName
			$("#rolesDiv button:eq(3)").show(100)
		})
	}
	
	UserRoles.saveChanges = function() {
		$("#rolesDiv button:eq(3)").html("Saving...")
		
		// send UserRoles.customRoles data to server so it can save records
		var url = window.location.href.replace("manage_roles", "save_changes")
		var data = UserRoles.customRoles
		
		$.ajax({
			url: url,
			type: "post",
			data: data,
			complete: function(response, mode) {
				// console.log("response text: " + response.responseText)
				// console.log("jqxhr mode: " + mode)
				var text = (mode == "success") ? "Success!" : "Error - Try Again"
				$("#rolesDiv button:eq(3)").html(text)
				$("#rolesDiv button:eq(3)").delay(1000).hide(100, function() {
					$("#rolesDiv button:eq(3)").html("Save Changes")
				})
			}
		})
	}
	
	UserRoles.seedRoleDagButtons = function(projectDropdown){
		pid = $(projectDropdown).closest('tr').find('td:eq(0)').html()
		project = UserRoles.projects[String(pid)]
		
		// switching projects so unassign current role/dag
		$(projectDropdown.closest('tr').find(".dd-header")[1]).html("(Unassigned)<i style='padding-left: 8px' class='fas fa-caret-down'></i>")
		$(projectDropdown.closest('tr').find(".dd-header")[2]).html("(Unassigned)<i style='padding-left: 8px' class='fas fa-caret-down'></i>")
		
		// seed dd options
		roleDiv = projectDropdown.closest('tr').find(".dd-content")[1]
		dagDiv = projectDropdown.closest('tr').find(".dd-content")[2]
		if (project) {
			roleContent = "<span>(Unassigned)</span>"
			for (i=0; i<project.roles.length; i++) {
				roleContent += "\n<span>" + UserRoles.roles[project.roles[String(i)]] + "</span>"
			}
			$(roleDiv).html(roleContent)
			
			dagContent = "<span>(Unassigned)</span>"
			for (i=0; i<project.dags.length; i++) {
				dagContent += "\n<span>" + UserRoles.dags[project.dags[String(i)]] + "</span>"
			}
			$(dagDiv).html(dagContent)
		} else {
			$(roleDiv).html("<span>(Unassigned)</span>")
			$(dagDiv).html("<span>(Unassigned)</span>")
		}
	}
	
	UserRoles.addProjectRow = function(pid, role_id, group_id){
		if ($(".roleButton.selected").length == 0) return
		let projectRow = $(UserRoles.templates.projectRow)
		// seed new row first dropdown with list of projects
		projectRow.find(".dd-content").first().append(UserRoles.templates.projectList)
		$("#projectsDiv").find("tbody").append(projectRow)
		
		// if pid/role/dag supplied set those
		if (pid) {
			$("#projectsDiv tr:last td:eq(0)").html(pid)
			// set project dropdown text and seed role/dag button options
			$("#projectsDiv tr:last .dd-content:eq(0) span").each(function(i, element){
				if ($(element).text().indexOf(UserRoles.projects[pid].name) != -1) {
					ddButton = $("#projectsDiv tr:last .dd-header:eq(0)")
					ddButton.html($(element).text()+"<i style='padding-left: 8px' class='fas fa-caret-down'></i>")
					UserRoles.seedRoleDagButtons(ddButton)
				}
			})
		}
		if (role_id) {
			role_name = UserRoles.roles[role_id]
			$("#projectsDiv tr:last .dd-content:eq(1) span").each(function(i, element){
				if (i==0) return
				if ($(element).text() == role_name) {
					ddButton = $("#projectsDiv tr:last .dd-header:eq(1)")
					ddButton.html(role_name+"<i style='padding-left: 8px' class='fas fa-caret-down'></i>")
				}
			})
		}
		if (group_id) {
			group_name = UserRoles.dags[group_id]
			$("#projectsDiv tr:last .dd-content:eq(2) span").each(function(i, element){
				if (i==0) return
				if ($(element).text() == group_name) {
					ddButton = $("#projectsDiv tr:last .dd-header:eq(2)")
					ddButton.html(group_name+"<i style='padding-left: 8px' class='fas fa-caret-down'></i>")
				}
			})
		}
	}
	
	UserRoles.removeProjectRow = function(){
		// find selected row if exists
		selectedRow = $("#projectsDiv tbody tr.selected")
		selectedRow.length > 0 ? selectedRow.first().remove() : $("#projectsDiv tbody tr").last().remove()
		UserRoles.setUserRoles()
	}
	
	UserRoles.setUserRoles = function() {
		// find out which role is selected
		var selectedRole = UserRoles.customRoles[$(".roleButton.selected").attr('record_id')]
		if (!selectedRole) {
			console.log("no selected role")
			return
		}
		// convert project access table contents to json to store in UserRoles
		var projectAccess = {}
		$("#projectsDiv tbody tr").each(function(index, row) {
			var pid = $(row).find('td:eq(0)').html()
			var role = $(row).find('td:eq(2) button').html()
			role = role.replace("<i style='padding-left: 8px' class='fas fa-caret-down'></i>", "")
			role = role.replace('<i style="padding-left: 8px" class="fas fa-caret-down"></i>', "")
			for (var key in UserRoles.roles) {
				if (UserRoles.roles[key] == role) role = key
			}
			
			var dag = $(row).find('td:eq(3) button').html()
			dag = dag.replace("<i style='padding-left: 8px' class='fas fa-caret-down'></i>", "")
			dag = dag.replace('<i style="padding-left: 8px" class="fas fa-caret-down"></i>', "")
			for (var key in UserRoles.dags) {
				if (UserRoles.dags[key] == dag) dag = key
			}
			
			projectAccess[pid] = {"role": null, "dag": null}
			if (role != "(Unassigned)") projectAccess[pid].role = role
			if (dag != "(Unassigned)") projectAccess[pid].dag = dag
		})
		
		selectedRole.projects = projectAccess
		// (enable save changes button)
		$("#rolesDiv button:eq(3)").show(100)
	}
	
	// // Project divs section for add/remove buttons
	// build projectList template string
	for (var pid in UserRoles.projects){
		UserRoles.templates.projectList += "<span>" + UserRoles.projects[pid].name + "</span>\n"
	}
	for (var role_name in UserRoles.customRoles){
		var role = UserRoles.customRoles[role_name]
		role.projects = JSON.parse(role.projects)
	}
	
	// add roles
	for (var record_id in UserRoles.customRoles) {
		UserRoles.addRole(record_id)
	}
	
	// add dashboard and report items
	dashItems = UserRoles.dashboards.map(function(name, index) {return "<li><button dashboardid=\"" + index + "\" class=\"btn\" type=\"button\">" + name + "</button></li>"})
	$("#dashboardsDiv ul").append(dashItems.join(''))
	reportItems = UserRoles.reports.map(function(name, index) {return "<li><button reportid=\"" + index + "\" class=\"btn\" type=\"button\">" + name + "</button></li>"})
	$("#reportsDiv ul").append(reportItems.join(''))
	
	/////////// click handlers ->
	// when click on role buttons ->
	$("#rolesDiv").on("click", "td:nth-child(1) button", function() {
		// user clicked a role
		$(".roleButton").removeClass("selected")
		$(this).addClass('selected')
		var selectedRole = UserRoles.customRoles[$(".roleButton.selected").attr('record_id')]
		
		// show delete and rename buttons
		$("#rolesDiv button:eq(1)").show(100)
		$("#rolesDiv button:eq(2)").show(100)
		
		// add/remove project access rows as necessary to match existing role access
		$("#projectsDiv tbody").html("")
		
		Object.keys(selectedRole.projects).forEach(function(pid, index) {
			var role_id = selectedRole.projects[pid].role
			var group_id = selectedRole.projects[pid].dag
			if (UserRoles.projects[pid]) {
				UserRoles.addProjectRow(pid, role_id, group_id)
			}
			// console.log("pid: " + pid)
		})
		
		// toggle dashboard/report access items to match existing
		$("#dashboardsDiv button").removeClass("selected")
		for (var i in selectedRole.dashboards) {
			$('[dashboardid="'+(selectedRole.dashboards[i]-1)+'"]').addClass('selected')
		}
		$("#reportsDiv button").removeClass("selected")
		for (var i in selectedRole.reports) {
			$('[reportid="'+(selectedRole.reports[i]-1)+'"]').addClass('selected')
		}
	})
	
	// when click on active or external checkboxes ->
	$("#rolesDiv").on('click', ".activeCheckbox", function() {
		var checked = $(this).prop('checked')
		var record_id = $(this).closest('tr').find('td:eq(0) button').attr('record_id')
		var role = UserRoles.customRoles[record_id]
		if (role) {
			role.active = String(checked)
		}
		// enable save changes button
		$("#rolesDiv button:eq(3)").show(100)
	})
	$("#rolesDiv").on('click', ".externalCheckbox", function() {
		var checked = $(this).prop('checked')
		var record_id = $(this).closest('tr').find('td:eq(0) button').attr('record_id')
		var role = UserRoles.customRoles[record_id]
		if (role) {
			role.external = String(checked)
		}
		// enable save changes button
		$("#rolesDiv button:eq(3)").show(100)
	})
	
	// when click on report or dashboard items ->
	toggle = function(button, type) {
		var selectedRole = UserRoles.customRoles[$(".roleButton.selected").attr('record_id')]
		if ($(button).hasClass('selected')) {
			$(button).removeClass('selected')
			var itemIndex = String(parseInt($(button).attr(type+'id')) + 1)
			if (selectedRole) {
				var arrayIndex = selectedRole[type+'s'].indexOf(itemIndex)
				if (arrayIndex >= 0) {
					selectedRole[type+'s'].splice(arrayIndex, 1)
					// (enable save changes)
					$("#rolesDiv button:eq(3)").show(100)
				}
			}
		} else {
			$(button).addClass('selected')
			var itemIndex = String(parseInt($(button).attr(type+'id')) + 1)
			if (selectedRole) {
				var arrayIndex = selectedRole[type+'s'].indexOf(itemIndex)
				if (arrayIndex == -1) {
					selectedRole[type+'s'].push(itemIndex)
					// (enable save changes)
					$("#rolesDiv button:eq(3)").show(100)
				}
			}
		}
	}
	$("#dashboardsDiv").on("click", "li button", function() {toggle(this, 'dashboard')})
	$("#reportsDiv").on("click", "li button", function() {toggle(this, 'report')})
	$("#projectsDiv").on("click", "tbody tr", function(){
		// untoggle all project table rows except newly selected
		$("#projectsDiv tbody tr").removeClass('selected')
		$(this).addClass('selected')
	})
	
	///// dropdown/project access table items ->
	// handle dropdown content clicking
	$("body").on("click", ".dd-content *", function(){
		// determine project id and title
		var ddButton = $(this).parent().siblings(".dd-header")
		var clicked = $(this).html()
		
		var projectTitle
		if (ddButton.is(".project-dd")) {
			projectTitle = clicked.replace("<i style='padding-left: 8px' class='fas fa-caret-down'></i>", "")
		} else {
			projectTitle = $(this).closest('tr').find('td:eq(1) button').html().replace("<i style='padding-left: 8px' class='fas fa-caret-down'></i>", "")
		}
		projectTitle = projectTitle.replace('<i style="padding-left: 8px" class="fas fa-caret-down"></i>', "")
		
		var pid
		for (var key in UserRoles.projects) {
			if (UserRoles.projects[key].name == projectTitle) {pid = key}
		}
		
		// set pid in column 1 of project access table
		$(this).closest('tr').find('td:eq(0)').html(pid)
		
		var ddType
		if (ddButton.hasClass("project-dd")) ddType = "project"
		if (ddButton.hasClass("role-dd")) ddType = "role"
		if (ddButton.hasClass("dag-dd")) ddType = "dag"
		
		// put clicked text in dropdown button
		ddButton.html(clicked + "<i style='padding-left: 8px' class='fas fa-caret-down'></i>")
		$(this).parent().toggle(100)
		
		// if project dropdown got altered, seed options for role and dag dropdowns
		if (ddButton.is(".project-dd")) {
			var project = UserRoles.projects[String(pid)]
			
			// switching projects so unassign current role/dag
			$(ddButton.closest('tr').find(".dd-header")[1]).html("(Unassigned)<i style='padding-left: 8px' class='fas fa-caret-down'></i>")
			$(ddButton.closest('tr').find(".dd-header")[2]).html("(Unassigned)<i style='padding-left: 8px' class='fas fa-caret-down'></i>")
			
			// seed dd options
			var roleDiv = ddButton.closest('tr').find(".dd-content")[1]
			var dagDiv = ddButton.closest('tr').find(".dd-content")[2]
			if (project) {
				var roleContent = "<span>(Unassigned)</span>"
				for (var i=0; i<project.roles.length; i++) {
					roleContent += "\n<span>" + UserRoles.roles[project.roles[String(i)]] + "</span>"
				}
				$(roleDiv).html(roleContent)
				
				var dagContent = "<span>(Unassigned)</span>"
				for (var i=0; i<project.dags.length; i++) {
					dagContent += "\n<span>" + UserRoles.dags[project.dags[String(i)]] + "</span>"
				}
				$(dagDiv).html(dagContent)
			} else {
				$(roleDiv).html("<span>(Unassigned)</span>")
				$(dagDiv).html("<span>(Unassigned)</span>")
			}
		}
		
		UserRoles.setUserRoles()
	})
	
	// close non-clicked dropdowns
	window.onclick = function(event) {
		divs = $(".dd-content")
		for (i=0; i<divs.length; i++) {
			// console.log($(divs[i]).closest(".dd-container"))
			if (!$(divs[i]).closest(".dd-container").has(event.target).length > 0) {
				$(divs[i]).hide(100)
			}
		}
	}
	$("#rolesDiv button:eq(3)").hide(0)
	
	// if press enter, assume the user is trying to rename a role
	$(document).keypress(function(e) {
		if (e.which==13) {
			var newName = $(".roleButton.selected input").val()
			$(".roleButton.selected").html(newName)
			UserRoles.customRoles[String($(".roleButton.selected").attr('record_id'))].name = newName || "New Role"
			$("#rolesDiv button:eq(3)").show(100)
		}
	})
})