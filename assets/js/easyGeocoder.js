/* (c) J.F.O. https://daozen.org/ | easyGeocoder changes are in public domain */
/* Leaflet Control Geocoder (c) 2013-2016 Per Liedman, (c) 2012 sa3m https://github.com/sa3m | https://opensource.org/licenses/BSD-2-Clause */
const UTIL = {
	extend: function(obj) {
		const CLASS = function(){},
			proto = Object.create(CLASS.prototype);
		CLASS.extend = function(){
			if (this.initialize){
				this.initialize(...arguments);
			}
		};
		Object.assign(proto, obj);
		CLASS.extend.prototype = proto;
		return CLASS.extend;
	},
	ajax: (url, params, timeout) => {
		const queryString = obj => {
			if (!obj){
				return '';
			}
			const params = [];
			for (let prop of Object.entries(obj)){
				params.push(prop.join('='));
			}
			return '?' + encodeURI(params.join('&')).replace(/%25/g, '%');
		};
		return new Promise((resolve, reject) => {
			const xhr = new XMLHttpRequest();
			xhr.onreadystatechange = () => {
				if (xhr.readyState === 4){
					if (xhr.status === 200){
						resolve(JSON.parse(xhr.responseText));
					} else {
						reject([]);
					}
				}
			};
			xhr.open('GET', url + queryString(params), true);
			xhr.setRequestHeader('Accept', 'application/json');
			xhr.onerror = () => reject([]);
			xhr.ontimeout = xhr.abort;
			xhr.timeout = timeout;
			xhr.send();
		});
	},
	htmlTemplate: (str, jsonData, customData) => {
		const escaped = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&apos;',
			'/': '&sol;',
			'[': '&lbrack;',
			']': '&rbrack;',
			'`': '&grave;',
			'°': '&deg;'
		};
		return str.replace(/\{*(\w+)*\}/g, (str, key) => {
			let value = jsonData[key] || customData[key] || '[ Missing data ]';
			if (typeof value === 'function'){
				value = value(jsonData, customData) || '[ Missing data ]';
			}
			value = typeof value !== 'string' ? value.toString() : value;
			if (/[&<>"'\/\[\]`°]/.test(value)){
				return value.replace(/[&<>"'\/\[\]`°]/g, chr => {
					return escaped[chr];
				});
			}
			return value;
		});
	},
	formatTitle: (data, properties) => {
		let name, names = [];
		for (let p of properties){
			name = data[p];
			if (name){
				names.push(' ' + name);
			}
		}
		return names;
	},
	reverseGeocode: query => {
		const DMS = /^\d{1,2}°\d{1,2}'\d{1,2}"(N|S),\s*\d{1,3}°\d{1,2}'\d{1,2}"(E|W)$/i,
			DD = /^-?\d{1,2}\.\d{1,20},\s*-?\d{1,3}\.\d{1,20}$/;
		return DMS.test(query) || DD.test(query);
	},
	DMSToDD: query => {
		const convertDMS = (D, M, S, dir) => {
				let DD = parseInt(D, 10) + (parseInt(M, 10) / 60) + (parseInt(S, 10) / 3600);
				return /S|W/i.test(dir) ? DD *= -1 : DD;
			},
			DMS = query.split(/[^\d\w]+/),
			lat = convertDMS(DMS[0], DMS[1], DMS[2], DMS[3]),
			lng = convertDMS(DMS[4], DMS[5], DMS[6], DMS[7]);
		return { lat: lat, lng: lng };
	},
	create: (tag, container, className, type = 'HTML') => {
		const el = type === 'SVG' ?
			document.createElementNS('http://www.w3.org/2000/svg', tag) :
			document.createElement(tag);
		if (container){
			container.appendChild(el);
		}
		if (className){
			el.setAttribute('class', className);
		}
		return el;
	},
	setAttributes: (el, obj) => {
		for (let [key, val] of Object.entries(obj)){
			el.setAttribute(key, val);
		}
	},
	stop: e => {
		e.stopPropagation();
		e.preventDefault();
	}
};

