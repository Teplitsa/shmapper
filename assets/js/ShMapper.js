
var media_uploader = null, setmsg, $pm_pars={}, shm_add_modal=function(){}, shm_close_modal=function(){}, create_point=function(){}, shm_delete_map_hand = function(){}, shm_map_add_point = function(data){}, shm_img=[];
var __ = function(text)
{
	return voc[text] ? voc[text] : text;
}
function open_media_uploader_image()
{
    media_uploader = wp.media({
        frame:    "post", 
        state:    "insert", 
        multiple: false
    });
    media_uploader.on("insert", function()
	{
        var json = media_uploader.state().get("selection").first().toJSON();

        var image_url = json.url;
        var image_caption = json.caption;
        var image_title = json.title;
		on_insert_media(json);
    });
    media_uploader.open();
}

function shmapperIsMobileView() {
	var isMobile = false;
	
	if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
	 || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) { 
		isMobile = true;
	}
	
	return isMobile;
}


jQuery(document).ready(function($)
{	
	// ajax
	$( '[name="shm_wnext"]' ).on( 'click', function() {
		shm_send(['shm_wnext']);
	});
	$( '.shm_doubled[post_id]' ).on( 'click' ,function(evt) {
		evt.preventDefault();
		shm_send( ['shm_doubled', $(evt.currentTarget).attr("post_id")] );
	} );
	$( '[name="shm_wclose"]' ).on( 'click', function(evt) { 
		shm_send( ['shm_wclose'] );
	});
	$( '#shm_settings_wizzard' ).on( 'click', function(evt) {
		shm_send( ['shm_wrestart'] );
	});
	$( '.shm-change-input-change' ).on( 'click', function(evt) {
		evt.preventDefault();
		var command = $(evt.currentTarget).attr("c");
		var num		= $(evt.currentTarget).parents("[shm-num]").attr("shm-num");
		var post_id	= $(evt.currentTarget).parents("section[post_id]").attr("post_id");
		shm_send([ command, num, post_id ]);
	});
	$( '.admin_voc' ).on( 'change', function(evt) {
		$("#shm_vocabulary_cont").css("opacity", 0.7);
		shm_send(["shm_voc", $(evt.currentTarget).attr("name"), $(evt.currentTarget).val()]);
	});
	$("[name=shm_default_longitude]").on( 'change', function(evt) {
		var shmDefaultLongitude = $('[name=shm_default_longitude]').val();
		var shmDefaultLatitude  = $('[name=shm_default_latitude]').val();
		shm_send([ 'shm_default_coordinates', [ shmDefaultLongitude, shmDefaultLatitude ] ]);
		console.log(shmDefaultLongitude);
		console.log(shmDefaultLatitude) ;
	});
	$("[name=shm_default_zoom]").on( 'change', function(evt) {
		setTimeout(function(){
			shm_send(["shm_default_zoom", $(evt.currentTarget).val() ]);
		},1000);
	});
	$( '[name="map_api"]' ).on( 'click', function(evt) {
		$(".map_api_cont").css("opacity", 0.7);
		shm_send(["map_api", $(evt.currentTarget).val()]);
	});
	$( '[name="shm_yandex_maps_api_key"]' ).on( 'change', function(evt) {
		$(".map_api_cont").css("opacity", 0.7);
		shm_send(["shm_yandex_maps_api_key", $(evt.currentTarget).val() ]);
	});
	$( '#shm_map_is_crowdsourced' ).on( 'click', function(evt) {
		$("#shm_map_is_crowdsourced_cont").css("opacity", 0.7);
		shm_send(["shm_map_is_crowdsourced", $(evt.currentTarget).is(":checked") ? 1 : 0]);
	});
	$( '#shm_map_marker_premoderation' ).on( 'click', function(evt) {
		$("#shm_map_is_crowdsourced_cont").css("opacity", 0.7);
		shm_send(["shm_map_marker_premoderation", $(evt.currentTarget).is(":checked") ? 1 : 0]);
	});
	$( '#shm_reload' ).on( 'click', function(evt) {
		$("#shm_map_is_crowdsourced_cont").css("opacity", 0.7);
		shm_send(["shm_reload", $(evt.currentTarget).is(":checked") ? 1 : 0]);
	});
	$( '#shm_settings_captcha' ).on( 'click', function(evt) {
		$("#shm_settings_captcha_cont").css("opacity", 0.7);
		shm_send(["shm_settings_captcha", $(evt.currentTarget).is(":checked") ? 1 : 0]);
	});
	$( '[name=shm_captcha_siteKey]' ).on( 'change', function(evt) {
		$("#shm_settings_captcha_cont").css("opacity", 0.7);
		shm_send(["shm_captcha_siteKey", $(evt.currentTarget).val() ]);	
	});
	$( '[name=shm_captcha_secretKey]' ).on( 'change', function(evt) {
		$("#shm_settings_captcha_cont").css("opacity", 0.7);
		shm_send(["shm_captcha_secretKey", $(evt.currentTarget).val() ]);
	});
	
	$( 'a.shm-csv-icon[map_id]' ).on( 'click', function(evt) {
		evt.preventDefault();
		shm_send(['shm_csv', $(evt.currentTarget).attr("map_id")]);
	});
	create_point = function()
	{
		$(".shm-alert").removeClass("shm-alert");
		var s = ["shm-new-point-title", "shm-new-point-content"];
		var alerting = [];
		s.forEach(function(elem)
		{
			if($("[name='" + elem + "']").val() == "")
			{
				alerting.push(elem);
			}
		});
		if(alerting.length)
		{
			alerting.forEach(function(elem) {$("[name='" + elem + "']").addClass("shm-alert") });
			return;
		}
		shm_send(['shm_create_map_point', {
			map_id: $("[name='shm_map_id']").val(),
			latitude: $("[name='shm_x']").val(), 
			longitude: $("[name='shm_y']").val(), 
			post_title: $("[name='shm-new-point-title']").val(), 
			post_content: $("[name='shm-new-point-content']").val(), 
			type: $("[name='shm-new-point-type']").val(),
			location: $("[name='shm-new-point-location']").val(),
		}]);
	}
	shm_delete_map_hand = function(id)
	{
		var action = $('[name=shm_esc_points]:checked').val();
		var anover = $("#shm_esc_points_id").val();
		var id = $("[shm_delete_map_id]").attr("shm_delete_map_id");
		shm_send(['shm_delete_map_hndl', {
			action : action,
			anover: anover,
			id: id,
		}])
	}
	// map filter
	$(".shm-map-panel[for] .point_type_swicher>input[type='checkbox']").on( 'change', function(evt) {
		var $this = $(evt.currentTarget);
		var uniq = $this.parents("[for]").attr("for");
		var map = shm_maps[uniq];
		var term_id = $this.attr("term_id");
		
		var dat = {
			uniq 	: uniq,
			term_id	: term_id,
			$this	: $this,
			map		: map
		}
		var customEvent = new CustomEvent("shm_filter", {bubbles : true, cancelable : true, detail : dat})
		document.documentElement.dispatchEvent(customEvent);	
	});
	
	//admin map editor
	shm_map_add_point = function(elem)
	{
		if(map_type == 1)
		{
			var paramet;
			if( elem.icon )
			{
				paramet = {
					balloonMaxWidth: 250,
					hideIconOnBalloonOpen: false,
					iconColor:elem.color,
					iconLayout: 'default#image',
					iconImageHref: elem.icon,
					iconImageSize:[elem.height, elem.height], //[50,50], 
					iconImageOffset: [-elem.height/2, -elem.height/2],
					term_id:elem.term_id,
					type:'point'
				};
			}
			else
			{
				paramet = {
					balloonMaxWidth: 250,
					hideIconOnBalloonOpen: false,
					iconColor: elem.color ? elem.color : '#FF0000',
					preset: 'islands#dotIcon',
					term_id:elem.term_id,
					type:'point',
				}
			}
			var myPlacemark = new ymaps.Placemark(
				[elem.latitude, elem.longitude],
				{
					geometry: 
					{
						type: 'Point', 
						coordinates: [elem.latitude, elem.longitude]
					},
					balloonContentHeader: elem.post_title,
					balloonContentBody: elem.post_content,
					hintContent: elem.post_title
				}, paramet
			);
			shm_maps[elem.mapid].geoObjects.add(myPlacemark);
		}
		else
		{
			var icons=[];
			if( !icons[elem.term_id] && elem.icon )
			{
				icons[elem.term_id] = L.icon({
					iconUrl: elem.icon,
					shadowUrl: '',
					iconSize:     [elem.height, elem.height], // size of the icon
					shadowSize:   [elem.height, elem.height], // size of the shadow
					iconAnchor:   [elem.height/2, elem.height/2], // point of the icon which will correspond to marker's location
					shadowAnchor: [0, elem.height],  // the same for the shadow
					popupAnchor:  [-elem.height/4, -elem.height/2] // point from which the popup should open relative to the iconAnchor
				});
				var shoptions = elem.icon != '' ? {icon: icons[elem.term_id]} : {};		
				var marker = L.marker([ elem.latitude, elem.longitude ], shoptions )
					.addTo(shm_maps[elem.mapid])
						.bindPopup('<div class=\"shml-title\">' + elem.post_title +'</div><div class=\"shml-body\">' + elem.post_content + '</div>');
			
			}
			else
			{
				var clr = elem.color ? elem.color : '#FF0000'
				var style = document.createElement('style');
				style.type = 'text/css';
				style.innerHTML = '.__class'+ elem.post_id + ' { color:' + clr + '; }';
				document.getElementsByTagName('head')[0].appendChild(style);
				var classes = 'dashicons dashicons-location shm-size-40 __class'+ elem.post_id;
				var myIcon = L.divIcon({className: classes, iconSize:L.point(40, 40) });//
				L.marker([ elem.latitude, elem.longitude ], {icon: myIcon})
					.addTo(shm_maps[elem.mapid])
						.bindPopup('<div class=\"shml-title\">' + elem.post_title +'</div><div class=\"shml-body\">' + elem.post_content + '</div>');
			}
		}
	}
	
	//point_type_swicher
	$(".point_type_swicher .ganre_checkbox").on( 'click', function(evt) {
		var types = [];
		var $e = $(evt.currentTarget).parents(".point_type_swicher").find(".ganre_checkbox");
		$e.each(function(num, elem)
		{
			if($(elem).is(":checked"))
				types.push($(elem).attr("term_id"));
		});
		$(evt.currentTarget).parents(".point_type_swicher").find("[point]").val(types.join(","));
	});
	
	
	//admin form element chooser
	$("#form_editor select[selector]").on( 'change', function(evt) {
		var $this = $(evt.currentTarget);
		var flds = $this.find("option:selected").attr("data-fields").split(",");
		var $num =  $this.parents(".shm-row").attr("shm-num");	
		$this.parents(".shm-row [shm-num="+$num + "]").find(".shm-t").hide();
		flds.forEach(function(elem, num)
		{
			$this.parents(".shm-row [shm-num="+$num + "]").find(".shm--" + elem).show();
		});
	});
	$("[name='tax_input[shm_point_type][]']").on( 'change', function(evt) {
		var ch = $(evt.currentTarget).is(":checked");
		$("[name='tax_input[shm_point_type][]']:checked").each( function(num, elem){$(elem).prop('checked', false)} );
		$(evt.currentTarget).attr("checked", ch);
	});
	$("[name=shm_esc_points]").on( 'change', function(evt) {
		if($(evt.currentTarget).val() == 3)
		{
			$("#shm_esc_points_id").show();
		}
		else
		{
			$("#shm_esc_points_id").hide();
		}
	});
	//interface
	$(".shm-close-btn").on( 'click', function(evt) {
		$(evt.currentTarget).parents(".shm-win").hide();
	});
	
	//
	//media loader
	var prefix;
	var cur_upload_id = 1;
	$( ".my_image_upload" ).on( 'click', function() {
		var cur_upload_id = $(this).attr("image_id");
		prefix = $(this).attr("prefix");// "pic_example";
		var downloadingImage = new Image();
		downloadingImage.cur_upload_id = cur_upload_id;
		on_insert_media = function(json)
		{
			//alert(json.id);
			$( "#" + prefix +"_media_id" + cur_upload_id ).val(json.id);
			$( "#" + prefix +"_media_id" + cur_upload_id ).attr("value", json.id);
			downloadingImage.onload = function()
			{								
				$("#" + prefix + this.cur_upload_id).empty().append("<img src=\'"+this.src+"\' width='auto' height='68'>");
				$("#" + prefix + this.cur_upload_id).css({"height":"68px", "width":"68px"});
				
			};
			downloadingImage.src = json.url;		
			//
		}
		open_media_uploader_image();						
	});
	$( ".my_image_upload" ).each(function(num,elem)
	{
		prefix = $(this).attr("prefix");// "pic_example";
		$( elem ).height( $("#" + prefix  + $(elem).attr("image_id")).height() + 0);
	})
	$(".my_image_delete").on( 'click', function(evt) {
		var $prefix = $(evt.currentTarget).attr("prefix");
		var $default = $(evt.currentTarget).attr("default");
		var $targ = $("#" + $prefix + " > img");
		var $input = $("#" + $prefix + "_media_id");
		$targ.attr("src", $default);
		$input.val("");
	});
	
	
	// input file
	$(".shm-form-file > input[type='file']").each(function(num, elem)
	{
		$(elem).on('change', function(evt) {
			var file	= evt.target.files[0];	
			if(!file)	return;
			shm_img		= evt.target.files;
			var img 	= document.createElement("img");
			img.height	= 50;
			//img.id 		= this.props.prefix + 'imagex';
			img.style	= "height:50px; margin-right:5px;";
			img.alt 	= '';
			img.file 	= file;
			img.files	= evt.target.files;
			$(evt.currentTarget).parent().find("img").detach();
			$(evt.currentTarget).parent().find("label").text("");
			$(evt.currentTarget).parent().prepend(img);
			var reader = new FileReader();
			reader.g = this;
			reader.onload = (function(aImg) 
			{ 
				return function(e) 
				{ 
					aImg.src = e.target.result; 
				}; 
			})(img);
			reader.readAsDataURL(file);
		})
	});
	//
	shm_add_modal = function (data)
	{
		// console.log(data);
		if(typeof data == "string")
		{
			data={content: data};
		}
		if(!data.title) data.title = __("Attantion");
		$("html").append("<div class='shm_modal_container " + data['class'] + "'></div>");
		$(".shm_modal_container").append("<div class='shm_modal'></div>");
		$(".shm_modal_container").append("<div class='shm_modal_screen wp-core-ui'></div>");
		$(".shm_modal_screen").append("<div class='shm_modal_header shm-color-grey'>" + data.title + "</div>");
		$(".shm_modal_header").append("<div class='shm_modal_close' onclick='shm_close_modal();'>x</div>");
		$(".shm_modal_screen").append("<div class='shm_modal_body'>" + data.content + "</div>");
		$(".shm_modal_screen").append("<div class='shm_modal_footer'></div>");
		if(data.send)
		{
			$(".shm_modal_footer").append(
				"<button class='button' onClick='" + data.sendHandler + "(" + data.sendArgs + ");'>"+ data.send + "</button>"
			);
		}
		$(".shm_modal_footer").append(data.footer)
		$(".shm_modal_footer").append("<button class='button' onclick='shm_close_modal();'>"+__("Close") + "</button>");
		$(".shm_modal").on( 'click', function(evt) {
			$(evt.currentTarget).parents(".shm_modal_container").detach();
		});
	}
	shm_close_modal = function()
	{
		$(".shm_modal_container").detach();
	}

});


