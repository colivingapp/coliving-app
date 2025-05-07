<template>
  <div class="map-container">
    <div ref="map" class="map"></div>
  </div>
</template>

<script>
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import { MarkerClusterGroup } from 'leaflet.markercluster';

export default {
  name: 'MapComponent',
  props: {
    locations: {
      type: Array,
      required: true
    },
  },
  data() {
    return {
      map: null,
      markers: [],
      markerClusterGroup: null,
    };
  },
  mounted() {
    this.initializeMap();
    this.addMarkers();
    this.fitMapBounds();
  },
  watch: {
    locations(newLocations) {
      this.clearMarkers();
      this.addMarkers();
      this.fitMapBounds();
    },
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

      this.map.on('zoomend', this.closePopupsOnZoom);
    },
    slugify(text) {
      // replace non letter or digits by -
      text = text.replace(/[^a-zA-Z0-9]+/g, '-');

      // transliterate
      text = text
          .normalize('NFD')
          .replace(/[\u0300-\u036f]/g, '')
          .replace(/([^\u0000-\u007F]+)/g, '')
          .replace(/[^\w-]+/g, '');

      // trim
      text = text.trim('-');

      // remove duplicated - symbols
      text = text.replace(/-+/g, '-');

      // lowercase
      text = text.toLowerCase();

      if (text === '') {
          return 'n-a';
      }

      return text;
    },
    addMarkers() {
      this.markerClusterGroup = L.markerClusterGroup({
        iconCreateFunction: function(cluster) {
          return L.divIcon({
            html: '<div><span>' + cluster.getChildCount() + '</span></div>',
            className: 'marker-cluster-custom',
            iconSize: L.point(40, 40),
          });
        },
      });

      this.locations.forEach(location => {
        const { uid, name, country, latitude, longitude } = location;

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

        const marker = L.marker([latitude, longitude], { icon: markerIcon });

        // let popupContent = `
        //   <div class="overflow-dots">
        //     <a href="/spaces/${this.slugify(country)}/${uid}" target="_blank">
        //       <img src="/storage/spaces/${this.slugify(name)}-photo-main.jpg" style="width:100%;border-radius: 5px;">
        //       <div style="height:5px;"></div>
        //       <span style="font-size:14px;font-weight:bold;" >${name}</span>
        //     </a>
        //     <div style="height:5px;"></div>
        //     <a href="/spaces/${this.slugify(country)}" target="_blank">${country}</a>
        //   </div>
        // `;
        // marker.bindPopup(popupContent);

        this.markerClusterGroup.addLayer(marker);
        this.markers.push(marker);
      });

      this.map.addLayer(this.markerClusterGroup);
    },
    clearMarkers() {
      if (this.markerClusterGroup) {
        this.markerClusterGroup.clearLayers();
      }
      this.markers = [];
    },
    fitMapBounds() {
      if (this.locations.length > 0) {
        const bounds = this.locations.map(location => [location.latitude, location.longitude]);
        this.map.fitBounds(bounds, { paddingTopLeft: [0, 65], paddingBottomRight: [0, 0] });
      }
    },
    closePopupsOnZoom() {
      this.markers.forEach(marker => {
        marker.closePopup();
      });
    },
  },
};
</script>

<style scoped>
.map {
  height: 100%;
  border-radius: 5px;
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
</style>
