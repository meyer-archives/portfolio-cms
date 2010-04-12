// The console killer
if(!window.console)console={log:function(){return;},error:function(){return;}};

$(document).ready(function() {
	window.$$ = {
		itemList: $("#item-list"),
		projectList: $("#project-list"),
		ab: $("#container"),
		isAjaxing: false,
		showingItems: false,
		d: $(document),
		w: $(window),
		b: $("body"),
		l: $("ul.listview")
	}

	$(document).ajaxStart(function(){
		$$.isAjaxing = true;
	}).ajaxComplete(function(){
		$$.isAjaxing = false;
	});

	// Same regex as the PHP version
	function sluginate( text ){
	    return text.toLowerCase().replace(/([^\w]|^the\s)+/g,'-').replace(/(^-|-$)/g,"");
	}

	$("#project-list ul.listview").sortable({
		axis: "y",
		handle: ".sort-handle span",
		placeholder: "li-item item-placeholder",
		helper: 'clone'
	}).disableSelection();

	$("#reorder-items,#reorder-projects").click(function(){
		var $a = $(this);
		if( $a.hasClass("save-items") ){
			$a.removeClass("save-items").find("span").text("reorder");
		}else{
			$a.addClass("save-items").find("span").text("save items");
		}
	});

	function resizeListview(){
		$$.b.removeClass("resizing");

		var dh = $$.d.height() - 56;

		$$.l.each(function(){
			var $g = $(this);
			if( $g.height() > dh ){
				$g.css("height",dh);
			} else {
				$g.css("height","100%");
			}
		});
	};
	resizeListview();

	$$.w.bind('resize', function() {
	    if($$.resizeTimer){
			clearTimeout($$.resizeTimer);
		}

		$$.b.addClass("resizing");
		$$.l.css("height","100%");

		$$.resizeTimer = setTimeout(resizeListview, 100);
	});

	$("#show-project-list").click(function(){
		$$.ab.animate({left:0},150);
		$$.showingItems = false;
	});

	$("#project-list ul.listview li.project").bind("viewProject",function(){
		var pid = $(this).attr("data-pid");
		var $link = $(this);

		if( pid > 0 ) {

			$link.addClass("ajaxing");
			console.log( "Fetching all items from project "+pid+"..." );

			$.getJSON(
				'/api/items.json',
				{'project_id':pid},
				function(e, textStatus){
					$link.removeClass("ajaxing");

					$("#item-list h1").html(e.data.project.title);

					$.each( e.data.items, function(itemID){
						console.log(e.data.items);
						console.log("---");
					} )

					if( e.data.items.length > 0 ){
					} else {
						$("#item-list ul").html("<li class='list-item empty'>"+e.data.project.title+" ("+pid+") is empty</li>");
					}

					$$.ab.animate({left:-250},150);
					$$.showingItems = true;

					console.log(e.status+" - "+e.status_msg);
//					console.log(e);
				}
			);

		} else {
			console.log( "Unpublished" );
		}

		return false;
	});

	$("#project-list .view-project a").click(function(){
		$(this).trigger("viewProject");
	});

//	$("#wrapper").block({
//		"message": "<a href='#'>Reload Frame</a>"
//	});
});