// The console killer
if(!window.console)console={log:function(){return;},error:function(){return;}};

$(document).ready(function() {
	window.$$ = {
		d: $(document),
		w: $(window),
		b: $("body"),
		c: $(".container"),
		projectList: $("#project-list ul"),
		itemList: $("#item-list ul"),
		popup: $("#modal-popup"),
		overlay: $("#transparent-overlay"),
		itemData: [],
		projectData: {},
		pidArray: [],
		sorting: false
	};

	// jQuery Uniform
	$("select, input:checkbox, input:radio").uniform();

	$$.overlay.hide();

	// Hot Buttons
	$("input:button,button").each(function(){
		$(this).addClass("button").wrapInner("<span><span></span></span>");
	});

	$("ul#item-list").sortable();

	// Idle timer
	$.idleTimer(20000); // 20 seconds

	$(document).bind("idle.idleTimer", function(){
		$$.overlay.fadeIn(200);
		console.log( "Window is inactive" );
	});

	$(document).bind("active.idleTimer", function(){
		console.log( "Checking to see if the page is up to date..." );
		$.ajax({
			url: '/admin/index.php/heartbeat.json',
			success: function(o){
				if( o.data.last_updated == last_updated ){
					$$.overlay.fadeOut(200);
					console.log(Date.now() + " - Page is up to date -- "+o.data.last_updated);
				} else {
					$.idleTimer('destroy');
					console.log(Date.now() + " - Page needs an update! -- "+o.data.last_updated+" != "+last_updated );
				}
			},
			dataType: "json"
		});
	});

	$$.popup.hide().find("p").html("Loading&hellip;");

	function projectAdd(pid,project){
		var projectHTML = $("<li data-project='"+pid+"' data-item-count='"+project.item_count+"'>"+project.title+"</li>");
		projectHTML.click(function(){
			if( $$.currentProject != pid ) {
				$$.currentProject = pid;
				console.log( "Show items from project "+pid );
			}
		});
		return projectHTML.html();
	}
});