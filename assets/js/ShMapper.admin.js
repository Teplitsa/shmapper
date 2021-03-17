
jQuery(document).ready(function($)
{
	//ajax
	$("[shm_notify_req]").on( 'click', function(evt) {
		var postid = $(evt.currentTarget).attr("shm_notify_req");	
		shm_send(['shm_notify_req',postid]);
	});

	$("[shm_nonotify_req]").on( 'click', function(evt) {
		var postid = $(evt.currentTarget).attr("shm_nonotify_req");	
		shm_send(['shm_nonotify_req',postid]);
	});

	$("[shm_trash_req]").on( 'click', function(evt) {
		var postid = $(evt.currentTarget).attr("shm_trash_req");	
		shm_send(['shm_trash_req',postid]);
	});

	$("span.trash > .submitdelete").on( 'click', function(evt) {
		if(window.location.href.indexOf("/wp-admin/edit.php?post_type=shm_map") < 1) return;
		evt.preventDefault();
		var href = $(evt.currentTarget).attr("href");
		var post_id = $(evt.currentTarget).parents("tr").attr("id");
		var id = ( post_id.substring(5) );
		shm_send( [ 'shm_delete_map', id, href] );
	});

	/* ADMIN FROM CODEX */
	/* https://codex.wordpress.org/Plugin_API/Action_Reference/bulk_edit_custom_box*/
	if( inlineEditPost != undefined )
	{
		// we create a copy of the WP inline edit post function
		var $wp_inline_edit = inlineEditPost.edit;
		// and then we overwrite the function with our own code
		inlineEditPost.edit = function( id ) 
		{
			// "call" the original WP edit function
			// we don't want to leave WordPress hanging
			$wp_inline_edit.apply( this, arguments );

			// now we take care of our business
			// get the post ID
			var $post_id = 0;
			if ( typeof( id ) == 'object' )
				$post_id = parseInt( this.getId( id ) );

			if ( $post_id > 0 ) 
			{
				// define the edit row
				var $edit_row = $( '#edit-' + $post_id );
				var $post_row = $( '#post-' + $post_id );

				// get the data
				var $is_legend = $( '.column-is_legend', $post_row ).html();
				var $is_legend = $( '.column-is_legend', $post_row ).html();
				// populate the data
				$( ':input[name="is_legend"]', $edit_row ).val( $is_legend );
			}
		};

		$( document ).on( 'click', '#bulk_edit', function(evt) 
		{
			// define the bulk edit row
			var $bulk_row = $( '#bulk-edit' );

			// get the selected post ids that are being edited
			var $post_ids = new Array();
			$bulk_row.find( '#bulk-titles' ).children().each( function() {
				$post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
			});

			// get the data
			var $owner_id = $bulk_row.find( '[name="owner_id[]"]:checked' ).map(function(idx, elem) {
				return $(elem).val();
			  }).get();
			
			var $smc_post_changer = {};
			$bulk_row.find( '.smc_post_changer' ).each((num, elem) => 
			{				
				var $val = $("[name='" + $(elem).attr("name") + "']:checked").val();
				$smc_post_changer[ $(elem).attr("name") ] = $val;
			});
			
			console.log($post_ids);
			// save the data
			$.ajax({
				url: ajaxurl, // this is a variable that WordPress has already defined for us
				type: 'POST',
				async: false,
				cache: false,
				success:function(f,g,h)
				{
					console.log("1:", f);
				},
				data: 
				{
					action: 'save_bulk_edit', 
					post_ids: $post_ids, // and these are the 2 parameters we're passing to our function
					owner_id: $owner_id,
					smc_post_changer: $smc_post_changer
					//inprint: $inprint
				}
			});
		});
	}
	
	$(".shm-types-radio").hide();
	
	$("[c='shm_add_before'], [c='shm_add_after']").on( 'click', function(evt) {
		evt.preventDefault();
		var $this = $(evt.currentTarget)
		var num		= $this.parents("[shm-num]").attr("shm-num");
		var type_id	= $this.parents("[shm-num]").attr("type_id");
		var post_id	= $this.parents("section[post_id]").attr("post_id");
		var command	= $this.attr("c");
		var pos = $this.offset();
		console.log( $this.parents("ul.shm-card").find("li:visible").size() );
		console.log( $this.parents("ul.shm-card > li:visible") );
		console.log( num );
		$(".shm-types-radio")
			.attr("row_id", num)
				.attr("post_id", post_id)
					.attr("command", command)
						.fadeIn("slow")
							.offset({top:pos.top - $(".shm-types-radio").height() - 35, left:pos.left-100});
	});

	$("[name='form_forms_form']").on( 'change', function(evt) {
		var $this 	= $(evt.currentTarget);
		var $rad	= $this.parents("[row_id]");
		var row_id 	= $rad.attr("row_id");
		var post_id = $rad.attr("post_id");
		var command = $rad.attr("command");
		var type_id = $this.val();
		var g = setTimeout(() =>
		{
			shm_send([ command, row_id, post_id, type_id ]);
			$this.prop('checked', false);
			$rad.hide();
			clearTimeout(g);
		}, 300);
	});
	
	$("[c='shm_delete_me']").on( 'click', function(evt) {
		if( confirm(__("Are you shure?")) )
		{
			var $this 	= $(evt.currentTarget);
			var num		= $this.parents("[shm-num]").attr("shm-num");
			$this.parents("[shm-num]").slideUp("slow" );
			var g = setTimeout(() =>
			{
				$this.parents("[shm-num]").detach();
				clearTimeout(g);
			}, 1200);
		}
	});
	
	if( $('[name="overlay_color"], [name="border_color"]').length > 0 )
	{
		$('[name="overlay_color"], [name="border_color"]').wpColorPicker({
			'defaultColor': '#d1d1d1'
		});
	}
	$('.shm-range').on('input', function(){
		$(this).attr('value', this.value);
	});

});

jQuery(function($){
	var $yandexMapsAPIKeyControl = $('#shm_settings_yandex_map_api_key_cont');

	function shmMapProviderChangeHandler(mapProvider) {
		if(mapProvider == 1) {
			$yandexMapsAPIKeyControl.show();
			if($yandexMapsAPIKeyControl.find('input').val()) {
				$yandexMapsAPIKeyControl.find('.shm-color-alert').hide();
			}
			else {
				$yandexMapsAPIKeyControl.find('.shm-color-alert').show();
			}
		}
		else {
			$yandexMapsAPIKeyControl.hide();
		}
	}

	$('.map_api_cont input[name="map_api"]').change(function(){
		shmMapProviderChangeHandler($(this).val());
	});

	shmMapProviderChangeHandler($('.map_api_cont input[name="map_api"]:checked').val());
});