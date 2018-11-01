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
						<button onclick="$(this).siblings('[class*=dd-content').toggle(100)" class="dd-header project-dd btn">(Unassigned)<i style="padding-left: 8px" class="fas fa-caret-down"></i></button>
						<div class="dd-content">
						</div>
					</div>
				</td>
				<td>
					<div class="dd-container">
						<button onclick="$(this).siblings('[class*=dd-content').toggle(100)" class="dd-header btn">(Unassigned)<i style="padding-left: 8px" class="fas fa-caret-down"></i></button>
						<div class="dd-content">
							<span>(Unassigned)</span>
						</div>
					</div>
				</td>
				<td>
					<div class="dd-container">
						<button onclick="$(this).siblings('[class*=dd-content').toggle(100)" class="dd-header btn">(Unassigned)<i style="padding-left: 8px" class="fas fa-caret-down"></i></button>
						<div class="dd-content">
							<span>(Unassigned)</span>
						</div>
					</div>
				</td>
			</tr>`,
		projectList:""
	}
	
	UserRoles.seedRoleDagButtons = function(projectDropdown){
		pid = projectDropdown.html().split('(')[1].split(')')[0]
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
		let projectRow = $(UserRoles.templates.projectRow)
		// seed new row first dropdown with list of projects
		projectRow.find(".dd-content").first().append(UserRoles.templates.projectList)
		$("#projectsDiv").find("tbody").append(projectRow)
		
		// if pid/role/dag supplied set those
		if (pid) {
			// set project dropdown text and seed role/dag button options
			$("#projectsDiv tr:last .dd-content:eq(0) span").each(function(i, element){
				if ($(element).text().indexOf("("+pid+")") != -1) {
					ddButton = $("#projectsDiv tr:last .dd-header:eq(0)")
					ddButton.html($(element).text()+'<i style="padding-left: 8px" class="fas fa-caret-down">')
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
					ddButton.html(role_name+'<i style="padding-left: 8px" class="fas fa-caret-down">')
				}
			})
		}
		if (group_id) {
			group_name = UserRoles.dags[group_id]
			$("#projectsDiv tr:last .dd-content:eq(2) span").each(function(i, element){
				if (i==0) return
				if ($(element).text() == group_name) {
					ddButton = $("#projectsDiv tr:last .dd-header:eq(2)")
					ddButton.html(group_name+'<i style="padding-left: 8px" class="fas fa-caret-down">')
				}
			})
		}
	}
	
	UserRoles.removeProjectRow = function(){
		// find selected row if exists
		selectedRow = $("#projectsDiv tbody tr.selected")
		selectedRow.length > 0 ? selectedRow.first().remove() : $("#projectsDiv tbody tr").last().remove()
	}
	
	// // Project divs section for add/remove buttons
	// build projectList template string
	for (pid in UserRoles.projects){
		UserRoles.templates.projectList += "<span>("+ pid + ") " + UserRoles.projects[pid].name + "</span>\n"
	}
	
	// add roles
	var falseCheckbox = "<input type=\"checkbox\">"
	var trueCheckbox = "<input type=\"checkbox\" checked>"
	for (var role in UserRoles.customRoles) {
		let tableRow = `
		<tr>
			<td><button type=\"button\" class=\"btn roleButton\">${role}</button></td>
			<td>${UserRoles.customRoles[role].active=="true" ? trueCheckbox : falseCheckbox}</td>
			<td>${UserRoles.customRoles[role].external=="true" ? trueCheckbox : falseCheckbox}</td>
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
		$(".roleButton").removeClass("selected")
		$(this).addClass('selected')
		var role = UserRoles.customRoles[$(this).html()]
		
		console.log(role)
		
		// add/remove project access rows as necessary to match existing role access
		$("#projectsDiv tbody").html("")
		var roleProjects = JSON.parse(role.projects)
		Object.keys(roleProjects).forEach(function(key){
			if (UserRoles.projects[key]) {
				if (UserRoles.projects[key] == null) {
					UserRoles.addProjectRow(key)
				} else {
					var role_id = roleProjects[key].role ? null : roleProjects[key].role
					var dag_id = roleProjects[key].dag ? null : roleProjects[key].dag
					UserRoles.addProjectRow(key, role_id, dag_id)
				}
			}
		})
		
		// toggle dashboard/report access items to match existing
		
		// console.log(role)
	})
	
	// make report and dashboard items selectable
	toggle = function() {$(this).hasClass('selected') ? $(this).removeClass('selected') : $(this).addClass('selected')}
	$("#dashboardsDiv").on("click", "li button", toggle)
	$("#reportsDiv").on("click", "li button", toggle)
	$("#projectsDiv").on("click", "tbody tr", function(){
		// untoggle all project table rows except newly selected
		$("#projectsDiv tbody tr").removeClass('selected')
		$(this).addClass('selected')
	})
	
	// // dropdown logic:
	// handle dropdown content clicking
	$("body").on("click", ".dd-content *", function(){
		// put project "(pid) title" text in dropdown button
		ddButton = $(this).parent().siblings(".dd-header")
		ddButton.html($(this).html() + "<i style=\"padding-left: 8px\" class=\"fas fa-caret-down\"></i>")
		$(this).parent().toggle(100)
		
		// if project dropdown got altered, seed options for role and dag dropdowns
		if (ddButton.is(".project-dd")) {
			pid = ddButton.html().split('(')[1].split(')')[0]
			project = UserRoles.projects[String(pid)]
			
			// switching projects so unassign current role/dag
			$(ddButton.closest('tr').find(".dd-header")[1]).html("(Unassigned)<i style='padding-left: 8px' class='fas fa-caret-down'></i>")
			$(ddButton.closest('tr').find(".dd-header")[2]).html("(Unassigned)<i style='padding-left: 8px' class='fas fa-caret-down'></i>")
			
			// seed dd options
			roleDiv = ddButton.closest('tr').find(".dd-content")[1]
			dagDiv = ddButton.closest('tr').find(".dd-content")[2]
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
})