function shm_send( params, type )
{
	var $ = jQuery;
	// console.log(params, type);
	jQuery.post	(

		myajax.url,
		{
			action	: 'myajax',
			nonce	: myajax.nonce,
			params	: params
		},
		function( response ) 
		{
			var $ = jQuery;
			//console.log(response);
			try
			{
				var dat = JSON.parse(response);
			}
			catch (e)
			{
				return;
			}
			console.log(dat);
			var command	= dat[0];
			var datas	= dat[1];
			//console.log(command);
			switch(command)
			{
				case "test":
					break;
				case "shm_wclose":
					$(".shm_wizzard").detach();
					$(".shm_wizzard_current").removeClass("shm_wizzard_current");
					break;
				case "shm_doubled":
					window.location.reload(window.location.href);
					break;
				case "shm_wrestart":
					window.location.reload(window.location.href);
					break;
				case "shm_csv":
					var encodedUri = 'data:application/csv;charset=utf-8,' + encodeURIComponent(datas['text']);
					var link = document.createElement("a");
					link.setAttribute("href", datas['text']);
					link.setAttribute("download", datas['name'] + ".csv");
					link.innerHTML= "Download";
					document.body.appendChild(link);
					link.click();
					link.parentNode.removeChild(link);
					break;
				case "shm_set_req":
					if(datas['grecaptcha'] == 1)
						grecaptcha.reset();
					if(datas['reload'])
						window.location.reload(window.location.href);
					break;
				case "shm_add_before":
					$(datas['text'])
						.insertBefore("#form_editor li:eq(" + datas['order'] + ")")
							.hide()
								.slideDown("slow");
					$("#form_editor li").each(
						function(num, elem) 
						{
							var prev_id = $(elem).attr("shm-num");
							$(elem).attr("shm-num", num);
							$(elem).find("[name^='form_forms[" + prev_id + "]']").each( function(n, e)
							{
								var name = $(e).attr("name").replace("form_forms[" + prev_id + "]", "form_forms[" + num + "]");
								$(e).attr("name", name);
							});
						}							
					);
					break;
				case "shm_add_after":
					$(datas['text'])
						.insertAfter("#form_editor li:eq(" + datas['order'] + ")")
							.hide()
								.slideDown("slow");
					$("#form_editor li").each(
						function(num, elem)
						{
							var prev_id = $(elem).attr("shm-num");
							$(elem).attr("shm-num", num);
							$(elem).find("[name^='form_forms[" + prev_id + "]']").each( function(n, e) 
							{
								var name = $(e).attr("name").replace("form_forms[" + prev_id + "]", "form_forms[" + num + "]");
								$(e).attr("name", name);
							});
						}							
					);
					break;
				case "shm_wnext":
					if(datas['href'])
						window.location.href = datas['href'];
					$(".shm_wizzard").detach();
					$(".shm_wizzard_current").removeClass("shm_wizzard_current");
					break;
				case "shm_delete_map_hndl":
					shm_close_modal();
					jQuery("#post-"+datas['id']).slideUp( 800 ).hide("slow");
					window.location.reload(window.location.href);
					break;	
				case "shm_create_map_point":
					shm_close_modal();
					shm_map_add_point(datas['data']);
					break;	
				case "shm_add_point_prepaire":
				case "shm_delete_map":
					shm_add_modal( datas['text'] );
					break;	
				case "shm_notify_req":
					$( "#post-" + datas['post_id'] + " .column-notified" ).empty().append(datas['text']);
					break;	
				case "shm_trash_req":
					$( "#post-" + datas['post_id'] + "" ).fadeOut("slow");
					window.location.reload(window.location.href);
					break;	
				case "map_api":
					$(".map_api_cont").css("opacity", 1);
					setTimeout( function() {
						window.location.reload(window.location.href);
					}, 500 );
				case "shm_yandex_maps_api_key":
					$(".map_api_cont").css("opacity", 1);
					if(datas['hide_dang'])
						$("#shm_settings_yandex_map_api_key_cont .shm-color-alert").hide();
					else
						$("#shm_settings_yandex_map_api_key_cont .shm-color-alert").fadeIn("slow");
					break;				
				case "shm_voc":
					$("#shm_vocabulary_cont").css("opacity", 1);
					break;	
				case "shm_map_is_crowdsourced":
				case "shm_map_marker_premoderation":
				case "shm_reload":
					$("#shm_map_is_crowdsourced_cont").css("opacity", 1);
					break;	
				case "shm_settings_captcha":
				case "shm_captcha_siteKey":
				case "shm_captcha_secretKey":
					$("#shm_settings_captcha_cont").css("opacity", 1);
					if(datas['hide_dang'])
						$("#recaptcha_danger").hide();
					else
						$("#recaptcha_danger").fadeIn("slow");
					break;				
				default:
					var customEvent = new CustomEvent("_shm_send_", {bubbles : true, cancelable : true, detail : dat})
					document.documentElement.dispatchEvent(customEvent);
					break;
			}			
			if(datas['exec'] && datas['exec'] != "")
			{
				window[datas['exec']](datas['args']);
			}
			if(datas['a_alert'])
			{
				alert(datas['a_alert']);
			}
			if(datas.msg)
			{
				jQuery(".msg").detach();
				clearTimeout(setmsg);
				jQuery("<div class='msg'>" + datas.msg + "</div>").appendTo("body").hide().fadeIn("slow");
				setmsg = setTimeout( function() {
					jQuery(".msg").fadeOut(700, jQuery(".msg").detach());
				}, 6000);
			}
		}		
	);
} 

// click marker event for mobiles
jQuery(document).ready(function($) {
	//if(shmapperIsMobileView()) {
		var $shmapperIcons = $(".shm-type-icon");
		$shmapperIcons.on('mousedown', function(e) {
			$(this).removeClass('shmapperDragged');
		});
		$shmapperIcons.click(function(e) {
			e.preventDefault();
			$(this).closest('.shm-form-placemarks').find('.shm-type-icon').removeClass('shmapperMarkerSelected');
			$(this).parents("form.shm-form-request").find('input[name="shm_point_loc"]').removeClass("hidden");
			
			if(!$(this).hasClass('shmapperDragged')) {
				$(this).addClass('shmapperMarkerSelected');
			}
		});
	//}
});
