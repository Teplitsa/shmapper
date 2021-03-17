(function (factory) {
    if (typeof define === 'function' && define.amd) 
	{
        // AMD
        define(['leaflet'], factory);
    } 
	else if (typeof module !== 'undefined') 
	{
        // Node/CommonJS
        module.exports = factory(require('leaflet'));
    } 
	else 
	{
        // Browser globals
        if (typeof window.L === 'undefined') 
		{
            throw new Error('Leaflet must be loaded first');
        }
        factory(window.L);
    }
}(function (L) 
{
    L.Control.LayerSwitcher = L.Control.extend({
        options: {
            position: 'topright',
            title: {}
        },
        onAdd: function (map) 
		{
            var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
            this.select = L.DomUtil.create('select', 'leaflet-control-layerswitch leaflet-bar-part', container);
            [
				"Topographic",
				"Streets",
				//"NationalGeographic",
				//"Oceans",
				"Gray",
				"DarkGray",
				//"StreetsRelief", 
				"Imagery",
				//"ImageryClarity",
				//"ImageryFirefly",
				"Physical"
			].forEach(function(elem)
				{
					var option = document.createElement("option");
					option.text = elem;
					option.value = elem;
					this.select.appendChild(option);
				});
			this._layer = L.esri.basemapLayer( 'Topographic' );
			this._layerLabels;
			this._map.addLayer(this._layer);
			L.DomEvent.on(this.select, 'change', this._change, this);
			
            return container;
        },
		_change: function (e) 
		{
			L.DomEvent.stopPropagation(e);
            L.DomEvent.preventDefault(e);
            var basemap = e.target.value;
			if (this._layer) 
				this._map.removeLayer(this._layer);
			this._layer = L.esri.basemapLayer( basemap );
			this._map.addLayer(this._layer);
			if (this._layerLabels) 
			{
				this._map.removeLayer(this._layerLabels);
			}

			if (
				   basemap === 'ShadedRelief'
				|| basemap === 'Oceans'
				|| basemap === 'Gray'
				|| basemap === 'DarkGray'
				|| basemap === 'Terrain'
			) 
			{
				this._layerLabels = L.esri.basemapLayer(basemap + 'Labels');
				this._map.addLayer(this._layerLabels);
			} 
			else if ( basemap.includes('Imagery')) 
			{
				this._layerLabels = L.esri.basemapLayer('ImageryLabels');
				this._map.addLayer(this._layerLabels);
			}
        },
    });
    L.control.layerSwitcher = function (options) 
	{
        return new L.Control.LayerSwitcher(options);
    };
}));