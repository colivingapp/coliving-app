<template>
  <div style="margin:0 auto;text-align:center;">
    <div>
      <div style="margin: auto; width: 340px;">
        <input v-model="searchQuery" placeholder="Search..." 
               @input="showResults = searchQuery !== ''" 
               @blur="onBlur" 
               class="input-search" />
        <div v-if="showResults && spacesFiltered.length > 0" class="dropdown">
          <router-link v-for="space in spacesFiltered.slice(0, 3)" :to="'/space/'+space.uid">
            <div class="dropdown-item">
              {{ space.name }}, {{ space.country }}
            </div>
          </router-link>
          <div v-if="spacesFiltered.length > 0" class="dropdown-item link" style="text-align:center;" @click="showAll">All...</div>
        </div>
      </div>

      <div style="height:10px;"></div>
      <span style="font-size:12px;">Search by country, address or coliving space name</span>
      <!-- <div style="height:4px;"></div>
      <router-link to="/spaces">View all spaces</router-link> -->
      <div style="height:25px;"></div>
    </div> 
    <div style="height:calc(100vh - 300px);">
      <map-home :locations="locations" />
    </div>
    <div>
      <div style="height:30px;"></div>

      <router-link to="/space/new"><button class="action-button button-1">Add New Space</button></router-link>
      
      <div style="height:30px;"></div>

      <div class="bottom-arrow-div" @click="scrollToContent">
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"  xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
          viewBox="0 0 78.6 45.6" style="enable-background:new 0 0 78.6 45.6;width:22px;fill:#7e7e7e;" xml:space="preserve">
          <g>
            <path style="fill:#888888;" d="M76.8,1.8L76.8,1.8c1.2,1.2,1.8,2.9,1.8,4.5c0,1.6-0.6,3.2-1.8,4.5l-33,33l0,0c-0.6,0.6-1.3,1-2,1.4
              l0,0c-0.8,0.3-1.6,0.5-2.4,0.5c-0.8,0-1.6-0.2-2.4-0.5h0c-0.7-0.3-1.4-0.8-2-1.4l0,0l-33-33C0.6,9.5,0,7.9,0,6.3
              c0-1.6,0.6-3.2,1.9-4.4l0,0l0,0C3.1,0.6,4.7,0,6.3,0c1.6,0,3.2,0.6,4.4,1.8h0l28.5,28.5L67.9,1.8l0,0C69.1,0.6,70.7,0,72.3,0
              C73.9,0,75.5,0.6,76.8,1.8L76.8,1.8L76.8,1.8z"/>
          </g>
        </svg>
      </div>
    </div>
  </div>

  <div class="content" id="home-content" style="text-align:center;max-width:800px;">
    
    <div style="height:80px;"></div>

    
    <h1>Coliving Spaces</h1>

    Find your perfect coliving space with Coliving App, where you can discover a wide selection of spaces - from small apartments to big coliving communities all around the world. Add your space to Coliving App and start accepting join requests from coliving mates. It's free to use!

    <div style="height:20px;"></div>

    <img src="/img/landing-add.png" style="width:100%;max-width:330px;">


    <div style="height:100px;"></div>


    <h1>Coliving Mates</h1>
    
    Join a global network of coliving mates. Whether you're a digital nomad, remote worker, student, or just a traveler, create a free profile on Coliving App to showcase your visited countries, find coliving mates nearby, and connect with the coliving community.

    <div style="height:20px;"></div>

    <img src="/img/landing-global.jpg" style="width:100%;max-width:320px;">

    <div style="height:20px;"></div>

    
    <router-link v-if="!userLoggedIn" to="/register"><button class="action-button button-1">Create Profile</button></router-link>
  </div>
  <div style="height:110px;"></div>
</template>


<script setup>
  import { ref, computed, watch } from 'vue';
  import MapHome from './map/MapHome.vue';

  import { useRouter } from 'vue-router';
  const router = useRouter();

  const caUser = window.caUser;
  let userLoggedIn = window.userLoggedIn;

  const searchQuery = ref('');
  const showResults = ref(false);

  const spaces = ref(window.spaces || []);

  if (!window.spaces) {
    async function fetchSpaces() {
      try {
        const response = await axios.get('/api');
        // console.log(response.data.spaces)
        // spaces.value = response.data[0] || [];
        // console.log(spaces.value)
        spaces.value = response.data.spaces;
      } catch (error) {
        console.error(error);
      }
    }
    fetchSpaces();
  }

  const spacesFiltered = computed(() => {
    if (searchQuery.value && spaces.value) {
      return spaces.value.filter(space => {
        const { country, name, formatted_address } = space;
        const searchValue = searchQuery.value.toLowerCase();

        return (
          country.toLowerCase().includes(searchValue) ||
          name.toLowerCase().includes(searchValue) || 
          formatted_address.toLowerCase().includes(searchValue)
        );
      });
    } else {
      return spaces.value || [];
    }
  });

  let locations = ref([]);

  function updateLocations() {
    if (spaces.value) {
      locations.value = spacesFiltered.value.reduce((acc, space) => {
        if (space.latitude && space.longitude) {
          const { uid, name_slug, name, country_slug, country, latitude, longitude } = space;
          acc.push({ latitude, longitude, uid, name_slug, name, country_slug, country });
        }
        return acc;
      }, []);
    } else {
      locations.value = [];
    }
  }

  watch(spacesFiltered, updateLocations);

  if (spaces.value) {
    updateLocations();
  }

  function onBlur() {
    setTimeout(() => { showResults.value = false; }, 150);
  }

  function showAll() {
    router.push('/search/'+searchQuery.value);
  }

  function scrollToContent() {
    document.getElementById('home-content').scrollIntoView({ behavior: 'smooth' });
  }
</script>


<style>
  /* Search input on home page */
  .input-search {
    border: 1px solid #888!important;
    font-family: Montserrat,sans-serif;
    font-size: 20px;  
    border: 1px solid #87c9eb;
    border-radius: 50px;
    padding: 10px 20px;
  }

  .dropdown {
    border: 1px solid #ddd;
    max-height: 200px;
    overflow-y: auto;
    position: absolute;
    width: 340px;
    background: white;
    z-index: 1000;
    margin-top: 5px;
    border-radius: 10px;
    font-size: 14px;
    padding: 5px;
  }

  .dropdown a {
    text-decoration: none;
  }

  .dropdown-item {
    padding: 5px;
    cursor: pointer;
    text-align: left;
    font-size: 18px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .dropdown-item:hover {
    background-color: #87c9eb;
    color: white;
  }

  .more-results {
    text-align: center;
    padding: 5px;
    height: 41px;
    font-size: 22px;
    margin-top: -11px;
  }
</style>