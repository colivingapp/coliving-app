<template>
  <div style="margin:0 auto;text-align:center;">
    <div>
      <div style="margin: auto; width: 340px;">
        <input v-model="searchQuery" placeholder="Search..." 
               @input="showResults = searchQuery.length >= 2" 
               @blur="onBlur" 
               class="input-search" />
        <div v-if="showResults && searchQuery.length >= 2 && spacesFiltered.length > 0" class="dropdown">
          <router-link v-for="space in spacesFiltered.slice(0, 3)" :to="'/space/'+space.uid">
            <div class="dropdown-item">
              {{ space.name }}, {{ space.country }}
            </div>
          </router-link>
          <div v-if="spacesFiltered.length > 0" class="dropdown-item link" style="text-align:center;" @click="showAll">All...</div>
        </div>
      </div>

      <div style="height:10px;"></div>
      <span style="font-size:12px;">Search by country, city, address, or space name</span>
      <!-- <div style="height:4px;"></div>
      <router-link to="/spaces">View all spaces</router-link> -->
      <div style="height:25px;"></div>
    </div> 
    <div style="height:calc(100vh - 300px);">
      <map-home :locations="locations" />
    </div>
    <div>
      <div style="height:30px;"></div>
      <router-link to="/space/new"><button class="action-button button-1">Add a Space</button></router-link>
      
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
    
    <div style="height:50px;"></div>

    <h1 style="margin-bottom:10px;">Open Coliving</h1>
    <div style="line-height:1.75;">
      Coliving isn’t just a trendy label for branded buildings. It’s the broader idea of people living together - sharing a home, lowering costs, making connections, and building community.
      <br><br>
      Coliving App is an open-source platform for shared living worldwide: coliving businesses, community houses, private rooms in shared apartments, student housing, communes, cohousing, retirement communities, and other alternative communal living arrangements.
    </div>
    <div style="height:18px;"></div>
    <div>
      <router-link to="/spaces"><button class="action-button button-2" style="margin-right:8px;">Explore Spaces</button></router-link>
    </div>

    <div style="height:70px;"></div>
    
    <h1 style="margin-bottom:10px;">More than a directory</h1>

    Coliving App starts as a discovery tool, but it’s designed to go further:
    <div style="height:14px;"></div>
    <div style="max-width:720px;margin:0 auto;line-height:1.75;">
      <div style="padding:10px 14px;margin-bottom:10px;border:1px solid #e5e5e5;border-radius:10px;background:#fafafa;">
        <b>1) Find spaces</b><br>
        Explore shared living around the world: rooms, shared apartments, student housing, coliving spaces, community houses, cohousing, communes, retirement communities, and more.
      </div>
      <div style="padding:10px 14px;margin-bottom:10px;border:1px solid #e5e5e5;border-radius:10px;background:#fafafa;">
        <b>2) Join spaces</b><br>
        Request to join and become part of a home. Coliving App doesn’t manage payments - hosts and members handle arrangements directly.
      </div>
      <div style="padding:10px 14px;border:1px solid #e5e5e5;border-radius:10px;background:#fafafa;">
        <b>3) Use the Space Hub</b><br>
        Once you’re in, use the Space Hub to stay connected: shared space info, members list, and group chat - with more community features coming based on what spaces actually need.
      </div>
    </div>


    <div style="height:70px;"></div>

    <h1 style="margin-bottom:10px;">Why “open coliving”?</h1>
    <div style="line-height:1.7;">
      “Coliving” should describe a way of living, not just a brand category. Coliving App embraces the broader meaning - from shared flats and community houses to cohousing and intentional communities.
      Being <b>open source</b> keeps the spirit aligned: transparent, community-driven, and built for the movement, not only for listings.
    </div>

    <div style="height:16px;"></div>
    <div style="max-width:780px;margin:0 auto;">
      <a href="https://github.com/colivingapp/coliving-app" target="_blank" rel="noopener">Contribute on GitHub →</a>
    </div>

    <div style="height:70px;"></div>


    <h1 style="margin-bottom:10px;">People & Community</h1>
    
    Create a free profile to connect with others, join spaces, and build the kind of home you want: collaborative, affordable, social, quiet, or anywhere in between.

    <div style="height:20px;"></div>

    
    <router-link v-if="!userLoggedIn" to="/register"><button class="action-button button-1">Create Profile</button></router-link>

    <div style="height:70px;"></div>

    <h1 style="margin-bottom:10px;">For hosts</h1>
    <div style="line-height:1.7;">
      If you host a space - or you’re starting one - add it to the map, accept join requests, and grow a real community around your home. Your space can be as simple as a room in an apartment or as big as a coliving house, cooperative, or commune.
    </div>
    <div style="height:18px;"></div>
    <router-link v-if="!userLoggedIn" to="/register"><button class="action-button button-2" style="margin-left:8px;">Create Profile</button></router-link>
    <router-link v-else to="/space/new"><button class="action-button button-1">Add a Space</button></router-link>

    <div style="height:70px;"></div>

    <h1 style="margin-bottom:10px;">For members</h1>
    <div style="line-height:1.7;">
      Explore spaces, bookmark what you like, message hosts, and join spaces. Once you’re in, the Space Hub gives you shared info, a members list, and a space chat.
    </div>
    <div style="height:18px;"></div>
    <router-link to="/hub"><button class="action-button button-2">Explore the Hub</button></router-link>
  </div>
  <div style="height:130px;"></div>
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

  // Improved search function with relevance scoring
  function calculateRelevanceScore(space, searchValue) {
    const { country, name, formatted_address, city } = space;
    const searchLower = searchValue.toLowerCase();
    let score = 0;

    // Normalize fields
    const nameLower = (name || '').toLowerCase();
    const countryLower = (country || '').toLowerCase();
    const cityLower = (city || '').toLowerCase();
    const addressLower = (formatted_address || '').toLowerCase();

    // Exact name match (highest priority)
    if (nameLower === searchLower) {
      score += 1000;
    }
    // Name starts with query
    else if (nameLower.startsWith(searchLower)) {
      score += 500;
    }
    // Word boundary match in name (whole word)
    else if (new RegExp(`\\b${searchLower.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'i').test(nameLower)) {
      score += 400;
    }
    // Name contains query
    else if (nameLower.includes(searchLower)) {
      score += 300;
    }

    // City exact match
    if (cityLower === searchLower) {
      score += 200;
    }
    // City starts with query
    else if (cityLower.startsWith(searchLower)) {
      score += 150;
    }
    // Word boundary match in city
    else if (cityLower && new RegExp(`\\b${searchLower.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'i').test(cityLower)) {
      score += 100;
    }
    // City contains query
    else if (cityLower.includes(searchLower)) {
      score += 50;
    }

    // Country exact match
    if (countryLower === searchLower) {
      score += 100;
    }
    // Country starts with query
    else if (countryLower.startsWith(searchLower)) {
      score += 75;
    }
    // Word boundary match in country
    else if (new RegExp(`\\b${searchLower.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'i').test(countryLower)) {
      score += 50;
    }
    // Country contains query
    else if (countryLower.includes(searchLower)) {
      score += 25;
    }

    // Address contains query (lowest priority)
    if (addressLower.includes(searchLower)) {
      score += 10;
    }

    return score;
  }

  const spacesFiltered = computed(() => {
    if (searchQuery.value && spaces.value && searchQuery.value.length >= 2) {
      const searchValue = searchQuery.value.trim();
      if (!searchValue) {
        return spaces.value || [];
      }

      // Filter and score results
      const results = spaces.value
        .map(space => {
          const score = calculateRelevanceScore(space, searchValue);
          return { space, score };
        })
        .filter(item => item.score > 0) // Only include matches
        .sort((a, b) => b.score - a.score) // Sort by relevance (highest first)
        .map(item => item.space); // Extract just the space objects

      return results;
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