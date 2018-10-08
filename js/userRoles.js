$(function() {
	// $('h3').text(decodeURIComponent(document.cookie));
})

var UserRoles = {
	getDashboardItems: function(pageIndex = 1){
		// console.log("my ajax url: " + decodeURIComponent(document.cookie.slice(8)) + "\ajax.php");
		// console.log("pageIndex: " + pageIndex);
		$.ajax({
			data: {
				"page": "ajax",
				"function": "getDashboardItems",
				"pageIndex": pageIndex
			},
			success: function(data){
				console.log(data);
			}
		})
	}
}