const EasyGeocoder = UTIL.extend({
	options: {
		map: {},
		position: 'topright',
		collapsed: true,
		placeholder: 'Search...',
		errorMessage: 'Nothing found',
		geocodeTimeout: 9000,
		autosuggest: true,
		suggestMinLength: 3,
		suggestDelay: 350,
		suggestBackspace: false,
		instanceQuota: 40,
		instanceQuotaMessage: 'Instance quota met',
		resultIcons: false,
		markResult: (result, map, context) => {
			function resultLeaflet(){
				map.setView(result.location, 7);
				if (context.mark){ map.removeLayer(context.mark); }
				context.mark = L.marker(result.location)
					.bindPopup(result.html || result.title, { className: 'easyGeocoder-popup' })
					.addTo(map)
					.openPopup();
			}
			function resultOpenLayers(){
				const ol_fromLonLat = window.ol ? ol.proj.fromLonLat : fromLonLat,
					ol_Overlay = window.ol ? ol.Overlay : Overlay,
					ol_toStringHDMS = window.ol ? ol.coordinate.toStringHDMS : toStringHDMS,
					lat = parseFloat(result.location.lat),
					lng = parseFloat(result.location.lng),
					lngLat = ol_fromLonLat([lng, lat], map.getView().getProjection().getCode()),
					markerElement = UTIL.create('div', '', 'easyGeocoder-marker');
				markerElement.setAttribute('title', ol_toStringHDMS([lng, lat]));
				map.getView().setCenter(lngLat);
				map.getView().setZoom(7);
				if (context.mark){ map.removeOverlay(context.mark); }
				context.mark = new ol_Overlay({
					element: markerElement,
					positioning: 'bottom-center',
					stopEvent: false,
					position: lngLat
				});
				map.addOverlay(context.mark);
			}
			return window.L ? resultLeaflet() : resultOpenLayers();
		},
		defaultResultIcon: data => {
			const resultIcon = UTIL.create('svg', '', '', 'SVG'),
				resultIcon_g = UTIL.create('g', resultIcon, 'easyGeocoder-defaultResultIcon', 'SVG'),
				resultIcon_circle = UTIL.create('circle', resultIcon_g, '', 'SVG'),
				resultIcon_path = UTIL.create('path', resultIcon_g, '', 'SVG');
			UTIL.setAttributes(resultIcon, {
				'viewBox': '0 0 4 4'
			});
			UTIL.setAttributes(resultIcon_g, {
				'transform': 'matrix(0.15285513,-0.15285513,0.15285513,0.15285513,-0.44568202,2)',
				'fill': 'none',
				'stroke-width': '2'
			});
			UTIL.setAttributes(resultIcon_circle, {
				'cx': '8',
				'cy': '8',
				'r': '7'
			});
			UTIL.setAttributes(resultIcon_path, {
				'd': 'M 8,1 V 15 Z M 1,8 h 14 z'
			});
			return resultIcon;
		}
	},
	initialize: function(options){
		Object.assign(this.options, options);
		this.map = this.options.map;
		if (!this.options.geocoder){
			this.options.geocoder = easyGeocoder.nominatim();
		}
		const control = this.createControl();
		if (window.L){
			control.classList.add('leaflet-control');
			this.map._controlCorners[this.options.position].appendChild(control);
		} else {
			control.classList.add('easyGeocoder-ol');
			const ol_Control = window.ol ? ol.control.Control : Control;
			this.map.addControl(new ol_Control({
				element: control
			}));
		}
		if (!this.options.collapsed){
			this.expand();
			this.input.blur();
		}
		this.requestCount = 0;
		this.ajaxRequests = [];
	},
	createControl: function(){
		let rotate = 0,
			opacity = 1;
		const control = UTIL.create('div', '', 'easyGeocoder'),
			button = UTIL.create('div', control, 'easyGeocoder-button'),
			form = UTIL.create('div', control, 'easyGeocoder-form'),
			input = UTIL.create('input', form),
			searchIcon = UTIL.create('svg', button, '', 'SVG'),
			searchIcon_g = UTIL.create('g', searchIcon, 'easyGeocoder-searchIcon', 'SVG'),
			searchIcon_circle = UTIL.create('circle', searchIcon_g, '', 'SVG'),
			searchIcon_path = UTIL.create('path', searchIcon_g, '', 'SVG'),
			throbber = UTIL.create('svg', '', '', 'SVG'),
			throbber_g = UTIL.create('g', throbber, 'easyGeocoder-throbber', 'SVG'),
			throbber_animate = UTIL.create('animateTransform', throbber_g, '', 'SVG');
		this.errorElement = UTIL.create('div', control, 'easyGeocoder-form-no-error');
		this.alts = UTIL.create('ul', control, 'easyGeocoder-alternatives easyGeocoder-alternatives-minimized');
		UTIL.setAttributes(input, {
			'type': 'text',
			'placeholder': this.options.placeholder
		});
		UTIL.setAttributes(searchIcon, {
			'viewBox': '0 0 173 173'
		});
		UTIL.setAttributes(searchIcon_circle, {
			'cx': '71.646767',
			'cy': '71.646767',
			'r': '37.8',
			'fill': 'none',
			'stroke-width': '15'
		});
		UTIL.setAttributes(searchIcon_path, {
			'stroke-width': '21',
			'stroke-linecap': 'round',
			'd': 'm 100.64668,100.64663 32.4005,32.40056'
		});
		UTIL.setAttributes(throbber, {
			'viewBox': '0 0 135 135'
		});
		UTIL.setAttributes(throbber_g, {
			'transform': 'matrix(0,-0.82591789,0.82591789,0,67,67)',
			'stroke-width': '13',
			'stroke-linecap': 'round'
		});
		for (let f = 0; f < 12; f += 1){
			const throbber_line = UTIL.create('line', throbber_g, '', 'SVG');
			UTIL.setAttributes(throbber_line, {
				'y1': '-35',
				'y2': '-60',
				'transform': 'rotate('+ rotate +')',
				'opacity': opacity
			});
			if (opacity === 1){ opacity = 0; }
			rotate += 30;
			opacity += 0.0833;
		}
		UTIL.setAttributes(throbber_animate, {
			'attributeName': 'transform',
			'attributeType': 'XML',
			'calcMode': 'discrete',
			'additive': 'sum',
			'type': 'rotate',
			'repeatCount': 'indefinite',
			'dur': '1s',
			'values': '0;30;60;90;120;150;180;210;240;270;300;330;360',
			'keyTimes': '0;.0833;.1666;.2499;.3332;.4165;.5;.5835;.6668;.7501;.8334;.9167;1'
		});
		this.control = control;
		this.input = input;
		this.button = button;
		this.searchIcon = searchIcon;
		this.throbber = throbber;
		this.controlEvents(input, button, control);
		return control;
	},
	controlEvents: function(input, button, control){
		const buttonHandler = this.options.collapsed ? () => this.toggle() : () => this.geocode();
		input.addEventListener('keydown', this.keydown.bind(this));
		if (this.options.autosuggest && this.options.geocoder.suggest){
			input.addEventListener('paste', () => {
				this.preventSuggest = false;
			});
			input.addEventListener('input', this.suggest.bind(this));
		}
		input.addEventListener('blur', () => {
			if (!this.preventBlurCollapse){
				this.resetControl();
			}
			this.preventBlurCollapse = false;
		});
		button.addEventListener('touchstart', UTIL.stop);
		button.addEventListener('mousedown', UTIL.stop);
		button.addEventListener('touchend', buttonHandler);
		button.addEventListener('click', buttonHandler);
		if (window.L){
			L.DomEvent.disableClickPropagation(control);
		}
	},
	clearResults: function(){
		this.alts.classList.add('easyGeocoder-alternatives-minimized');
		this.errorElement.classList.remove('easyGeocoder-error');
		this.selected = '';
	},
	resetControl: function(){
		if (this.options.collapsed){
			this.control.classList.remove('easyGeocoder-expanded');
		}
		this.clearResults();
		this.input.blur();
	},
	expand: function(){
		this.control.classList.add('easyGeocoder-expanded');
		this.input.focus();
		this.input.select();
	},
	toggle: function(){
		if (this.control.classList.contains('easyGeocoder-expanded')){
			this.resetControl();
		} else {
			this.expand();
		}
	},
	markGeocode: function(result){
		this.resetControl();
		this.options.markResult(result, this.map, this);
	},
	createAlt: function(result, index){
		const li = UTIL.create('li'),
			a = UTIL.create('a', li),
			iconElement = typeof result.icon === 'string' ? 'img' : 'div',
			icon = this.options.resultIcons && result.icon ? UTIL.create(iconElement, a, 'easyGeocoder-display-icons') : false,
			preventCollapse = e => {
				UTIL.stop(e);
				this.preventBlurCollapse = true;
			},
			selectResult = e => {
				UTIL.stop(e);
				this.markGeocode(result);
			};
		a.classList.add('easyGeocoder-result');
		if (icon){
			a.classList.add('easyGeocoder-result-icon');
			if (iconElement === 'img'){
				icon.src = result.icon;
			} else {
				icon.appendChild(result.icon);
			}
		}
		if (result.html){
			a.innerHTML += result.html;
		} else {
			a.appendChild(document.createTextNode(result.title));
		}
		li.setAttribute('data-result-index', index);
		li.addEventListener('touchstart', preventCollapse);
		li.addEventListener('mousedown', preventCollapse);
		li.addEventListener('touchend', selectResult);
		li.addEventListener('click', selectResult);
		return li;
	},
	error: function(message){
		this.clearResults();
		this.errorElement.classList.add('easyGeocoder-error');
		this.errorElement.textContent = message;
	},
	geocodeResult: function(results, suggest, error){
		this.results = results;
		if (this.button.firstChild === this.throbber){
			this.button.replaceChild(this.searchIcon, this.throbber);
		}
		if (!this.control.classList.contains('easyGeocoder-expanded')){
			return;
		}
		this.input.focus();
		if (error === 'maxQuota'){
			return this.error(this.options.instanceQuotaMessage);
		}
		if (results && results.length){
			if (!suggest && results.length === 1){
				return this.markGeocode(results[0]);
			}
			this.alts.innerHTML = '';
			this.alts.classList.remove('easyGeocoder-alternatives-minimized');
			this.errorElement.classList.remove('easyGeocoder-error');
			for (let [f, result] of results.entries()){
				this.alts.appendChild(this.createAlt(result, f));
			}
		} else {
			this.error(this.options.errorMessage);
		}
		this.ajaxRequests = [];
	},
	getJSON: function(url, params, callback){
		this.ajaxRequests.push(UTIL.ajax(url, params, this.options.geocodeTimeout));
		Promise.all(this.ajaxRequests).then(jsonArray => {
				callback(jsonArray[jsonArray.length - 1]);
			}, error => {
				callback(error);
		});
	},
	geocode: function(suggest){
		clearTimeout(this.suggestDelay);
		const value = this.input.value;
		if (!value){
			return;
		}
		if (this.options.instanceQuota && this.requestCount >= this.options.instanceQuota){
			return this.geocodeResult(null, null, 'maxQuota');
		}
		if (value === this.lastGeocode){
			return this.geocodeResult(this.results, suggest);
		}
		this.lastGeocode = value;
		this.requestCount += 1;
		this.suggest = suggest;
		if (this.button.firstChild === this.searchIcon && !suggest){
			this.button.replaceChild(this.throbber, this.searchIcon);
		}
		this.options.geocoder.geocode(value, results => {
			this.geocodeResult(results, suggest);
		}, this);
	},
	suggest: function(){
		if (this.preventSuggest){
			return;
		}
		if (this.input.value.length >= this.options.suggestMinLength){
			this.suggestDelay = setTimeout(() => {
				this.geocode(true);
			}, this.options.suggestDelay);
		}
	},
	keydown: function(e){
		const select = dir => {
			if (this.selected){
				this.selected.classList.remove('easyGeocoder-selected');
				this.selected = this.selected[dir > 0 ? 'nextSibling' : 'previousSibling'];
			}
			if (!this.selected){
				this.selected = this.alts[dir > 0 ? 'firstChild' : 'lastChild'];
			}
			if (this.selected){
				this.selected.classList.add('easyGeocoder-selected');
			}
		};
		switch (e.keyCode){
			case 27: /* ⎋ */
				this.resetControl();
				break;
			case 8:	/* ⌫ */
				if (this.suggest && !this.options.suggestBackspace){
					this.preventSuggest = true;
					this.clearResults();
				} else if (!this.suggest || this.input.value.length <= this.options.suggestMinLength){
					this.clearResults();
				}
				break;
			case 32: /*  */
				this.preventSuggest = true;
				break;
			case 38: /* ↑ */
				select(-1);
				break;
			case 40: /* ↓ */
				select(1);
				break;
			case 13: /* ↵ */
				if (this.selected){
					const index = parseInt(this.selected.getAttribute('data-result-index'), 10);
					this.markGeocode(this.results[index]);
				} else {
					this.geocode();
				}
				break;
			default:
				if (!this.suggest){
					this.clearResults();
				}
				this.preventSuggest = false;
		}
	}
});

