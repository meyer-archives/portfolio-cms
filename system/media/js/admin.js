// The console killer
if(!window.console)console={log:function(){return;},error:function(){return;}};

$(document).ready(function() {
	window.$$ = {
		isAjaxing: false
	}

	$(document).ajaxStart(function(){
		$$.isAjaxing = true;
	}).ajaxComplete(function(){
		$$.isAjaxing = false;
	});

	$("#ab-project-list li a").click(function(){
		var pid = $(this).dataset("project-id");
		var $link = $(this);

		$link.addClass("ajaxing");

		$.ajax({
			url: '/api/items.json',
			data: {project_id:pid},
			complete: function(data){
				$link.removeClass("ajaxing");
				console.log(data.responseText);
			},
			dataType: "json"
		});

		return false;
	});
});