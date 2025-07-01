import './bootstrap';
import L from 'leaflet';
window.L = L; // جعل Leaflet متاحًا عالميًا ليتم استخدامه في مكونات Filamentimport L from 'leaflet';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// إصلاح مسار الأيقونات
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

window.L = L; // جعل Leaflet متاحًا عالميًا