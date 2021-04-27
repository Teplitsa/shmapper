
var init_map=function(){}, is_admin=function(){ }, map, all_markers = [], $this, geocodeService, eclectMarker, eclectCoords, myMap;
var changeBasemap = function(){}, setBasemap=function(){}, layer, layerLabels, lG;

jQuery(document).ready(function($)
{
	document.documentElement.addEventListener("init_map", function(e) 
	{
		init_map( e.detail.mData, e.detail.points );
	});
	//filter	
	document.documentElement.addEventListener("shm_filter", function(e) 
	{	
		var dat = e.detail;	
		all_markers[dat.uniq].forEach(function(elem)
		{
			if(elem.options.term_id == dat.term_id )
			{
				if(dat.$this.is(":checked"))
					//elem._icon.classList.remove("_hidden");
					$(elem._icon).css("opacity",1);
				else
					//elem._icon.classList.add("_hidden");
					$(elem._icon).css("opacity", 0.125);
			}
		});
	});
	
	//remove eclectMarker
	document.documentElement.addEventListener("clear_form", function(e) 
	{	
		var dat = e.detail;	
		var data = dat[0];
		var map = dat[1];
		if(eclectMarker) {
			eclectMarker.remove(map);
		}
	});
	
	if($(".shm-type-icon").size())
	{
		L.DomEvent.on(document, 'pushing', function(ev)
		{
			L.DomEvent.stopPropagation(ev);	
		});
		//
		$(".shm-type-icon").draggable(
		{
			revert: false,
			start: function(evt, ui)
			{
				$this = $(ui.helper);
				var $map_id = $this.parents("form.shm-form-request").attr("form_id");
				map = shm_maps[$map_id];	
				map.mp.enable();		
			},
			stop: function(evt, ui)
			{
				$(ui.helper).addClass('shmapperDragged');
				$('.shm-type-icon.shmapperMarkerSelected').removeClass('shmapperMarkerSelected');
				shmapperPlaceMarkerOnMap(evt, ui);
			}
		});	
	}
	
	// place marker by addr
	function shm_place_marker_by_addr($this) {
		var addr = $this.val();
//		console.log(addr);
		
		var $selectedMarker = $this.closest('.shm-form-request').find('.shm-form-placemarks .shm-type-icon.shmapperMarkerSelected');
		
		if(!$selectedMarker.length) {
			$selectedMarker = $this.closest('.shm-form-request').find('.shm-form-placemarks .shm-type-icon').first();
			$selectedMarker.addClass('shmapperMarkerSelected');
		}
		
		geocodeService.geocode().text(addr).run(function(error, result)
		{
//			console.log("decoded");
//			console.log(result);
			
			if(result.results[0]) {
				new_mark_coords = result.results[0].latlng;
//				console.log(new_mark_coords);
				
				eclectCoords = [new_mark_coords.lat, new_mark_coords.lng];
				
				var $map_id = $selectedMarker.parents("form.shm-form-request").attr("form_id");
				$selectedMarker.data("straight_geocoding", "true");
				shmapperPlaceMarkerOnMapByCoords($map_id, $selectedMarker);
			}
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
		if( mData.isMap )
		{
			if( mData.isAdmin ) 
			{
				L.ContextMenuClicker = L.Handler.extend({
					addHooks: function() 
					{
						L.DomEvent.on(myMap, 'contextmenu', this.onClicker, this);
					},
					removeHooks: function() 
					{
						L.DomEvent.off(myMap, 'contextmenu', this.onClicker, this);
					},
					onClicker: function(evt)
					{
						geocodeService.reverse().latlng(evt.latlng).run(function(error, result) 
						{
							shm_send( [
							'shm_add_point_prepaire', 
							[ mData.map_id, evt.latlng.lat.toPrecision(7), evt.latlng.lng .toPrecision(7), result.address.Match_addr] 
						] );	
						});
						
					}
				});
				L.Map.addInitHook('addHandler', 'tilt', L.ContextMenuClicker);	
			}		
			L.MousePosit = L.Handler.extend({
				addHooks: function() 
				{
					L.DomEvent.on(myMap, 'mousemove', this.onmousemove, this);
					L.DomEvent.on(myMap, 'touchmove', this.onmousemove, this);
					L.DomEvent.on(myMap, 'touchstart', this.ontouchstart, this);
					L.DomEvent.on(myMap, 'click', this.onmouseclick, this);
				},
				removeHooks: function() 
				{
					L.DomEvent.off(myMap, 'mousemove', this.onmousemove, this);
					L.DomEvent.off(myMap, 'touchmove', this.onmousemove, this);
				},
				onmousemove: function(evt)
				{
					eclectCoords = [
						L.Util.formatNum(evt.latlng.lat, 7), 
						L.Util.formatNum(evt.latlng.lng, 7)
					];
						
					$("[name='latitude']").val( L.Util.formatNum(myMap.getCenter().lat ));
					$("[name='longitude']").val( L.Util.formatNum(myMap.getCenter().lng ));
					$("[name='zoom']").val( myMap.getZoom() );	
				},
				ontouchstart: function(evt)
				{
					
				},
				onmouseclick: function(evt)
				{
					eclectCoords = [
						L.Util.formatNum(evt.latlng.lat, 7), 
						L.Util.formatNum(evt.latlng.lng, 7)
					];
						
					$("[name='latitude']").val( L.Util.formatNum(myMap.getCenter().lat ));
					$("[name='longitude']").val( L.Util.formatNum(myMap.getCenter().lng ));
					$("[name='zoom']").val( myMap.getZoom() );
					
					var $selectedMarker = $('.shm-type-icon.shmapperMarkerSelected');
					if($selectedMarker.size()) {
						shmapperPlaceMarkerOnMap(evt, {"helper": $selectedMarker});
					}
					
				}
			});
			L.Map.addInitHook('addHandler', 'mp', L.MousePosit);	
		}
		//console.log(mData.mapType);
		//var shmLayer1 = mData.mapType && 
			
		var possibleMapTypes = mData.isLayerSwitcher ? ['OpenStreetMap', 'Topographic', 'Streets', 'Gray', 'DarkGray', 'Imagery', 'Physical'] : [];
		var currentMapTypeIndex = possibleMapTypes.indexOf(mData.mapType);
		if (currentMapTypeIndex !== -1) {
			possibleMapTypes.splice(currentMapTypeIndex, 1);
		}
		possibleMapTypes.push(mData.mapType);
		
		var shmLayers = [];
		var shmBaseMaps = {};
		
		for(var li in possibleMapTypes) {
			
			shmBaseMaps[possibleMapTypes[li]] = "OpenStreetMap" !== possibleMapTypes[li] 
				? L.esri.basemapLayer( possibleMapTypes[li] ) : 
				L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', 
				{
					attribution: '<a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a>'
				}); 
			
			shmLayers.push(shmBaseMaps[possibleMapTypes[li]]);			
		}
			
		myMap = L.map(mData.uniq, 
		{
			//layers: [shmLayer1],
			layers: shmLayers,
			center: [mData.latitude, mData.longitude],
			zoom: mData.zoom,
			renderer: L.svg(),
			//attributionControl:false,
			fullscreenControl: mData.isFullscreen ? 
			{
				pseudoFullscreen: false
			} : false,
			zoomControl:mData.isZoomer,
			dragging:!mData.isDesabled,
			//boxZoom:true,
		});			
		shm_maps[mData.uniq] = myMap;
		all_markers[mData.uniq]	= [];	
		
		//layer switcher 
		if(mData.isLayerSwitcher)
		{
			var layerControl = L.control.layerSwitcher();
			L.control.layers(shmBaseMaps).addTo(myMap);
		}
		
		if(mData.isMap) myMap.mp.disable();	
		//https://esri.github.io/esri-leaflet/examples/reverse-geocoding.html
		geocodeService = L.esri.Geocoding.geocodeService();
		//L.esri.basemapLayer("Topographic").addTo(myMap);
		
		if(mData.isDesabled)
			myMap.scrollWheelZoom.disable();
		else
			myMap.scrollWheelZoom.enabled();
		
		
		//search 
		if(mData.isSearch && typeof easyGeocoder !== "undefined" )
		{
			easyGeocoder({ map: myMap });
		}
		
		if( mData.isMap) 
		{
			if(mData.isAdmin) 
				myMap.tilt.enable();
			myMap.mp.enable();
		}
		//clusters
		if( mData.isClausterer )
		{
			var markers = new L.MarkerClusterGroup();
			var dist = markers;//myMap;
			myMap.addLayer(dist);
		}
		else
			var dist = myMap;
		
		var icons = [], marker;
		points.forEach( function(elem)
		{
			if(  elem.icon )
			{
				var h = parseInt(elem.height);
				var w = elem.width ? parseInt(elem.width) : h;
//				console.log(String(w) + ' x ' + h);
				
				if(!icons[elem.term_id])
				{
					icons[elem.term_id] = L.icon({
						iconUrl		: elem.icon,
						draggable	: elem.draggable,
						shadowUrl	: '',
						iconSize	: [w, h], // size of the icon
						shadowSize	: [w, h], // size of the shadow
						iconAnchor	: [w/2, h/2], // point of the icon which will correspond to marker's location
						shadowAnchor: [0, h],  // the same for the shadow
						popupAnchor	: [-w/4, -h/4] // point from which the popup should open relative to the iconAnchor
					});
				}
				
				if(elem.icon != '')
				{
					shoptions = { draggable: elem.draggable, icon: icons[elem.term_id], term_id: elem.term_id};
				}	
				else
				{
					shoptions = { term_id: elem.term_id };
				}	
				marker = L.marker([ elem.latitude, elem.longitude ], shoptions )
					.addTo(dist)
						.bindPopup('<div class=\"shml-body shml-popup-scroll\">' + '<div class=\"shml-title\">' + elem.post_title +'</div>' + elem.post_content + '</div>');
				
			}					
			else if( mData.default_icon && !elem.color )
			{
				shoptions = {
					icon: L.icon({
						draggable : elem.draggable,
						iconUrl: mData.default_icon,
						shadowUrl: '',
						iconSize:     [40, 40], // size of the icon
						iconAnchor:   [20, 20], // point of the icon which will correspond to marker's location
						popupAnchor:  [-10, -20] // point from which the popup should open relative to the iconAnchor
					})
				};
				marker = L.marker([ elem.latitude, elem.longitude ], shoptions )
					.addTo(dist)
						.bindPopup('<div class=\"shml-body shml-popup-scroll\">' + '<div class=\"shml-title\">' + elem.post_title +'</div>' + elem.post_content + '</div>');			
				
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
				marker = L.marker(
					[ elem.latitude, elem.longitude ], 
					{draggable	: elem.draggable,icon: myIcon, term_id: elem.term_id}
				)
				.addTo(dist)
					.bindPopup('<div class=\"shml-body shml-popup-scroll\">' + '<div class=\"shml-title\">' + elem.post_title +'</div>' + elem.post_content + '</div>');
			}
			all_markers[mData.uniq].push(marker);
			if(elem.draggable)
			{
				marker.on('dragend', function (e) 
				{
					$('[name="latitude"]').val(marker.getLatLng().lat);
					$('[name="longitude"]').val(marker.getLatLng().lng);
					geocodeService.reverse().latlng(marker.getLatLng()).run(function(error, result)
					{
						$('[name="location"]').val(result.address.Match_addr);
					});
				});
			}		
		});
		
		
	}
	
	function shmapperPlaceMarkerOnMap(evt, ui) {
		$this = $(ui.helper);
		var $map_id = $this.parents("form.shm-form-request").attr("form_id");
		
//		console.log(eclectCoords);
		shmapperPlaceMarkerOnMapByCoords($map_id, $this);
	}
	
	function shmapperPlaceMarkerOnMapByCoords($map_id, $selectedMarker) {
		map = shm_maps[$map_id];
		
		setTimeout(function()
		{			
			
			//заполняем формы отправки 
			var lat = $("form.shm-form-request[form_id='" + $map_id + "']").find("[name=shm_point_lat]");
			var lon = $("form.shm-form-request[form_id='" + $map_id + "']").find("[name=shm_point_lon]");
			var type = $("form.shm-form-request[form_id='" + $map_id + "']").find("[name=shm_point_type]");
			var loc = $("form.shm-form-request[form_id='" + $map_id + "']").find("[name=shm_point_loc]");
			lat.val(eclectCoords[0]);
			lon.val(eclectCoords[1]);
			type.val($selectedMarker.attr("shm_type_id"));
			//
			if(!$selectedMarker.data("straight_geocoding")) {
				geocodeService.reverse().latlng(eclectCoords).run(function(error, result) {
					//console.log(result.address.Match_addr);
					loc.val(result.address.Match_addr).removeClass("_hidden").hide().fadeIn("slow");
				});
			}
	
			//set marker
			var bg = $selectedMarker.css('background-image');
			if( bg !== "none")
			{
				bg = bg.replace('url(','').replace(')','').replace(/\"/gi, "");
				s_style = {draggable:true};
				
				var icon_width = $selectedMarker.data('icon-width');
				var icon_height = $selectedMarker.data('icon-height');
				
				if(!icon_width) {
					icon_width = 40;
				}
				if(!icon_height) {
					icon_height = 40;
				}
				
				s_style.icon = L.icon({
					iconUrl: bg,
					shadowUrl: '',
					iconSize:     [icon_width, icon_height], // size of the icon
					iconAnchor:   [icon_width / 2, icon_height / 2], // point of the icon which will correspond to marker's location
				});
			}
			else if($selectedMarker.attr("shm_clr"))
			{
				var clr = $selectedMarker.attr("shm_clr");
				var style = document.createElement('style');
				var iid = $selectedMarker.attr("shm_type_id");
				style.type = 'text/css';
				style.innerHTML = '.__class'+ iid + ' { color:' + clr + '; }';
				document.getElementsByTagName('head')[0].appendChild(style);
				var classes = 'dashicons dashicons-location shm-size-40 __class'+ iid;
				var myIcon = L.divIcon({className: classes, iconSize:L.point(30, 40), iconAnchor: [20, 30] });//
				s_style = { draggable:true, icon: myIcon };
			}
			else
			{
				s_style = {draggable:true};
				
			}
			
			if(eclectMarker)
			{
				eclectMarker.remove(map);
			}
			eclectMarker = L.marker(eclectCoords,s_style).addTo(map);
			map.mp.disable();
			eclectMarker.on("dragstart", function(evt){
				console.log(evt);
			});
			
			eclectMarker.on("dragend touchend", function(evt)
			{
				//console.log(evt.target._latlng);
				eclectCoords = [evt.target._latlng.lat, evt.target._latlng.lng]
				//заполняем формы отправки 
				var lat = $("form.shm-form-request[form_id='" + $map_id + "']").find("[name=shm_point_lat]");
				var lon = $("form.shm-form-request[form_id='" + $map_id + "']").find("[name=shm_point_lon]");
				var type = $("form.shm-form-request[form_id='" + $map_id + "']").find("[name=shm_point_type]");
				var loc = $("form.shm-form-request[form_id='" + $map_id + "']").find("[name=shm_point_loc]");
				lat.val(eclectCoords[0]);
				lon.val(eclectCoords[1]);
				type.val($selectedMarker.attr("shm_type_id"));
				//
				if(!$selectedMarker.data("straight_geocoding")) {
					geocodeService.reverse().latlng(eclectCoords).run(function(error, result) {
						//console.log(result.address.Match_addr);
						loc.val(result.address.Match_addr).removeClass("_hidden").hide().fadeIn("slow");
					});
				}
			})
			
			$selectedMarker.data("straight_geocoding", "");
			
		}, 100);
		
		$selectedMarker.css({left:0, top:0}).hide().fadeIn("slow");
		$selectedMarker.parents(".shm-form-placemarks").removeAttr("required").removeClass("shm-alert");
	}
});



  