const easyGeocoder = options => {
	return new EasyGeocoder(options);
};

const NOMINATIM = (() => {
	EasyGeocoder.Nominatim = UTIL.extend({
		options: {
			geocodeParams: { limit: 4 },
			reverseParams: {},
			template: function(data, custom){
				const formatResult = [],
					displayName = data.display_name,
					mainData = data.address;
				custom = {
					title: /,/.test(displayName) ? displayName.split(',', 1) : displayName,
					urban: mainData.city || mainData.town || mainData.village,
					context: mainData.country || function(data, custom){ return custom.title; }
				};
				formatResult.push('<span>{title}</span>');
				if (custom.urban && mainData.country && custom.urban != custom.title){
					formatResult.push('<span>{urban}, {country}</span>');
				} else if (mainData.state && mainData.country && mainData.state != custom.title){
					formatResult.push('<span>{state}, {country}</span>');
				} else {
					formatResult.push('<span>{context}</span>');
				}
				return UTIL.htmlTemplate(formatResult.join('<br />'), mainData, custom);
			}
		},
		initialize: function(options){
			Object.assign(this.options, options);
			this.geocodeURL = 'https://nominatim.openstreetmap.org/search/';
		},
		getJSON: function(url, params, cb, context){
			context.getJSON(url, params, json => {
				const results = [];
				if (json.length){
					for (let data of json){
						if (!data.lat){
							continue;
						}
						results.push({
							icon: data.icon || context.options.defaultResultIcon(data),
							title: data.display_name,
							html: this.options.template ? this.options.template(data) : undefined,
							location: {
								lat: data.lat,
								lng: data.lon
							}
						});
					}
				}
				return cb(results);
			});
		},
		geocode: function(query, cb, context){
			const params = {
				q: query,
				format: 'json',
				addressdetails: 1
			};
			if (UTIL.reverseGeocode(query)){
				params.q = query.toUpperCase();
				Object.assign(params, this.options.reverseParams);
			} else {
				Object.assign(params, this.options.geocodeParams);
			}
			this.getJSON(this.geocodeURL, params, cb, context);
		},
		suggest: false
	});
	easyGeocoder.nominatim = options => {
		return new EasyGeocoder.Nominatim(options);
	};
})();

