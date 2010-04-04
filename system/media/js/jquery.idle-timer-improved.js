(function($){
$.idleTimer = function f(newTimeout){

	var idle	= false,		//indicates if the user is idle
		enabled = true,		   //indicates if the idle timer is enabled
		timeout = 30000,		//the amount of time (ms) before the user is considered idle
		events	= 'mousemove keydown DOMMouseScroll mousewheel mousedown', // activity is one of these events

	toggleIdleState = function(){
		idle = !idle;
		f.olddate = +new Date;

		$(document).trigger(
			$.data(document,'idleTimer', idle ? "idle" : "active" )  + '.idleTimer'
		);
	},

	stop = function(){
		enabled = false;
		clearTimeout($.idleTimer.tId);
		$(document).unbind('.idleTimer');
	},

	handleUserEvent = function(){
		clearTimeout($.idleTimer.tId);

		if (enabled){
			if (idle)
				toggleIdleState();
			$.idleTimer.tId = setTimeout(toggleIdleState, timeout);
		}
	 };

	f.olddate = f.olddate || +new Date;

	if( typeof newTimeout == "number" ){
		timeout = newTimeout;
	} else if( newTimeout === 'destroy' ){
		stop();
		return this;
	} else if (newTimeout === 'getElapsedTime'){
		return (+new Date) - f.olddate;
	}

	$(document).bind(
		$.trim(
			(events+' ')
			.split(' ')
			.join('.idleTimer ')
		),
		handleUserEvent
	);

	$.idleTimer.tId = setTimeout(toggleIdleState, timeout);

	$.data(document,'idleTimer',"active");

};

})(jQuery);