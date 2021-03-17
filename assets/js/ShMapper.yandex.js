
var init_map=function(){}, is_admin=function(){}
jQuery(function () 
{
	ymaps.ready(init);
	
});

function init () 
{		
	
}
jQuery(document).ready(function($)
{
	document.documentElement.addEventListener("init_map", function(e) 
	{
		ymaps.ready( function()
		{
			init_map( e.detail.mData, e.detail.points ) 
		} ); 
	});
	//filter	
	document.documentElement.addEventListener("shm_filter", function(e) 
	{	
		var dat = e.detail;	
		var geos = dat.map.geoObjects;
		for(var ii = 0, ll = geos.getLength(); ii < ll; ii++)
		{
			switch(geos.get([ii]).options.get("type"))
			{
				case "clusterer":
					var clusterer  	= geos.get([ii]);
					var mrks 		= clusterer.getGeoObjects();
					for(var i=0, l = mrks.length; i<l; i++ )
					{
						if(dat.term_id == mrks[i].options.get("term_id"))
							mrks[i].options.set({visible : dat.$this.is(":checked")});
					}
					break;
				case "point":
				default:
					if(dat.term_id == geos.get([ii]).options.get("term_id"))
							geos.get([ii]).options.set({visible : dat.$this.is(":checked")});
					break;
			}
		}
	});
	initAddress = function(new_mark_coords)
	{
		ymaps.geocode(new_mark_coords).then(function (res) 
		{
			var firstGeoObject = res.geoObjects.get(0);
			var getAdministrativeAreas = firstGeoObject.getAdministrativeAreas().join(", ");
			var getLocalities = firstGeoObject.getLocalities().join(", ");
			var getThoroughfare = firstGeoObject.getThoroughfare();
			var getPremise = firstGeoObject.getPremise();
			var address = [
				getAdministrativeAreas,
				getLocalities,
				getThoroughfare
			];
			if(getPremise)
				address.push(getPremise);
			shm_address = address.join(", ");
			var dat = { adress: shm_address };
			var initAddress = new CustomEvent("initAddress", {bubbles : true, cancelable : true, detail : dat});
			document.documentElement.dispatchEvent(initAddress);
		});
	}
	//new point creating engine
	addAdress = function($this, new_mark_coords)
	{	
		ymaps.geocode(new_mark_coords).then(function (res) 
		{		
			//console.log( new_mark_coords );
			var firstGeoObject = res.geoObjects.get(0);
			var getAdministrativeAreas = firstGeoObject.getAdministrativeAreas().join(", ");
			var getLocalities = firstGeoObject.getLocalities().join(", ");
			var getThoroughfare = firstGeoObject.getThoroughfare();
			var getPremise = firstGeoObject.getPremise();
			var address = [
				getAdministrativeAreas,
				getLocalities,
				getThoroughfare
			];
			if(getPremise)
				address.push(getPremise);
			shm_address = address.join(", ");
			//заполняем формы отправки 
			var lat = $this.parents("form.shm-form-request").find("[name=shm_point_lat]");
			var lon = $this.parents("form.shm-form-request").find("[name=shm_point_lon]");
			var type = $this.parents("form.shm-form-request").find("[name=shm_point_type]");
			var loc = $this.parents("form.shm-form-request").find("[name=shm_point_loc]");
			lat.val(new_mark_coords[0]);
			lon.val(new_mark_coords[1]);
			if(!$this.data("straight_geocoding")) {
				loc.val(shm_address).removeClass("hidden").hide().fadeIn("slow");
			}
			type.val($this.attr("shm_type_id"));
		})			
	}
	if($(".shm-type-icon").size())
	{
		$(".shm-type-icon").draggable(
		{
			revert: false,
			start: (evt, ui) => 
			{
				$this = $(ui.helper);
				var $map_id = $this.parents("form.shm-form-request").attr("form_id");
				
			},
			stop: (evt, ui) =>
			{
				$(ui.helper).addClass('shmapperDragged');
				shmapperPlaceMarkerOnMap(evt, ui);
				$('.shm-type-icon.shmapperMarkerSelected').removeClass('shmapperMarkerSelected');
			}
		});	
	}
	
	// place marker by addr
	function shm_place_marker_by_addr($this) {
		var addr = $this.val();
		//console.log(addr);
		
		var $selectedMarker = $this.closest('.shm-form-request').find('.shm-form-placemarks .shm-type-icon.shmapperMarkerSelected');
		
		if(!$selectedMarker.length) {
			$selectedMarker = $this.closest('.shm-form-request').find('.shm-form-placemarks .shm-type-icon').first();
			$selectedMarker.addClass('shmapperMarkerSelected');
		}
		
		ymaps.geocode(addr).then(function (res) {
			var firstGeoObject = res.geoObjects.get(0);
//			console.log("decoded");
			
			new_mark_coords = firstGeoObject.geometry.getCoordinates();
//			console.log(new_mark_coords);
			
			var $map_id = $selectedMarker.parents("form.shm-form-request").attr("form_id");
			map = shm_maps[$map_id];
			
			$selectedMarker.data("straight_geocoding", "true");
			shmapperPlaceMarkerOnMapByCoords(map, new_mark_coords, $selectedMarker);
			
		}, function (err) {
//		    console.log("error");
		    console.log(err);
		}); 
	}

	var $addrInput = $("input[name='shm_point_loc']");
	$addrInput.change(function(){
		shm_place_marker_by_addr($(this));
	});
	$addrInput.keydown(function(e){
	    if(e.keyCode == 13){
	        e.preventDefault();
			shm_place_marker_by_addr($(this));
	    }
	});
	
	//
	init_map = function(mData, points)
	{
		var i=0, paramet;
		var myMap = new ymaps.Map(mData.uniq, 
		{
		  center: [ mData.latitude, mData.longitude],
		  controls: [ ],
		  zoom: mData.zoom,
		  type: 'yandex#' + mData.mapType
		});
		
		//search 
		if(mData.isSearch)
		{
			var searchControl = new ymaps.control.SearchControl({
				options: {
					provider: 'yandex#search'
				}
			});
			myMap.controls.add(searchControl);
		}
		
		//fullscreen 
		if(mData.isFullscreen)
		{
			var fsControl = new ymaps.control.FullscreenControl({
				options: {
					provider: 'yandex#search'
				}
			});
			myMap.controls.add(fsControl);
		}
		
		//layer switcher 
		if(mData.isLayerSwitcher)
		{
			myMap.controls.add(new ymaps.control.TypeSelector(['yandex#map', 'yandex#hybrid', 'yandex#satellite']));
		}
		
		//zoom slider 
		if(mData.isZoomer)
		{
			myMap.controls.add('zoomControl', 
			{
				float: 'none'
			});
		}
		
		//config map behaviors
		if(mData.isDesabled)
		{
			myMap.behaviors.disable('scrollZoom');
			myMap.behaviors.disable('drag');
		}	
		// add to global array
		shm_maps[mData.uniq] = myMap;
		
		// Hand-made Boolon
		var customItemContentLayout = ymaps.templateLayoutFactory.createClass(
			// The 'raw' flag means the data is inserted 'as is' without html escaping.
			'<div class=ballon_header>{{ properties.balloonContentHeader|raw }}</div>' +
				'<div class=ballon_body>{{ properties.balloonContentBody|raw }}</div>' +
				'<div class="ballon_footer shm_ya_footer">{{ properties.balloonContentFooter|raw }}</div>'
		);
		if( mData.isClausterer )
		{
			var clusterer = new ymaps.Clusterer({	
				gridSize: 128,
				hasHint: true,
				minClusterSize: 3,
				clusterIconLayout: 'default#pieChart',
				clusterIconPieChartRadius: 40,
				clusterIconPieChartCoreRadius: 30,
				clusterIconPieChartStrokeWidth: 0,
				clusterNumbers: [10],
				//clusterIconContentLayout: null,
				//groupByCoordinates: false,
				clusterBalloonContentLayout: 'cluster#balloonCarousel',
				clusterBalloonItemContentLayout: customItemContentLayout,
				clusterBalloonPanelMaxMapArea: 0,
				clusterBalloonContentLayoutWidth: 270,
				clusterBalloonContentLayoutHeight: 100,
				clusterBalloonPagerSize: 5,
				clusterOpenBalloonOnClick: true,
				clusterDisableClickZoom: true,
				clusterHideIconOnBalloonOpen: false,
				geoObjectHideIconOnBalloonOpen: false,
				type:'clusterer'
			});
			clusterer.hint = '';
		}
		points.forEach( elem =>
		{
			if( elem.icon )
			{
				var h = parseInt(elem.height);
				var w = elem.width ? parseInt(elem.width) : h;
//				console.log( w, h );
				paramet = {
					balloonMaxWidth: 250,
					balloonItemContentLayout: customItemContentLayout,
					hideIconOnBalloonOpen: false,
					iconColor:elem.color,
					iconLayout: 'default#image',
					iconImageHref: elem.icon,
					iconImageSize:[w, h], //[50,50], 
					iconImageOffset: [-w/2, -h/2],
					term_id:elem.term_id,
					type:'point',
					draggable: true
				};
			}
			else if( mData.default_icon && !elem.color)
			{
				paramet = {
					balloonMaxWidth: 250,
					balloonItemContentLayout: customItemContentLayout,
					hideIconOnBalloonOpen: false,
					iconLayout: 'default#image',
					iconImageHref: mData.default_icon,
					iconImageSize: [40,40], 
					iconImageOffset: [-20, -20],
					term_id:-1,
					type:'point',
					draggable: true
				};
				
			}
			else
			{
				paramet = {
					draggable: true,
					balloonMaxWidth: 250,
					balloonItemContentLayout: customItemContentLayout,
					hideIconOnBalloonOpen: false,
					iconColor: elem.color ? elem.color : '#FF0000',
					preset: 'islands#dotIcon',
					term_id:elem.term_id,
					type:'point',
					draggable: true
				}
			}
			
			var myPlacemark = new ymaps.Placemark(
				[elem.latitude, elem.longitude],
				{
					geometry: 
					{
						type: 'Point', // тип геометрии - точка
						coordinates: [elem.latitude, elem.longitude] // координаты точки
					},
					draggable: true,
					balloonContentHeader: elem.post_title, 
					balloonContentBody: elem.post_content,
					balloonContentFooter: '',
					hintContent: elem.post_title
					
				}, 
				paramet
			);
			if(!mData.isMap)
			{				
				document.documentElement.addEventListener("initAddress", function(e) 
				{
					$("[name='location']").val(e.detail.adress);
				})
				myPlacemark.events.add("dragend", evt =>
				{

					var shmCoordinates = evt.get('target').geometry.getCoordinates();
					$("[name='shm_default_latitude']").val(shmCoordinates[0]).trigger('change');
					$("[name='shm_default_longitude']").val(shmCoordinates[1]).trigger('change');

					var pos = evt.get("position");
					var globalPixelPoint = myMap.converter.pageToGlobal( [pos[0], pos[1]] );
					var new_mark_coords = myMap.options.get('projection').fromGlobalPixels(globalPixelPoint, myMap.getZoom());
					$("[name='latitude']").val(new_mark_coords[0].toFixed(6));
					$("[name='longitude']").val(new_mark_coords[1].toFixed(6));
					initAddress(new_mark_coords);
				});

				// On change zoom.
				myMap.events.add('boundschange', function(e){
					zoom = myMap.getZoom();
					$('[name=shm_default_zoom]').val( zoom ).trigger('change');
				});
			}

			if( mData.isClausterer )
			{
				clusterer.add(myPlacemark);
			}			
			else 
				myMap.geoObjects.add(myPlacemark);
		})
		if( mData.isClausterer )	myMap.geoObjects.add(clusterer);
		if(mData.isAdmin)
			is_admin(myMap, mData);
		
		myMap.events.add('click', evt => {
			var $selectedMarker = $('.shm-type-icon.shmapperMarkerSelected');
			if($selectedMarker.size()) {
				shmapperPlaceMarkerOnMap({"clientX": evt.get('domEvent').get('pageX'), "clientY": evt.get('domEvent').get('pageY') - window.scrollY}, {"helper": $selectedMarker});
			}
		});
			var finish_draw_map = new CustomEvent("finish_draw_map", {bubbles : true, cancelable : true, detail : {data:mData, points:points} });
			document.documentElement.dispatchEvent(finish_draw_map);		
	}

	is_admin = function(myMap, mData)
	{

		if(mData.isMap)
		{

			myMap.events.add( 'boundschange', function(event)
			{
				 coords = myMap.getCenter();
				 zoom = myMap.getZoom();
				 $('[name=latitude]').val(coords[0].toPrecision(7));
				 $('[name=longitude]').val(coords[1].toPrecision(7));
				 $('[name=zoom]').val(zoom);
			});
			myMap.events.add('contextmenu', function (e) 
			{
				if (!myMap.balloon.isOpen()) 
				{
					var coords = e.get('coords');
					shm_send( 
						['shm_add_point_prepaire', [ mData.map_id, coords[0].toPrecision(7), coords[1].toPrecision(7)] ] 
					);
				}
				else 
				{
					myMap.balloon.close();
				}
			});
		}
		else
		{
			
		}
	}
	
	function shmapperPlaceMarkerOnMap(evt, ui) {
		$this = $(ui.helper);
		var $map_id = $this.parents("form.shm-form-request").attr("form_id");
		map = shm_maps[$map_id];
		//
//		console.log(evt.clientX, evt.clientY + window.scrollY);
		var globalPixelPoint = map.converter.pageToGlobal( [evt.clientX, evt.clientY + window.scrollY] );
		new_mark_coords = map.options.get('projection').fromGlobalPixels(globalPixelPoint, map.getZoom());
		shmapperPlaceMarkerOnMapByCoords(map, new_mark_coords, $this);
	}
	
	function shmapperPlaceMarkerOnMapByCoords(map, new_mark_coords, $markerIcon) {
		map.geoObjects.remove(shm_placemark);
		var bg = $markerIcon.css('background-image');
		if( bg !== "none")
		{
			bg = bg.replace('url(','').replace(')','').replace(/\"/gi, "");
			shm_paramet = {
				balloonMaxWidth: 250,
				hideIconOnBalloonOpen: false,
				iconLayout: 'default#imageWithContent',
				iconShadow:true,
				iconImageHref: bg,
				iconImageSize:[40,40], 
				iconImageOffset: [-20, -20],
				draggable:true,
				term_id:$markerIcon.attr("shm_type_id"),
				type:'point',
				fill:true,
				fillColor: "#FF0000",
				opacity:0.22
			};
		}
		else
		{
			shm_paramet = {
				balloonMaxWidth: 250,
				hideIconOnBalloonOpen: false,
				iconColor: $markerIcon.attr("shm_clr") ? $markerIcon.attr("shm_clr"):'#FF0000',
				preset: 'islands#dotIcon',
				draggable:true,
				term_id:$markerIcon.attr("shm_type_id"),
				type:'point',
				fill:true,
				fillColor: "#FF0000",
				iconShadow:true,
				opacity:0.22
			}
		}
		
		shm_placemark = new ymaps.GeoObject({
			geometry: 
			{
				type: 'Point',
				coordinates: new_mark_coords,
			}
		} , 
		shm_paramet);
		
		shm_placemark.events.add("dragend", evt =>
		{
			var pos = evt.get("position");
			var globalPixelPoint = map.converter.pageToGlobal( [pos[0], pos[1]] );
			new_mark_coords = map.options.get('projection').fromGlobalPixels(globalPixelPoint, map.getZoom());
			//console.log(pos);
			//console.log( evt.originalEvent.target.options.get("type") );
			addAdress( $markerIcon, new_mark_coords );
		});
		addAdress( $markerIcon, new_mark_coords );
		map.geoObjects.add(shm_placemark); 
		$markerIcon.css({left:0, top:0}).hide().fadeIn("slow");
		$markerIcon.parents(".shm-form-placemarks").removeAttr("required").removeClass("shm-alert");		
		
		$markerIcon.data("straight_geocoding", "");
	}
	
})
