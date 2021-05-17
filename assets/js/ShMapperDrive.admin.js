var google_matrix = [], google_data = [],
	generate_matrix_table = function(){};
function getGoogleIdenters()
{
	return [ "A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ"];
}

function getSingleGoogleIdenter($order)
{
	var $a = getGoogleIdenters();
	return $a[ parseInt($order) ];
}
function getSingleGoogleOrder($identer)
{
	var $a = getGoogleIdenters();
	
}

jQuery(document).ready(function($)
{
	generate_matrix_table = function()
	{
		$("#google_row").empty();
		var n = 0;
		for(var i in google_matrix)
		{
			if(!google_matrix[0][i]) 
			{
				//console.log( google_matrix[0][i] );
				continue;
			}
			if(!google_data[i])
				google_data[i] = {};
			var clone = $("#google_null").clone();
			//console.log( google_data[i] );
			google_data[i].include = typeof google_data[i].include != "undefined" ? parseInt(google_data[i].include)  : 1;
			google_data[i].title   = typeof google_data[i].title   != "undefined" ? google_data[i].title    : google_matrix[0][i].toString();
			google_data[i].meta    = typeof google_data[i].meta    != "undefined" ? google_data[i].meta     : getSingleGoogleIdenter( i );
			google_data[i].order   = typeof google_data[i].order   != "undefined" ? google_data[i].order    : i;
			
			clone.css("opacity", google_data[i].include ? 1 : 0.5);
			clone.attr("id", "google_matrix" + i);
			clone.attr("shmd_google_row", i);
			clone.find("[nid='google-include']>input[type='checkbox']").prop("checked", google_data[i].include );
			clone.find("[nid='google-id']").text( getSingleGoogleIdenter( i ) );
			clone.find("[nid='google-meta'] input" ).val( google_data[i].meta );
			clone.find("[nid='google-title'] input").val( google_data[i].title );
			clone.find("[nid='google-order'] input").val( google_data[i].order );
			$("#google_row").append( clone );
			n++;
		}
		//console.log(google_data);
	}
	var querry = {
		"google-include" 	: "include",
		"google-meta" 		: "meta",
		"google-title" 		: "title",
		"google-order" 		: "order"
	};
	$(".shmd_row_check").on( 'change', function(evt)
	{
		var value 			= $(this).is(":checked") ? 1 : 0;
		var name 			= querry[$(this).parents("[nid]").attr("nid")];	
		var shmd_google_row	= $(this).parents("[shmd_google_row]").attr("shmd_google_row");	
		$(this).parents("[shmd_google_row]").css("opacity", value ? 1 : 0.5);
		shm_send([ 'google_matrix_data', name, value, shmd_google_row ]);
	})
	$(".shmd_row_input").on( 'change', function(evt)
	{ 
		var value 			= $(this).val();
		var name 			= querry[$(this).parents("[nid]").attr("nid")];		
		var shmd_google_row	= $(this).parents("[shmd_google_row]").attr("shmd_google_row");
		shm_send([ 'google_matrix_data', name, value, shmd_google_row ]);
	})
	$("[name='google_table_id']").change(function(evt)
	{
		var val = $(evt.currentTarget).val();
		if(val != "")
		{
			$("#shmd_settings_wizzard").fadeIn().removeClass('_hidden');
		}
		else
		{
			$("#shmd_settings_wizzard").fadeOut();
			$("#shm_google_params").slideUp();
		}
	});
	$(" #shm-google-reload, [name='google_table_id']").click(function(evt)
	{
		shm_send([ 'load_google_table', $("[name='google_table_id']").val() ]);
	});
	$("#shmd_settings_open").click(function(evt)
	{
		shm_send([ 'load_google_table', $("[name='google_table_id']").val() ]);
		$("#shm_google_params").slideToggle();
	});
	$("#shmd_google_preview").click(function(evt)
	{
		$('.shmd-loader').removeClass('_hidden');
		shm_send([ 'shmd_google_preview' ]);
	});
	$("#shmd_google_update").click(function(evt)
	{
		$('.shmd-loader').removeClass('_hidden');
		shm_send([ 'shmd_google_update' ]);
	});
	
	$(".shm_options").change(function(evt)
	{
		var name	= $(this).attr("name");
		var val 	= $(this).val();
		shm_send([ 'shm_options', name, val ]);	
	});
	
	//ajax
	document.addEventListener("_shm_send_", function( event ) 
	{ 
		//console.log( event.detail ); 
		switch(event.detail[0])
		{
			case "load_google_table":
				google_matrix 	= event.detail[1].matrix;
				google_data 	= event.detail[1].data;
				//console.log( google_matrix );
				//console.log( google_data );
				generate_matrix_table();
				break;
			case "shmd_google_preview":
				shm_add_modal( { title:"Preview", content: event.detail[1].matrix } );
				$('.shmd-loader').addClass('_hidden');
				break;
			case "shmd_google_update":
				$('.shmd-loader').addClass('_hidden');
				break;
		}
	});
	document.addEventListener("shm_point_click", function( event ) 
	{ 
		//console.log( event.detail ); 
	
	});
});