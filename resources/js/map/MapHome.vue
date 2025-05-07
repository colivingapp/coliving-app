<template>
  <div class="map-container">
    <div ref="map" class="map">
      <div v-if="loading" class="loader">
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 96.7 96.7" style="enable-background:new 0 0 96.7 96.7;" xml:space="preserve">
          <g>
            <polygon class="st0" points="48.3,48.3 72.1,48.3 72.1,48.6 48.3,72.1 48.3,48.3 	"/>
            <polygon class="st1" points="48.3,72.1 48.6,72.2 74.9,78.3 48.3,89.6 48.3,72.1 	"/>
            <polygon class="st1" points="72.1,48.3 74.9,78.3 48.3,72.1 72.1,48.3 	"/>
            <polygon class="st1" points="72.1,48.3 89.8,48.3 74.9,78.3 72.1,48.3 	"/>
            <polygon class="st2" points="89.8,48.3 95.9,48.3 89.8,72 89.8,48.3 	"/>
            <polygon class="st2" points="74.9,78.3 89.8,72 89.8,48.3 74.9,78.3 	"/>
            <polygon class="st1" points="48.3,89.6 72.4,89.6 74.9,78.3 48.3,89.6 	"/>
            <polygon class="st2" points="74.9,78.3 89.8,72 72.4,89.6 74.9,78.3 	"/>
            <polygon class="st1" points="48.3,89.6 72.4,89.6 48.3,95.9 48.3,89.6 	"/>
            <polygon class="st1" points="48.3,89.6 36.8,89.6 28.3,89.6 24.5,89.6 48.3,95.9 48.3,89.6 	"/>
            <polygon class="st1" points="48.3,72.1 27.6,72.1 36.8,89.6 48.3,89.6 48.3,72.1 	"/>
            <polygon class="st1" points="27.6,72.1 12.7,72.1 28.3,89.6 36.8,89.6 27.6,72.1 	"/>
            <polygon class="st0" points="12.7,72.1 7,72.1 24.5,89.6 28.3,89.6 12.7,72.1 	"/>
            <polygon class="st1" points="48.3,48.3 24.5,48.3 27.6,72.1 48.3,72.1 48.3,48.3 	"/>
            <polygon class="st0" points="24.5,48.3 7,48.3 12.7,72.1 27.6,72.1 24.5,48.3 	"/>
            <polygon class="st2" points="7,48.3 0.8,48.3 7,72.1 12.7,72.1 7,48.3 	"/>
            <polygon class="st0" points="48.3,48.3 24.5,48.3 24.5,48.1 48.3,24.5 48.3,48.3 	"/>
            <polygon class="st0" points="48.3,24.5 48,24.5 21.8,18.3 48.3,7 48.3,24.5 	"/>
            <polygon class="st1" points="24.5,48.3 21.8,18.3 48.3,24.5 24.5,48.3 	"/>
            <polygon class="st1" points="24.5,48.3 6.8,48.3 21.8,18.3 24.5,48.3 	"/>
            <polygon class="st0" points="6.8,48.3 0.8,48.3 6.8,24.7 6.8,48.3 	"/>
            <polygon class="st2" points="21.8,18.3 6.8,24.7 6.8,48.3 21.8,18.3 	"/>
            <polygon class="st3" points="48.3,7 24.3,7 21.8,18.3 48.3,7 	"/>
            <polygon class="st2" points="21.8,18.3 6.8,24.7 24.3,7 21.8,18.3 	"/>
            <polygon class="st2" points="48.3,7 24.3,7 48.3,0.8 48.3,7 	"/>
            <polygon class="st1" points="48.3,7 59.8,7 68.4,7 72.2,7 48.3,0.8 48.3,7 	"/>
            <polygon class="st4" points="48.3,24.5 69.1,24.5 59.8,7 48.3,7 48.3,24.5 	"/>
            <polygon class="st4" points="69.1,24.5 84,24.5 68.4,7 59.8,7 69.1,24.5 	"/>
            <polygon class="st1" points="84,24.5 89.6,24.5 72.2,7 68.4,7 84,24.5 	"/>
            <polygon class="st4" points="48.3,48.3 72.1,48.3 69.1,24.5 48.3,24.5 48.3,48.3 	"/>
            <polygon class="st4" points="72.1,48.3 89.6,48.3 84,24.5 69.1,24.5 72.1,48.3 	"/>
            <polygon class="st1" points="89.6,48.3 95.9,48.3 89.6,24.5 84,24.5 89.6,48.3 	"/>
          </g>
        </svg>
      </div>
    </div>
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
      markerClusters: true,
      loading: true,
      initial: false,
    };
  },
  mounted() {
		vueVM = this;

    this.initializeMap();
    this.addMarkers();
    // this.fitMapBounds();
  },
  watch: {
    locations(newLocations) {
      this.clearMarkers();
      this.addMarkers();
      if (this.initial) {
        this.fitMapBounds();

      }
      this.initial = true;
    },
  },
  methods: {
    resetMap() {
      this.map.setView([0, 0], 2);
    },

    // initializeMap() {
    //   this.map = L.map(this.$refs.map, {
    //     maxBounds: [[-62, -180], [90, 180]],
    //     maxBoundsViscosity: 1.0,
    //     minZoom: 2,
    //     worldCopyJump: false,
    //   }).setView([0, 0], 2);

    //   L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //     attribution: '&copy; OpenStreetMap contributors',
    //     noWrap: true,
    //   }).addTo(this.map);

    //   L.control.zoom().addTo(this.map);
    //   L.control.attribution().addTo(this.map);

    //   this.map.scrollWheelZoom.enable();

    //   this.map.on('zoomend', this.closePopupsOnZoom);
    // },

    // initializeMap() {
    //   this.map = L.map(this.$refs.map, {
    //     maxBounds: [[-62, -180], [90, 180]],
    //     maxBoundsViscosity: 1.0,
    //     minZoom: 2,
    //     worldCopyJump: false,
    //   }).setView([0, 0], 2);

    //   const mapTiles = customTileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //     attribution: '&copy; OpenStreetMap contributors',
    //     noWrap: true,
    //   }).addTo(this.map);

    //   L.control.zoom().addTo(this.map);
    //   L.control.attribution().addTo(this.map);
    //   this.map.scrollWheelZoom.enable();
    //   this.map.on('zoomend', this.closePopupsOnZoom);
    // },

    // initializeMap() {
    //   this.map = L.map(this.$refs.map, {
    //     maxBounds: [[-62, -180], [90, 180]],
    //     maxBoundsViscosity: 1.0,
    //     minZoom: 2,
    //     worldCopyJump: false,
    //   }).setView([0, 0], 2);

    //   L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //     attribution: '&copy; OpenStreetMap contributors',
    //     noWrap: false,
    //   }).addTo(this.map);

    //   L.control.zoom().addTo(this.map);
    //   L.control.attribution().addTo(this.map);
    //   this.map.scrollWheelZoom.enable();

    //   // this.addAntarcticaOverlay();
    //   this.addOverlayRegions();
    // },

    initializeMap() {
      const isMobile = window.innerWidth < 680;

      // Default maxBounds for desktop
      let bounds = [[-62, -180], [90, 180]];
      this.map = L.map(this.$refs.map, {
        maxBounds: bounds,
        maxBoundsViscosity: 1.0,
        minZoom: 1,
        worldCopyJump: false,
      }).setView([0, 0], this.getInitialZoom()); // Set initial zoom dynamically

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        noWrap: false,
      }).addTo(this.map);

      L.control.zoom().addTo(this.map);
      L.control.attribution().addTo(this.map);
      this.map.scrollWheelZoom.enable();
      this.addOverlayRegions();

        // Adjust maxBounds for mobile
        if (isMobile) {
        bounds = [[-70, -180], [90, 180]]; // Example broader bounds for mobile
        this.map.setMaxBounds(bounds);
      }

      // Add an event listener to resize map zoom based on window size changes
      window.addEventListener('resize', this.handleResize);

        // Adjust the map center slightly after the map has been initialized
        this.$nextTick(() => {
        this.adjustMapCenter(0, 0, 0, -27); // Shifts the map 20 pixels up and 20 pixels to the left
      });
    },

    // Function to determine the initial zoom level based on window width
    getInitialZoom() {
      return window.innerWidth < 680 ? 1 : 2;
    },

    adjustMapCenter(lat, lng, pixelsUp, pixelsLeft) {
      // Convert the current center lat and lng to a point in pixels
      let centerPoint = this.map.latLngToContainerPoint([lat, lng]);
      
      // Adjust the point by the specified pixels (up is negative y, left is negative x)
      centerPoint = centerPoint.subtract([pixelsLeft, pixelsUp]);

      // Convert the adjusted point back to lat and lng
      const newCenterLatLng = this.map.containerPointToLatLng(centerPoint);
      
      // Set the map center to the new coordinates
      this.map.setView(newCenterLatLng, this.map.getZoom());
    },

    // Handle resize event
    handleResize() {
      const isMobile = window.innerWidth < 680;
      const newZoom = this.getInitialZoom();

      // Update zoom based on current window width
      this.map.setView([0, 0], newZoom);

      // Update maxBounds based on whether the view is considered mobile or not
      if (isMobile) {
        // Broader bounds for mobile
        this.map.setMaxBounds([[-70, -180], [90, 180]]);
      } else {
        // More restrictive bounds for desktop
        this.map.setMaxBounds([[-62, -180], [90, 180]]);
      }

      // If you need to adjust the map center slightly based on the device
      // This could be adapted to check if a centering adjustment is needed based on some other state variables
      this.adjustMapCenter(0, 0, 0, -40);
    },

    addOverlayRegions() {
      // Extended coordinates for covering the left and right map edges
      const leftEdgeCoords = [
        [90, -1080], [90, -180],
        [-90, -180], [-90, -1080]
      ];
      const rightEdgeCoords = [
        [90, 180], [90, 1080],
        [-90, 1080], [-90, 180]
      ];

      // Coordinates for covering Antarctica
      const antarcticaCoords = [
        [-60, -180], [-60, 180], [-90, 180], [-90, -180]
      ];

      // Add left edge overlay
      L.polygon(leftEdgeCoords, {
        color: "#aad3df",
        fillOpacity: 1,
        className: 'no-pointer-events' // Prevent interaction
      }).addTo(this.map);

      // Add right edge overlay
      L.polygon(rightEdgeCoords, {
        color: "#aad3df",
        fillOpacity: 1,
        className: 'no-pointer-events' // Prevent interaction
      }).addTo(this.map);

      // Add Antarctic overlay
      L.polygon(antarcticaCoords, {
        color: "#aad3df", // Matching the ocean color
        fillOpacity: 1,
        className: 'no-pointer-events' // Ensure no interaction
      }).addTo(this.map);
    },


    // addAntarcticaOverlay() {
    //   const antarcticaCoords = [[[-60, -180], [-60, 180], [-90, 180], [-90, -180]]];
    //   const antarcticaPolygon = L.polygon(antarcticaCoords, {
    //     color: "#aad3df",
    //     fillOpacity: 1,
    //     className: 'no-pointer-events'  // Apply a custom class for styling
    //   }).addTo(this.map);
    // },

    // addAntarcticaOverlay() {
    //   const antarcticaCoords = [[[-60, -180], [-60, 180], [-90, 180], [-90, -180]]];
    //   const antarcticaPolygon = L.polygon(antarcticaCoords, {color: "#aad3df", fillOpacity: 1}).addTo(this.map);
    // },

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
    fetchPopupContent(uid) {
      let self = this;
      return axios.get('/api/space/' + uid)
        .then(function (response) {

          let photo_url = '/img/coliving-app-logo-symbol.png';
          if (response.data.space.photo == 1) {
            console.log(response.data.space.first_photo)
            let selectedPhoto = response.data.space.first_photo;

            if (selectedPhoto) {
              photo_url = '/storage/spaces/' + selectedPhoto;
            }
          }

          let popupContentOrig = `
            <div class="overflow-dots">
              <a href="/space/${uid}">
                <img src="${photo_url}" style="width:100%;border-radius: 5px;">
                <div style="height:5px;"></div>
                <span style="font-size:14px;font-weight:bold;" >${response.data.space.name}</span>
              </a>
              <div style="height:5px;"></div>
              <a href="/spaces/${response.data.space.country_slug}" target="_blank">${response.data.space.country}</a>
            </div>
          `;

          let popupContent = `
            <div class="overflow-dots">
              <a href="/space/${uid}" onclick="vueVM3.navigateTo(event, '/space/${uid}')">
                <img src="${photo_url}" style="width:100%;border-radius: 5px;">
                <div style="height:5px;"></div>
                <span style="font-size:14px;font-weight:bold;" title="${response.data.space.name}">${response.data.space.name}</span>
              </a>
              <div style="height:5px;"></div>
              <a href="/spaces/${response.data.space.country_slug}" onclick="vueVM3.navigateTo(event, '/spaces/${response.data.space.country_slug}')">${response.data.space.country}</a>
            </div>
          `;
          return popupContent;
        })
        .catch(function (error) {
          console.error(error);
          throw error; // Rethrow the error to be caught by the calling function
        });
    },
    addMarkers() {
      this.loading = true; 
      this.markerClusterGroup = L.markerClusterGroup({
        maxClusterRadius: (zoom) => {
          return (zoom > 7) ? 0 : 80;
        },
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

        let marker = null;
        if (!this.markerClusters) {
          marker = L.marker([latitude, longitude], { icon: markerIcon, uid: uid }).addTo(this.map);
        }
        else {
          marker = L.marker([latitude, longitude], { icon: markerIcon, uid: uid });
        }

        // let popupContent = `
        //   <div class="overflow-dots">
        //     <a href="/space/${uid}" target="_blank">
        //       <img src="/storage/spaces/${this.slugify(name)}-photo-main.jpg" style="width:100%;border-radius: 5px;">
        //       <div style="height:5px;"></div>
        //       <span style="font-size:14px;font-weight:bold;" >${name}</span>
        //     </a>
        //     <div style="height:5px;"></div>
        //     <a href="/spaces/${this.slugify(country)}" target="_blank">${country}</a>
        //   </div>
        // `;
        // marker.bindPopup(popupContent);

        // Create an empty popup for each marker
        const popup = L.popup({ maxWidth: 400 });

        // Bind the popup to the marker
        marker.bindPopup(popup);

        // Define a function to fetch the popup content from the backend
        const fetchPopupContent = async () => {
          try {
            let uid = marker.options.uid;
            const data = await this.fetchPopupContent(uid);

            // Update the popup content with the fetched data
            popup.setContent(data);
          } catch (error) {
            console.error('Error fetching popup content:', error);
          }
        };

        marker.on('popupopen', () => {
          // const markerUid = marker.options.uid;
          fetchPopupContent();
        });
        
        if (this.markerClusters) {
          this.markerClusterGroup.addLayer(marker);
        }
        this.markers.push(marker);
        this.loading = false;
      });

      if (this.markerClusters) {
        this.map.addLayer(this.markerClusterGroup);
      }
    },
    clearMarkers() {
      if (this.markerClusters) {
        this.markerClusterGroup.clearLayers();
      }
      else {
        this.markers.forEach(marker => {
          marker.removeFrom(this.map);
        });
      }
      this.markers = [];
    },
    fitMapBounds() {
      if (this.markers.length > 0) {
        const markerBounds = L.featureGroup(this.markers).getBounds();
        this.map.fitBounds(markerBounds, { padding: [50, 50] });
      }
      // if (this.locations.length > 0) {
      //   const bounds = this.locations.map(location => [location.latitude, location.longitude]);
      //   // this.map.fitBounds(bounds, { paddingTopLeft: [0, 65], paddingBottomRight: [0, 0] });
      // }
    },
    closePopupsOnZoom() {
      this.markers.forEach(marker => {
        marker.closePopup();
      });
    },
  },
};
</script>

