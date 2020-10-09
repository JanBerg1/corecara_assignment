import 'ol/ol.css';
import {Map,View} from 'ol';
import TileLayer from 'ol/layer/Tile';
import OSM from 'ol/source/OSM';
import {fromLonLat} from 'ol/proj';

// Define javascript function for getting openlayers map
var my_map = {                       
    display: function (lat, lon) { 
            new Map({
            target: 'map',
            layers: [
                new TileLayer({
                    source: new OSM()
                })
            ],
            view: new View({
                center: fromLonLat([lon,lat]),
                zoom: 16
                        })
            });
    }                                
};                                   
export default my_map;  