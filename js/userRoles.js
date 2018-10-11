$(function() {
	$("[id=roleDetailsCard]").hide();
	$("[id=projectDetailsCard]").hide();
})

var UserRoles = {
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
	},
	
	deselectAll: function(which) {
		$("[name^='" + which + "'][checked]").each(function() {UserRoles.toggleButton(this)})
	},
	
	selectAll: function(which) {
		$("[name^='" + which + "']:not([checked])").each(function() {UserRoles.toggleButton(this)})
	}
}