<style>
.map {
  height: 100%;
  position:relative;
}

.map-container {
  /* height: 60vh; */
  height:100%!important;
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

.loader {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 150px;
  height: 150px;
  transform: translate(-50%, -50%);
  z-index: 999;
}

.st0{fill-rule:evenodd;clip-rule:evenodd;fill:#66B4B4;stroke:#FFFFFF;stroke-width:1.5001;stroke-linejoin:round;stroke-miterlimit:22.9256;}
        
.st1{fill-rule:evenodd;clip-rule:evenodd;fill:#A1D1D1;stroke:#FFFFFF;stroke-width:1.5001;stroke-linejoin:round;stroke-miterlimit:22.9256;}

.st2{fill-rule:evenodd;clip-rule:evenodd;fill:#CCE6E6;stroke:#FFFFFF;stroke-width:1.5001;stroke-linejoin:round;stroke-miterlimit:22.9256;}

.st3{fill-rule:evenodd;clip-rule:evenodd;fill:#99CDCD;stroke:#FFFFFF;stroke-width:1.5001;stroke-linejoin:round;stroke-miterlimit:22.9256;}

.st4{fill-rule:evenodd;clip-rule:evenodd;fill:#78BDBD;stroke:#FFFFFF;stroke-width:1.5001;stroke-linejoin:round;stroke-miterlimit:22.9256;}
   
@keyframes colorChange1 {
    0%, 20% { fill: #66B4B4; }
    40%, 60% { fill: #005959; }
    80%, 100% { fill: #66B4B4; }
}

@keyframes colorChange2 {
    0%, 20% { fill: #A1D1D1; }
    40%, 60% { fill: #006666; }
    80%, 100% { fill: #A1D1D1; }
}

@keyframes colorChange3 {
    0%, 20% { fill: #CCE6E6; }
    40%, 60% { fill: #008080; }
    80%, 100% { fill: #CCE6E6; }
}

@keyframes colorChange4 {
    0%, 20% { fill: #99CDCD; }
    40%, 60% { fill: #007575; }
    80%, 100% { fill: #99CDCD; }
}

@keyframes colorChange5 {
    0%, 20% { fill: #78BDBD; }
    40%, 60% { fill: #005050; }
    80%, 100% { fill: #78BDBD; }
}

.st0 {
    animation: colorChange1 1s steps(1, end) infinite;
    animation-delay: 0s;
}

.st1 {
    animation: colorChange2 1s steps(1, end) infinite;
    animation-delay: 0.2s;
}

.st2 {
    animation: colorChange3 1s steps(1, end) infinite;
    animation-delay: 0.4s;
}

.st3 {
    animation: colorChange4 1s steps(1, end) infinite;
    animation-delay: 0.6s;
}

.st4 {
    animation: colorChange5 1s steps(1, end) infinite;
    animation-delay: 0.8s;
}

.leaflet-pane {
  z-index:1!important;
}

.leaflet-top {
  z-index:2!important;
}

.no-pointer-events {
  pointer-events: none;
  cursor: grab;
}
</style>
