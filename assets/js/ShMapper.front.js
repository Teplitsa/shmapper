
var place_new_mark = function(){}, addAdress = function(){}, $this,  new_mark_coords, shm_address, shm_placemark, map, shm_paramet;
jQuery(document).ready(function($)
{	
	//destroy all orphan map forms
	$("form[form_id]").each(function(num, elem)
	{
		if($("#"+ $(elem).attr("form_id")).size() < 1)
			$(elem).empty().append( __("Error: no map") );
	})
	
	//send new request
	$('form.shm-form-request').on( 'submit', function(evt) {
		evt.preventDefault();
		
		var $this = $(evt.currentTarget);
		$this.find("[required]").each(function(num, elem)
		{
			$(elem).removeClass("shm-alert");
			if( $(elem).val() == "" )
			{
				$(elem).addClass("shm-alert");
			}
		}); 
		if( $this.find(".shm-alert").length )	return;
		var data = $this.serializeArray();
		var d = new FormData();
		data.forEach( function(evt)
		{
			if( evt.name=="g-recaptcha-response" ) d.append("cap", evt.value);
		});
		d.append("id", $this.attr("map_id"));
		d.append("form_id", $this.attr("form_id"));
		d.append("shm_point_type", $this.find( "[name='shm_point_type']" ).val());
		d.append("action", "shm_set_req");
		d.append("shm_point_lat", 	$this.find( "[name='shm_point_lat']" ).val());
		d.append("shm_point_lon", 	$this.find( "[name='shm_point_lon']" ).val());
		d.append("shm_point_loc", 	$this.find( "[name='shm_point_loc']" ).val());
		d.append("shm_form_name", 	$this.find( "[name='shm_form_name']" ).val());
		d.append("shm_form_email", 	$this.find( "[name='shm_form_email']" ).val());
		d.append("shm_form_phone", 	$this.find( "[name='shm_form_phone']" ).val());
		d.append("elem", $this.find( "[name='elem[]']" ).map( function(num,el){
			var val = el.value;
			if(val) {
				val = String(val).replace(/,/g, "{{shmapper_comma}}");
			} 
			return val;
		} ).get() );
		console.log( d );
		$.each( shm_img, function( key, value )
		{			
			d.append( key, value );
		}); 
		
		// AJAX запрос
		$.ajax({
			url         : shm_set_req.url,
			type        : 'POST',
			cache       : false,
			dataType    : 'json',
			processData : false,
			contentType : false, 
			success     : function( response, status, jqXHR )
			{
				console.log(response);
				add_message(response.msg);
				if(response.grecaptcha == 1)
					grecaptcha.reset();
				//clear form
				var $form  = $("[form_id='" + response.data.form_id + "']");
				$form.find("[name='shm_point_type']").val("");
				$form.find("[name='shm_point_lat']").val("");
				$form.find("[name='shm_point_lon']").val("");
				$form.find("[name='shm_point_loc']").val("").slideUp("slow");
				$form.find(".shm-form-element input:not([type='submit']), .shm-form-element textarea")
					.each(function(num,elem) { $(elem).val("")});
				$form.find(".shm-form-element .shm-form-file img").attr("src","");
				var dat = [
					{},
					shm_maps["" + response.data.form_id ],
					response.data.id
				];
				var clear_form = new CustomEvent("clear_form", {bubbles : true, cancelable : true, detail : dat});
				document.documentElement.dispatchEvent(clear_form);
				
				if(response.reload)
					window.location.reload(window.location.href);
			},
			data: d,
			error: function( jqXHR, status, errorThrown )
			{
				add_message( "Error" ); 
			}

		});
	});
});
function add_message(msg)
{
	if(msg)
	{
		jQuery(".msg").detach();
		clearTimeout(setmsg);
		jQuery("<div class='msg'>" + msg + "</div>").appendTo("body").hide().fadeIn("slow");
		setmsg = setTimeout( function() {
			jQuery(".msg").fadeOut( 700 );
		}, 6000);
	}
}