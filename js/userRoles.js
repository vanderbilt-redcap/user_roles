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
						<button onclick="$(this).siblings('[class*=dd-content').toggle(100)" class="dd-header project-dd btn">Project <i style="padding-left: 8px" class="fas fa-caret-down"></i></button>
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
	
	// // Project divs section for add/remove buttons
	// build projectList template string
	for (pid in UserRoles.projects){
		UserRoles.templates.projectList += "<span>("+ pid + ") " + UserRoles.projects[pid].name + "</span>\n"
	}
	
	UserRoles.addProjectRow = function(){
		let projectRow = $(UserRoles.templates.projectRow)
		// seed new row first dropdown with list of projects
		projectRow.find(".dd-content").first().append(UserRoles.templates.projectList)
		$("#projectsDiv").find("tbody").append(projectRow)
	}
	
	UserRoles.removeProjectRow = function(){
		// find selected row if exists
		selectedRow = $("#projectsDiv tbody tr.selected")
		selectedRow.length > 0 ? selectedRow.first().remove() : $("#projectsDiv tbody tr").last().remove()
	}
	
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

var UserRoles = {
	
}