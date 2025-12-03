<template>
    <div class="content">
        <h1>Search</h1>
        Search term: <b>{{  decodeURI($route.params.searchTerm) }}</b>
        <br>
        Results: <span v-if="isLoading">Loading...</span><span v-else>{{ resultsCount }}</span>
        <div style="height:20px;"></div>
        
        <map-by-country :locations="spacesFiltered"  />
        
        <div style="height:20px;"></div>

        <div>
          <router-link v-for="space in spacesFiltered" :to="'/space/'+space.uid">
            <div class="dropdown-item">
              {{ space.name }}, {{ space.country }}
            </div>
          </router-link>
        </div>
    </div>
</template>

<script setup>
  import { ref, computed, watch } from 'vue';
  import { useRoute } from 'vue-router';
  const route = useRoute();

  import MapByCountry from '../map/MapByCountry.vue';

  // let userLoggedIn = ref(window.userLoggedIn);
  const spaces = ref(window.spaces || []);
  const resultsCount = ref("");

  let isLoading = ref(true);

  if (window.spaces) {
    console.log(window.spaces);
  } else {
    axios.get('/api/search')
    .then(resp => {
      spaces.value = resp.data.spaces;
      isLoading.value = false;
    })
    .catch(err => console.log(err))
  }

  const searchQuery = ref(decodeURI(route.params.searchTerm));

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
    if (searchQuery.value && spaces.value.length > 0 && searchQuery.value.length >= 2) {
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

  watch(spacesFiltered, (newVal) => {
    resultsCount.value = newVal.length;
    isLoading.value = false;
  });
</script>