const PHOTON = (() => {
	EasyGeocoder.Photon = UTIL.extend({
		options: {
			nameProperties: [
				'name',
				'street',
				'city',
				'state',
				'country'
			],
			geocodeParams: {},
			reverseParams: {},
			template: ''
		},
		initialize: function(options){
			Object.assign(this.options, options);
			const baseURL = 'https://photon.komoot.de/';
			this.geocodeURL = baseURL + 'api/';
			this.reverseURL = baseURL + 'reverse/';
		},
		getJSON: function(url, params, cb, context){
			context.getJSON(url, params, json => {
				const results = [];
				if (json && json.features){
					for (let data of json.features){
						if (!data.geometry.coordinates){
							continue;
						}
						const formattedTitle = UTIL.formatTitle(data.properties, this.options.nameProperties);
						results.push({
							icon: context.options.defaultResultIcon(data),
							title: formattedTitle,
							html: this.options.template ? this.options.template(data, formattedTitle) : undefined,
							location: {
								lat: data.geometry.coordinates[1],
								lng: data.geometry.coordinates[0]
							}
						});
					}
				}
				return cb(results);
			});
		},
		geocode: function(query, cb, context){
			const params = {};
			if (UTIL.reverseGeocode(query)){
				let lat, lng;
				if (/N|S/i.test(query)){ 
					const DD = UTIL.DMSToDD(query);
					lat = DD.lat;
					lng = DD.lng;
				} else {
					const latLng = query.split(',');
					lat = latLng[0].trim();
					lng = latLng[1].trim();
				}
				Object.assign(params, {
					lat: lat,
					lon: lng
				}, this.options.reverseParams);
				this.getJSON(this.reverseURL, params, cb, context);
			} else {
				Object.assign(params, {
					q: query
				}, this.options.geocodeParams);
				this.getJSON(this.geocodeURL, params, cb, context);
			}
		},
		suggest: true
	});
	easyGeocoder.photon = options => {
		return new EasyGeocoder.Photon(options);
	};
})();

