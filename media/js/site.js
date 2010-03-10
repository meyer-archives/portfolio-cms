// The console killer
if(!window.console)console={log:function(){return;},error:function(){return;}};

$(document).ready(function() {
	$("#uploadify").uploadify({
		uploader : '/media/uploadify/uploadify-cs4.swf?'+Date.now(),
		script : '/api/item/add.json',
		cancelImg : '/media/uploadify/cancel.png',
		auto : true,
		multi : false,
		fileExt : "*.jpg,*.jpeg",
		fileDesc : "File must be a JPG image",
		fileDataName: "image_original",
//		onProgress : function(a) {console.log( a );},
		onComplete: function(event, queueID, fileObj, response, data) {
			var r = jQuery.parseJSON(response);

			// Show status message
			$$.popup.fadeIn(300,function(){
				$(this).delay(2000).fadeOut(400);
			}).find("p").html(r.status_msg);

			// Log status message
			console.log( r.status + ": " + r.status_msg );
		}
	});

});