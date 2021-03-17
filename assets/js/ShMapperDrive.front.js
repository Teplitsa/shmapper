
jQuery(document).ready(function($)
{
	document.addEventListener("shm_point_click", function( event ) 
	{ 
		//console.log( event.detail.point ); 
		var pid = event.detail.point.post_id;
		jQuery("[pid]").removeClass("active");
		jQuery("[pid=" + pid + "]").addClass("active");
	});
});