const GEONAMES = (() => {
	EasyGeocoder.Geonames = UTIL.extend({
		options: {
			username: '',
			geocodeParams: {},
			reverseParams: {},
			template: ''
		},
		initialize: function(options){
			Object.assign(this.options, options);
			const baseURL = 'https://secure.geonames.org/';
			this.geocodeURL = baseURL + 'searchJSON';
			this.reverseURL = baseURL + 'findNearbyJSON';
		},
		getJSON: function(url, params, cb, context){
			context.getJSON(url, params, json => {
				const results = [];
				if (json.geonames && json.geonames.length){
					for (let data of json.geonames){
						if (!data.lat){
							continue;
						}
						results.push({
							icon: context.options.defaultResultIcon(data),
							title: data.name,
							html: this.options.template ? this.options.template(data) : undefined,
							location: {
								lat: data.lat,
								lng: data.lng
							}
						});
					}
				}
				return cb(results);
			});
		},
		geocode: function(query, cb, context){
			const params = {
				username: this.options.username
			};
			if (UTIL.reverseGeocode(query)){
				let lat, lng;
				if (/N|S/i.test(query)){ 
					const DD = UTIL.DMSToDD(query);
					lat = DD.lat;
					lng = DD.lng;
				} else {
					const latLng = query.split(',');
					lat = latLng[0].trim();
					lng = latLng[1].trim();
				}
				Object.assign(params, {
					lat: lat,
					lng: lng
				}, this.options.reverseParams);
				this.getJSON(this.reverseURL, params, cb, context);
			} else {
				Object.assign(params, {
					q: query
				}, this.options.geocodeParams);
				this.getJSON(this.geocodeURL, params, cb, context);
			}
		},
		suggest: false
	});
	easyGeocoder.geonames = options => {
		return new EasyGeocoder.Geonames(options);
	};
})();

const OPENCAGE = (() => {
	EasyGeocoder.Opencage = UTIL.extend({
		options: {
			apiKey: '',
			geocodeParams: {},
			reverseParams: {},
			template: ''
		},
		initialize: function(options){
			Object.assign(this.options, options);
			this.geocodeURL = 'https://api.opencagedata.com/geocode/v1/json';
		},
		getJSON: function(url, params, cb, context){
			context.getJSON(url, params, json => {
				const results = [];
				if (json.results && json.results.length){
					for (let data of json.results){
						if (!data.geometry){
							continue;
						}
						results.push({
							icon: context.options.defaultResultIcon(data),
							title: data.formatted,
							html: this.options.template ? this.options.template(data) : undefined,
							location: {
								lat: data.geometry.lat,
								lng: data.geometry.lng
							}
						});
					}
				}
				return cb(results);
			});
		},
		geocode: function(query, cb, context){
			const params = {
				key: this.options.apiKey
			};
			if (UTIL.reverseGeocode(query)){
				let lat, lng;
				if (/N|S/i.test(query)){ 
					const DD = UTIL.DMSToDD(query);
					lat = DD.lat;
					lng = DD.lng;
				} else {
					const latLng = query.split(',');
					lat = latLng[0].trim();
					lng = latLng[1].trim();
				}
				Object.assign(params, {
					q: lat + '+' + lng
				}, this.options.reverseParams);
			} else {
				Object.assign(params, {
					q: query.replace(/,/g, '%2C').replace(/\s+/g, '+')
				}, this.options.geocodeParams);
			}
			this.getJSON(this.geocodeURL, params, cb, context);
		},
		suggest: false
	});
	easyGeocoder.opencage = options => {
		return new EasyGeocoder.Opencage(options);
	};
})();

