<template>
  <div class="map-container">
    <div ref="map" class="map"></div>
  </div>
</template>

<script>
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-control-geocoder/dist/Control.Geocoder.css';
import 'leaflet-control-geocoder/dist/Control.Geocoder.js';

export default {
  name: 'MapComponent',
  props: [ 'location', 'address' ],
  data() {
    return {
      map: null,
      marker: null,

      longitude: '',
      latitude: '',
      searchQuery: '',

      searchResults: null,
      initial: true,
    };
  },
  mounted() {
    console.log(this.location)
    this.longitude = this.location.longitude;
    this.latitude = this.location.latitude;

    this.initializeMap();
    this.addMarkers();
  },
  methods: {
    initializeMap() {
      this.map = L.map(this.$refs.map, {
        maxBounds: [[-63, -180], [90, 180]],
        maxBoundsViscosity: 1.0,
        minZoom: 2,
        worldCopyJump: false,
      }).setView([0, 0], 2);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        noWrap: true,
      }).addTo(this.map);

      L.control.zoom().addTo(this.map);
      L.control.attribution().addTo(this.map);

      this.map.scrollWheelZoom.enable();
    },
    
    addMarkers() {
      const markerIcon = L.icon({
        iconUrl: '/img/leaflet/marker-icon.png',
        iconRetinaUrl: '/img/leaflet/marker-icon-2x.png',
        shadowUrl: '/img/leaflet/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        tooltipAnchor: [16, -28],
        shadowSize: [41, 41]
      });

      this.marker = L.marker([this.latitude, this.longitude], { icon: markerIcon }).addTo(this.map);
      
      this.map.setView([this.latitude, this.longitude], 15);
    },
    
    clearMarkers() {
      this.marker.removeFrom(this.map);
    },

    setAddress(address) {
      console.log(address);
      this.$emit('addressUpdate', address);
    }
  },
  
  watch: {
    location(loc) {
      this.longitude = loc.longitude;
      this.latitude = loc.latitude;

      this.clearMarkers();
      // this.initializeMap();
      this.addMarkers();
    },
  },
};
</script>

<style scoped>
.map {
  height: 100%;
}

.map-container {
  height: 400px!important;
}

.leaflet-container {
  background: #AAD3DF;
}

.leaflet-popup {
  width: 220px;
}

.marker-cluster-custom {
  background-color: lightblue;
  color: white;
  border: none;
  border-radius: 50%;
}

.marker-cluster-custom div {
  width: 30px;
  height: 30px;
  margin-top: 5px;
  margin-left: 5px;
  background: rgb(0, 137, 205);
  border-radius: 50%;
  text-align: center;
}

.marker-cluster-custom span {
  font-size: 14px;
  font-weight: bold;
  line-height: 30px;
}

.address-search-result {
  cursor: pointer;
}

.address-search-result:hover {
  font-weight: bold;
}
</style>
