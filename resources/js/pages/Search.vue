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

  const spacesFiltered = computed(() => {
    if (searchQuery.value && spaces.value.length > 0) {
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

  watch(spacesFiltered, (newVal) => {
    resultsCount.value = newVal.length;
  });
</script>