const MAPQUEST = (() => {
	EasyGeocoder.Mapquest = UTIL.extend({
		options: {
			apiKey: '',
			nameProperties: [
				'street',
				'adminArea5',
				'adminArea3',
				'adminArea1'
			],
			geocodeParams: {},
			reverseParams: {},
			template: ''
		},
		initialize: function(options){
			Object.assign(this.options, options);
			const baseURL = 'https://www.mapquestapi.com/geocoding/v1/';
			this.geocodeURL = baseURL + 'address';
			this.reverseURL = baseURL + 'reverse';
		},
		getJSON: function(url, params, cb, context){
			context.getJSON(url, params, json => {
				const results = [];
				if (json.results && json.results[0].locations){
					for (let data of json.results[0].locations){
						if (!data.latLng){
							continue;
						}
						const formattedTitle = UTIL.formatTitle(data, this.options.nameProperties);
						results.push({
							icon: context.options.defaultResultIcon(data),
							title: formattedTitle,
							html: this.options.template ? this.options.template(data, formattedTitle) : undefined,
							location: {
								lat: data.latLng.lat,
								lng: data.latLng.lng
							}
						});
					}
				}
				return cb(results);
			});
		},
		geocode: function(query, cb, context){
			const params = {
				key: this.options.apiKey,
				outFormat: 'json'
			};
			if (UTIL.reverseGeocode(query)){
				let lat, lng;
				if (/N|S/i.test(query)){ 
					const DD = UTIL.DMSToDD(query);
					lat = DD.lat;
					lng = DD.lng;
				} else {
					const latLng = query.split(',');
					lat = latLng[0].trim();
					lng = latLng[1].trim();
				}
				Object.assign(params, {
					location: lat + ',' + lng,
				}, this.options.reverseParams);
				this.getJSON(this.reverseURL, params, cb, context);
			} else {
				Object.assign(params, {
					location: query,
				}, this.options.geocodeParams);
				this.getJSON(this.geocodeURL, params, cb, context);
			}
		},
		suggest: true
	});
	easyGeocoder.mapquest = options => {
		return new EasyGeocoder.Mapquest(options);
	};
})();

const ESRI = (() => {
	EasyGeocoder.Esri = UTIL.extend({
		options: {
			geocodeParams: {},
			reverseParams: {},
			template: ''
		},
		initialize: function(options){
			Object.assign(this.options, options);
			const baseURL = 'https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/';
			this.geocodeURL = baseURL + 'findAddressCandidates';
			this.reverseURL = baseURL + 'reverseGeocode';
		},
		getJSON: function(url, params, cb, context){
			context.getJSON(url, params, json => {
				const results = [],
					pushResults = data => {
						results.push({
							icon: context.options.defaultResultIcon(data),
							title: data.address.Match_addr || data.address,
							html: this.options.template ? this.options.template(data) : undefined,
							location: {
								lat: data.location.y,
								lng: data.location.x
							}
						});
					};
				if (json.candidates && json.candidates.length){
					for (let data of json.candidates){
						if (!data.location){
							continue;
						}
						pushResults(data);
					}
				} else if (json.location){
					let data = json;
					pushResults(data);
				}
				return cb(results);
			});
		},
		geocode: function(query, cb, context){
			const params = {
				f: 'json'
			};
			if (UTIL.reverseGeocode(query)){
				let lat, lng;
				if (/N|S/i.test(query)){ 
					const DD = UTIL.DMSToDD(query);
					lat = DD.lat;
					lng = DD.lng;
				} else {
					const latLng = query.split(',');
					lat = latLng[0].trim();
					lng = latLng[1].trim();
				}
				Object.assign(params, {
					location: lng + ',' + lat
				}, this.options.reverseParams);
				this.getJSON(this.reverseURL, params, cb, context);
			} else {
				Object.assign(params, {
					SingleLine: query
				}, this.options.geocodeParams);
				this.getJSON(this.geocodeURL, params, cb, context);
			}
		},
		suggest: true
	});
	easyGeocoder.esri = options => {
		return new EasyGeocoder.Esri(options);
	};
})();

