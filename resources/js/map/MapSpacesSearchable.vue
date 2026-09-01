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
    };
  },
  mounted() {
    this.initializeMap();
    this.$nextTick(() => {
      this.invalidateMapSize();
      this.syncMarker(this.location);
    });
    window.addEventListener('resize', this.invalidateMapSize);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.invalidateMapSize);
    if (this.map) {
      this.map.remove();
      this.map = null;
    }
    this.marker = null;
  },
  methods: {
    parseCoord(value) {
      if (value === '' || value === null || value === undefined) {
        return null;
      }
      const n = Number(value);
      return Number.isFinite(n) ? n : null;
    },
    coordsFrom(loc) {
      if (!loc) {
        return null;
      }
      const latitude = this.parseCoord(loc.latitude);
      const longitude = this.parseCoord(loc.longitude);
      if (latitude === null || longitude === null) {
        return null;
      }
      if (Math.abs(latitude) > 90 || Math.abs(longitude) > 180) {
        return null;
      }
      return { latitude, longitude };
    },
    sameCoords(a, b) {
      return !!(a && b && a.latitude === b.latitude && a.longitude === b.longitude);
    },
    initializeMap() {
      this.map = L.map(this.$refs.map, {
        maxBounds: [[-63, -180], [90, 180]],
        maxBoundsViscosity: 1.0,
        minZoom: 2,
        worldCopyJump: false,
        zoomControl: false,
      }).setView([20, 0], 2);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        noWrap: true,
      }).addTo(this.map);

      L.control.zoom().addTo(this.map);
      this.map.scrollWheelZoom.enable();
    },
    invalidateMapSize() {
      if (this.map) {
        this.map.invalidateSize();
      }
    },
    markerIcon() {
      return L.icon({
        iconUrl: '/img/leaflet/marker-icon.png',
        iconRetinaUrl: '/img/leaflet/marker-icon-2x.png',
        shadowUrl: '/img/leaflet/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        tooltipAnchor: [16, -28],
        shadowSize: [41, 41]
      });
    },
    syncMarker(loc) {
      if (!this.map) {
        return;
      }

      const coords = this.coordsFrom(loc);
      if (!coords) {
        return;
      }

      if (this.marker) {
        this.marker.setLatLng([coords.latitude, coords.longitude]);
      } else {
        this.marker = L.marker([coords.latitude, coords.longitude], { icon: this.markerIcon() }).addTo(this.map);
      }

      this.map.setView([coords.latitude, coords.longitude], 15);
      this.invalidateMapSize();
    },
    setAddress(address) {
      this.$emit('addressUpdate', address);
    }
  },
  watch: {
    location(loc, prev) {
      if (this.sameCoords(this.coordsFrom(loc), this.coordsFrom(prev))) {
        return;
      }
      this.syncMarker(loc);
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
