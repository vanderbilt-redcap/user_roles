
	// // was in iffe
	// $("#projectsDiv").on("click", "td:nth-child(2) button", function() {
		// // user clicked a project (in projects table) -- either select all in project, or if all selected, unselect all
		// // collect all role and dag buttons belonging to this project
		// items = []
		// $projectRow = $(this).parent().parent()
		// $projectRow.find("[roleid], [dagid]").each(function(key, val) {items.push(val)})
		// $nextRow = $projectRow.next()
		// $possibleProjectID = $nextRow.children(":first-child").html()
		// while ($possibleProjectID == "") {
			// $nextRow.find("[roleid], [dagid]").each(function(key, val) {items.push(val)})
			// $nextRow = $nextRow.next()
			// $possibleProjectID = $nextRow.children(":first-child").html()
		// }
		
		
		// allItemsSelected = true
		// $(items).each(function(i, val) {$(val).find('button').hasClass('selected') ? null : allItemsSelected = false})
		// allItemsSelected ? $(items).find('button').removeClass('selected') : $(items).find('button').addClass('selected')
	// })