const HERE = (() => {
	EasyGeocoder.Here = UTIL.extend({
		options: {
			appId: '',
			appCode: '',
			geolocation: 'geo:00.00,00.00',
			extraFeatures: false,
			geocodeParams: {},
			reverseParams: {},
			template: ''
		},
		initialize: function(options){
			Object.assign(this.options, options);
			const baseURL = 'https://places.api.here.com/places/v1/';
			this.geocodeURL = baseURL + 'autosuggest';
			this.reverseURL = baseURL + 'discover/search';
		},
		getJSON: function(url, params, cb, context){
			context.getJSON(url, params, json => {
				let jsonData;
				const results = [],
					extraFeatures = [],
					pushResults = (data, features) => {
						results.push({
							icon: features && features.icon ? features.icon : context.options.defaultResultIcon(data),
							title: data.title,
							html: this.options.template ? this.options.template(data) : undefined,
							location: {
								lat: data.position[0],
								lng: data.position[1]
							}
						});
					},
					pushResultsFeatures = data => {
						/* TODO this preserves [results] priority but annuls preserved request order */
						/* Issue if extraFeatures is combined with autosuggest */
						extraFeatures.push(UTIL.ajax(data.href));
						Promise.all(extraFeatures).then(featuresArray => {
							pushResults(data, featuresArray[featuresArray.length - 1]);
							if (extraFeatures.length === results.length){
								return cb(results);
							}
						});
					};
				if (json.results && json.results.length){
					jsonData = json.results;
				}
				if (json.results && json.results.items && json.results.items.length){
					jsonData = json.results.items;
				}
				if (jsonData){
					for (let data of jsonData){
						if (!data.position){
							continue;
						}
						if (this.options.extraFeatures){
							pushResultsFeatures(data);
						} else {
							pushResults(data);
						}
					}
				}
				if (!this.options.extraFeatures || !jsonData){
					return cb(results);
				}
			});
		},
		geocode: function(query, cb, context){
			const params = {
				q: query,
				app_id: this.options.appId,
				app_code: this.options.appCode,
				Geolocation: this.options.geolocation
			};
			if (UTIL.reverseGeocode(query)){
				Object.assign(params, this.options.reverseParams);
				this.getJSON(this.reverseURL, params, cb, context);
			} else {
				Object.assign(params, this.options.geocodeParams);
				this.getJSON(this.geocodeURL, params, cb, context);
			}
		},
		suggest: true
	});
	easyGeocoder.here = options => {
		return new EasyGeocoder.Here(options);
	};
})();

const MAPBOX = (() => {
	EasyGeocoder.Mapbox = UTIL.extend({
		options: {
			accessToken: '',
			geocodeParams: {},
			reverseParams: {},
			template: ''
		},
		initialize: function(options){
			Object.assign(this.options, options);
			this.geocodeURL = 'https://api.mapbox.com/geocoding/v5/mapbox.places/';
		},
		getJSON: function(url, params, cb, context){
			context.getJSON(url, params, json => {
				const results = [];
				if (json.features && json.features.length){
					for (let data of json.features){
						if (!data.center){
							continue;
						}
						results.push({
							icon: context.options.defaultResultIcon(data),
							title: data.place_name,
							html: this.options.template ? this.options.template(data) : undefined,
							location: {
								lat: data.center[1],
								lng: data.center[0]
							}
						});
					}
				}
				return cb(results);
			});
		},
		geocode: function(query, cb, context){
			const params = {
				access_token: this.options.accessToken
			};
			if (UTIL.reverseGeocode(query)){
				let lat, lng;
				if (/N|S/i.test(query)){ 
					const DD = UTIL.DMSToDD(query);
					lat = DD.lat;
					lng = DD.lng;
				} else {
					const latLng = query.split(',');
					lat = latLng[0].trim();
					lng = latLng[1].trim();
				}
				query = lng + ',' + lat;
				Object.assign(params, this.options.reverseParams);
			} else {
				Object.assign(params, this.options.geocodeParams);
			}
			this.getJSON(this.geocodeURL + encodeURI(query) + '.json', params, cb, context);
		},
		suggest: true
	});
	easyGeocoder.mapbox = options => {
		return new EasyGeocoder.Mapbox(options);
	};
})();

const BING = (() => {
	EasyGeocoder.Bing = UTIL.extend({
		options: {
			apiKey: '',
			geocodeParams: {},
			reverseParams: {},
			template: ''
		},
		initialize: function(options){
			Object.assign(this.options, options);
			this.geocodeURL = 'https://dev.virtualearth.net/REST/v1/Locations';
		},
		getJSON: function(url, params, cb, context){
			context.getJSON(url, params, json => {
				const results = [];
				if (json.resourceSets[0] &&
					json.resourceSets[0].resources &&
					json.resourceSets[0].resources.length){
					for (let data of json.resourceSets[0].resources){
						if (!data.point.coordinates){
							continue;
						}
						results.push({
							icon: context.options.defaultResultIcon(data),
							title: data.name,
							html: this.options.template ? this.options.template(data) : undefined,
							location: {
								lat: data.point.coordinates[0],
								lng: data.point.coordinates[1]
							}
						});
					}
				}
				return cb(results);
			});
		},
		geocode: function(query, cb, context){
			const params = {
				key: this.options.apiKey
			};
			if (UTIL.reverseGeocode(query)){
				let lat, lng;
				if (/N|S/i.test(query)){ 
					const DD = UTIL.DMSToDD(query);
					lat = DD.lat;
					lng = DD.lng;
				} else {
					const latLng = query.split(',');
					lat = latLng[0].trim();
					lng = latLng[1].trim();
				}
				Object.assign(params, {
					q: lat + ',' + lng
				}, this.options.reverseParams);
			} else {
				Object.assign(params, {
					q: query
				}, this.options.geocodeParams);
			}
			this.getJSON(this.geocodeURL, params, cb, context);
		},
		suggest: false
	});
	easyGeocoder.bing = options => {
		return new EasyGeocoder.Bing(options);
	};
})();

