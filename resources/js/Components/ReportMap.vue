<script setup>
import { ref, onMounted, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

const props = defineProps({
    latitude: Number,
    longitude: Number,
    accuracy: Number,
    reportLocation: String
});

const mapContainer = ref(null);
let map = null;
let marker = null;
let accuracyCircle = null;
let barangayBorder = null;

// Your actual Cabulijan border coordinates
const cabulijanBorder = [
    [9.9600992, 123.9754292],
    [9.959127, 123.9759442],
    [9.9587466, 123.9757725],
    [9.9577744, 123.9763733],
    [9.9547311, 123.9770171],
    [9.9490722, 123.9742008],
    [9.9490524, 123.9743845],
    [9.9490352, 123.9745656],
    [9.9489718, 123.9748821],
    [9.9489296, 123.9751234],
    [9.9490035, 123.9752736],
    [9.9489401, 123.9755365],
    [9.9461503, 123.9741417],
    [9.9445018, 123.9783474],
    [9.9435295, 123.9828106],
    [9.9482215, 123.9852139],
    [9.9594229, 123.9840552],
    [9.9613673, 123.9822957],
    [9.9628466, 123.9804932],
    [9.9626776, 123.9795491],
    [9.9622549, 123.9790341],
    [9.9621281, 123.9783474],
    [9.961811, 123.9776179],
    [9.9613673, 123.9770171],
    [9.9610291, 123.9764592],
    [9.9606064, 123.9757296],
    [9.9601837, 123.9756652],
    [9.9600992, 123.9754292]  // Close the polygon
];

// Google Maps-like custom marker
const googleMapsIcon = L.divIcon({
    className: 'custom-google-marker',
    html: `
        <div style="
            position: relative;
            width: 26px;
            height: 26px;
        ">
            <!-- Outer circle (Google Maps blue) -->
            <div style="
                position: absolute;
                top: 0;
                left: 0;
                width: 26px;
                height: 26px;
                background: #4285F4;
                border: 3px solid white;
                border-radius: 50%;
                box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                animation: pulse 2s infinite;
            "></div>
            <!-- Inner dot -->
            <div style="
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 8px;
                height: 8px;
                background: white;
                border-radius: 50%;
            "></div>
        </div>
    `,
    iconSize: [26, 26],
    iconAnchor: [13, 13]
});

onMounted(() => {
    if (mapContainer.value) {
        initializeMap();
    }
});

watch(() => [props.latitude, props.longitude], () => {
    if (map) {
        updateMap();
    }
});

function initializeMap() {
    const initialLat = props.latitude || 9.953; 
    const initialLng = props.longitude || 123.977; 
    const zoomLevel = props.latitude ? 14 : 13;

    map = L.map(mapContainer.value).setView([initialLat, initialLng], zoomLevel);

    // Define both map layers
    const streetLayer = L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png', {
        maxZoom: 20,
        attribution: '&copy; <a href="https://www.stadiamaps.com/" target="_blank">Stadia Maps</a> &copy; <a href="https://openmaptiles.org/" target="_blank">OpenMapTiles</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    });

    const satelliteLayer = L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_satellite/{z}/{x}/{y}{r}.jpg', {
        maxZoom: 20,
        attribution: '&copy; <a href="https://www.stadiamaps.com/" target="_blank">Stadia Maps</a> &copy; <a href="https://openmaptiles.org/" target="_blank">OpenMapTiles</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    });

    // Add default layer
    streetLayer.addTo(map);

    // Add layer control to map (top-right corner)
    const baseLayers = {
        "Map View": streetLayer,
        "Satellite View": satelliteLayer
    };

    L.control.layers(baseLayers, null, {
        position: 'topright'
    }).addTo(map);

    // Add Cabulijan border
    addBarangayBorder();

    if (props.latitude && props.longitude) {
        addMarker();
    }
}

function addBarangayBorder() {
    // Remove existing border if any
    if (barangayBorder) {
        map.removeLayer(barangayBorder);
    }

    // Create barangay border polygon
    barangayBorder = L.polygon(cabulijanBorder, {
        color: '#1a73e8', 
        weight: 3,
        opacity: 0.8,
        fillColor: '#1a73e8',
        fillOpacity: 0.08, 
        dashArray: '5, 5', 
        className: 'barangay-border'
    }).addTo(map);

    // Add popup to border
    barangayBorder.bindPopup(`
        <div class="border-popup">
            <strong>🏠 Barangay Cabulijan</strong><br>
            <small>Tubigon, Bohol</small><br>
            <small>Official Boundary</small>
        </div>
    `);

    // Add border label in the center
    const center = getPolygonCenter(cabulijanBorder);
    L.marker(center, {
        icon: L.divIcon({
            className: 'barangay-label',
            html: `
                <div style="
                    background: rgba(26, 115, 232, 0.9);
                    color: white;
                    padding: 6px 12px;
                    border-radius: 16px;
                    font-size: 12px;
                    font-weight: bold;
                    white-space: nowrap;
                    border: 2px solid white;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                ">Brgy. Cabulijan</div>
            `,
            iconSize: [140, 35],
            iconAnchor: [70, 17]
        })
    }).addTo(map);
}

function getPolygonCenter(coords) {
    let latSum = 0;
    let lngSum = 0;
    const numPoints = coords.length - 1;
    
    for (let i = 0; i < numPoints; i++) {
        latSum += coords[i][0];
        lngSum += coords[i][1];
    }
    
    return [latSum / numPoints, lngSum / numPoints];
}

function addMarker() {
    if (marker) {
        map.removeLayer(marker);
    }
    if (accuracyCircle) {
        map.removeLayer(accuracyCircle);
    }

    const isInside = isInsideCabulijan(props.latitude, props.longitude);
    
    marker = L.marker([props.latitude, props.longitude], { icon: googleMapsIcon })
        .addTo(map)
        .bindPopup(`
            <div class="custom-popup">
                <div class="popup-header">
                    <strong>📍 Report Location</strong>
                </div>
                <div class="popup-content">
                    <div class="location-text">${props.reportLocation || 'No location description'}</div>
                    ${props.accuracy ? `
                    <div class="accuracy-info">
                        <small>GPS Accuracy: ${props.accuracy.toFixed(0)} meters</small>
                    </div>
                    ` : ''}
                    <div class="border-status ${isInside ? 'inside' : 'borderline'}">
                        <small>
                            ${isInside ? 
                            '✅ Confirmed Inside Cabulijan' : 
                            '📍 Near Cabulijan Border (Under Review)'}
                        </small>
                    </div>
                </div>
            </div>
        `)
        .openPopup();

    // Add accuracy circle
    if (props.accuracy) {
        accuracyCircle = L.circle([props.latitude, props.longitude], {
            color: isInside ? '#4285F4' : '#ea4335',
            fillColor: isInside ? '#4285F4' : '#ea4335',
            fillOpacity: 0.1,
            weight: 2,
            opacity: 0.6,
            dashArray: '5, 5',
            radius: props.accuracy
        }).addTo(map);
    }

    // Fit map to show border, marker, and accuracy circle
    const bounds = barangayBorder.getBounds();
    if (accuracyCircle) {
        bounds.extend(accuracyCircle.getBounds());
    } else {
        bounds.extend([props.latitude, props.longitude]);
    }
    
    map.fitBounds(bounds, { 
        padding: [30, 30],
        maxZoom: 16 
    });
}

// Simple point-in-polygon check
function isInsideCabulijan(lat, lng) {
    const point = L.latLng(lat, lng);
    const polyBounds = L.latLngBounds(cabulijanBorder);
    return polyBounds.contains(point);
}

function updateMap() {
    if (props.latitude && props.longitude) {
        addMarker();
    } else if (marker) {
        map.removeLayer(marker);
        if (accuracyCircle) {
            map.removeLayer(accuracyCircle);
        }
    }
}
</script>

<template>
    <div class="w-full h-96 rounded-lg border-2 border-gray-200 overflow-hidden shadow-lg">
        <div class="map-info-bar bg-blue-50 border-b border-blue-200 px-4 py-2 text-sm text-blue-700 flex justify-between items-center">
            <span>🗺️ Barangay Cabulijan Boundary Displayed</span>
            <span class="text-xs bg-blue-100 px-2 py-1 rounded">Official Map</span>
        </div>
        <div ref="mapContainer" class="w-full h-full"></div>
    </div>
</template>

<style scoped>
.w-full {
    border-radius: 8px;
}

.map-info-bar {
    font-weight: 500;
}
</style>

<style>
/* Global styles for Leaflet elements */
.custom-google-marker {
    background: transparent !important;
    border: none !important;
}

.barangay-border {
    stroke-dasharray: 5, 5 !important;
}

.barangay-label {
    background: transparent !important;
    border: none !important;
}

/* Custom popup styling */
.custom-popup {
    min-width: 220px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.popup-header {
    font-weight: 600;
    color: #1a73e8;
    margin-bottom: 6px;
    font-size: 14px;
}

.popup-content {
    color: #5f6368;
}

.location-text {
    font-size: 13px;
    margin-bottom: 4px;
    line-height: 1.4;
}

.accuracy-info {
    color: #70757a;
    font-size: 11px;
    margin-top: 4px;
}

.border-status.inside {
    color: #1e8e3e;
    font-size: 11px;
    margin-top: 4px;
    font-weight: 500;
}

.border-status.outside {
    color: #ea4335;
    font-size: 11px;
    margin-top: 4px;
    font-weight: 500;
}

.border-popup {
    font-size: 13px;
    text-align: center;
}

/* Leaflet popup enhancements */
.leaflet-popup-content-wrapper {
    border-radius: 8px !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15) !important;
    border: 1px solid #e0e0e0 !important;
}

.leaflet-popup-tip {
    box-shadow: none !important;
}

.leaflet-popup-content {
    margin: 12px 16px !important;
    line-height: 1.4 !important;
}

/* Pulsing animation for marker */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(66, 133, 244, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(66, 133, 244, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(66, 133, 244, 0);
    }
}
</style>