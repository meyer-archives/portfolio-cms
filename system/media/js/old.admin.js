// The console killer
if(!window.console)console={log:function(){return;},error:function(){return;}};

$(function(){
	window.$$ = {
		d: $(document),
		w: $(window),
		b: $("body"),
		c: $(".container")
	};

	function checkHeartbeat(){
		$.ajax({
			url: '/admin/index.php/heartbeat',
			success: function(data){
				if( data == last_updated ){
					console.log("Page is up to date -- "+data);
				} else {
					if( $$.hb )
						clearInterval($$.hb);
					

					console.log("Page needs an update! -- "+data+" != "+last_updated);
				}
			}
		});
	}

	$$.hb = setInterval(function(){
		checkHeartbeat();
	}, 30*1000); // Every 30 seconds

	function doneResizing(){
		$$.b.removeClass("resizing");
	};

	$$.w.bind('resize', function() {
		$$.b.addClass("resizing");

	    if ($$.resizeTimer) clearTimeout($$.resizeTimer);
	    $$.resizeTimer = setTimeout(doneResizing, 100);
	});

	$('ul#project-list li').bind('project_eip', function(event){
		var $li = $(this);
		var pid = $li.attr("id").substr(8);
		$h2 = $li.find("h2");
		$.get('/admin/index.php/project/'+pid+'/edit.ajax', function(data){
			$form = $("<form><input value='"+data.title_src+"'><p><button>Save Changes</button> <a class='cancel' href='#'>Cancel</a></p></form>");
			$h2.hide().after($form);
			$form.submit(function(){
				$.ajax({
					type: 'POST',
					url: '/admin/index.php/project/3/save',
					data: ['project_title','yesss'],
					success: function(proj){
						alert( proj )
						$form.remove();
						$h2.html(proj.title).show();
					},
					dataType: "json"
				});

				return false;
			}).find("a.cancel").click(function(){
				$form.remove();
				$h2.show();
				return false;
			});
		}, "json");
		return false;
	});

	// Edit-in-place
	$("#project-list h2").addClass("edit-in-place");
	$(".project-header a.edit").click(function(){
		$(this).trigger('project_eip');
		return false;
	});
	$(".project-header a.cancel").click(function(){
		$(this).trigger('project_eip_cancel');
		return false;
	});

	// Scrolly thing
	$("#secondary ul li").localScroll({
//		target:'#content'},
//		offset:{top:0,left:0},
		onAfter: function(e){
			$e = $(e);
			$e.children(".project-container").addClass("active").delay(700).removeClass("active",300);
		},
		duration: 300
	});

	$(".item-add,#project-add").bind("showform",function(){
		var $this = $(this);
		if( $this.hasClass("hide-form") ){
			$this
				.removeClass("hide-form")
				.addClass("show-form")
				.find("input")
				.focus()
				.select();
		}
	}).bind("hideform",function(){
		$(this).removeClass("show-form").addClass("hide-form");
	}).find("a.cancel").click(function(){
		$(this).trigger("hideform");
		return false;
	});

	var $addForm = $("#project-add")
		.addClass("hide-form")
		.append(
			$("<a class='add-project' href='#'><span>Add new project</span></a>")
			.click(
				function(){
					$(this).trigger("showform");
					return false;
				}
			)
		);

	$(".item-add").each(function(){
		var $item = $(this);
		$item
			.addClass("hide-form")
			.append(
				$("<a class='add-item' href='#'><span>Add new item</span></a>")
				.click(
					function(){
						$(this).trigger("showform");
						return false;
					}
				)
			);
	})

//	jQuery.timeago.settings.strings.seconds = "moments";
	$('span#last-updated').timeago();
	$(".item-list").sortable({
		distance: 10,
		helper: 'original',
		placeholder: 'item-placeholder',
//		axis: "y",
//		handle: "span.drag-handle",
		start: checkHeartbeat,
		update: function(e,ui){
			$this = $(this);
			var query = "last_updated="+last_updated+"&project="+$this.parent().parent().attr("id")+"&"+$this.sortable( 'serialize' );
			$.ajax({
				type: 'POST',
				url: '/admin/index.php/items/reorder',
				data: query,
				success: function(data){
					last_updated = data.last_updated;
				},
				dataType: "json"
			});
		},
		connectWith: '.item-list'
	}).disableSelection();
	if( location.hash ) {
		if( ( location.hash.substr(0,9) == "#project-" ) && ($hash = $(location.hash)) ){
			$hash.children(".project-container").addClass("active").delay(700).removeClass("active",300);
		} else
		if( location.hash.substr(0,6) == "#edit/" ) {
			var item_id = location.hash.substr(6);
			if( $li = $( "li#"+item_id ) ){
//				eip( $li );
			}
		}
	}

});