const YANDEX = (() => {
	EasyGeocoder.Yandex = UTIL.extend({
		options: {
			lang: 'en_US',
			geocodeParams: {},
			reverseParams: {},
			template: ''
		},
		initialize: function(options){
			Object.assign(this.options, options);
			this.geocodeURL = 'https://geocode-maps.yandex.ru/1.x/';
		},
		getJSON: function(url, params, cb, context){
			context.getJSON(url, params, json => {
				const results = [];
				if (json.response && json.response.GeoObjectCollection &&
					json.response.GeoObjectCollection.featureMember &&
					json.response.GeoObjectCollection.featureMember.length){
					for (let data of json.response.GeoObjectCollection.featureMember){
						if (!data.GeoObject.Point.pos){
							continue;
						}
						results.push({
							icon: context.options.defaultResultIcon(data),
							title: data.GeoObject.metaDataProperty.GeocoderMetaData.Address.formatted || data.GeoObject.name,
							html: this.options.template ? this.options.template(data) : undefined,
							location: {
								lat: data.GeoObject.Point.pos.split(' ')[1],
								lng: data.GeoObject.Point.pos.split(' ')[0]
							}
						});
					}
				}
				return cb(results);
			});
		},
		geocode: function(query, cb, context){
			const params = {
				format: 'json',
				lang: this.options.lang
			};
			if (UTIL.reverseGeocode(query)){
				let lat, lng;
				if (/N|S/i.test(query)){ 
					const DD = UTIL.DMSToDD(query);
					lat = DD.lat;
					lng = DD.lng;
				} else {
					const latLng = query.split(',');
					lat = latLng[0].trim();
					lng = latLng[1].trim();
				}
				Object.assign(params, {
					geocode: lng + ',' + lat
				}, this.options.reverseParams);
			} else {
				Object.assign(params, {
					geocode: query.replace(/\s+/g, '+')
				}, this.options.geocodeParams);
			}
			this.getJSON(this.geocodeURL, params, cb, context);
		},
		suggest: false
	});
	easyGeocoder.yandex = options => {
		return new EasyGeocoder.Yandex(options);
	};
})();

const YANDEX_PLACES = (() => {
	EasyGeocoder.Yandex_Places = UTIL.extend({
		options: {
			apiKey: '',
			lang: 'en_US',
			geocodeParams: {},
			reverseParams: {},
			template: ''
		},
		initialize: function(options){
			Object.assign(this.options, options);
			this.geocodeURL = 'https://search-maps.yandex.ru/v1/';
		},
		getJSON: function(url, params, cb, context){
			context.getJSON(url, params, json => {
				const results = [];
				if (json.features && json.features.length){
					for (let data of json.features){
						if (!data.geometry.coordinates){
							continue;
						}
						const metaData = data.properties ? data.properties : undefined,
							formattedTitle = metaData.description ?	metaData.name + ', ' + metaData.description : metaData.name;
						results.push({
							icon: context.options.defaultResultIcon(data),
							title: formattedTitle,
							html: this.options.template ? this.options.template(data, formattedTitle) : undefined,
							location: {
								lat: data.geometry.coordinates[1],
								lng: data.geometry.coordinates[0]
							}
						});
					}
				}
				return cb(results);
			});
		},
		geocode: function(query, cb, context){
			const params = {
				apikey: this.options.apiKey,
				lang: this.options.lang
			};
			if (UTIL.reverseGeocode(query)){
				let lat, lng;
				if (/N|S/i.test(query)){ 
					const DD = UTIL.DMSToDD(query);
					lat = DD.lat;
					lng = DD.lng;
				} else {
					const latLng = query.split(',');
					lat = latLng[0].trim();
					lng = latLng[1].trim();
				}
				Object.assign(params, {
					text: lat + ',' + lng
				}, this.options.reverseParams);
			} else {
				Object.assign(params, {
					text: query
				}, this.options.geocodeParams);
			}
			this.getJSON(this.geocodeURL, params, cb, context);
		},
		suggest: false
	});
	easyGeocoder.yandex_places = options => {
		return new EasyGeocoder.Yandex_Places(options);
	};
})();

/* export { UTIL, EasyGeocoder, easyGeocoder, NOMINATIM, PHOTON, GEONAMES, OPENCAGE, MAPQUEST, ESRI, HERE, MAPBOX, BING, YANDEX, YANDEX_PLACES }; */

