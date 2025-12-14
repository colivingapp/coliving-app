<template>
  <div class="content">
    <h1 v-if="spaces[0] && !isLoading">
      <span :class="'fi fi-'+spaces[0].country_code.toLowerCase()"></span>&nbsp;&nbsp;{{ category }} in {{ country }}
    </h1>
    <h1 v-else>
      Please wait...
    </h1>

    <div style="height:10px;"></div> 
    <map-by-country :locations="sortedSpaces" />
    <div style="height:10px;"></div> 

    <h2>{{ category }} in {{ country }}</h2>
    
    <div v-if="isLoading">
      Loading...
    </div>

    <div v-for="space in sortedSpaces" :key="space.id">
      <router-link :to="'/space/'+space.uid" style="font-size:17px;vertical-align: middle;">
        <div class="widget-space">
          <div class="widget-space-inner"><img v-if="space.first_photo" :src="'/storage/spaces/'+space.first_photo" class="img-fit-cover"><img v-else style="height:70px;" src="/img/coliving-app-logo-symbol.png"></div>
          <div></div>
          <div>
            <b>{{ space.name }}</b>
            <br>
            <span style="color:rgb(89, 89, 89);font-size:14px;">{{ space.address }}</span>
          </div>
        </div>
      </router-link>
      
      <div style="height:10px;"></div>
    </div>

    <div style="height:50px;"></div>
    
    <div style="text-align:center;">
      <router-link to="/space/new"><button class="action-button button-1">Add New Space</button></router-link>
    </div>
    
    <div style="height:30px;"></div>
  </div>
</template>

<script setup>
import "/node_modules/flag-icons/css/flag-icons.min.css";

import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import MapByCountry from '../map/MapByCountry.vue';

const country = ref('');
const category = ref('');
const categorySlug = ref('');
const spaces = ref([]);
let isLoading = ref(true);

const CATEGORY_SLUGS = new Set([
  'managed-coliving',
  'shared-housing',
  'student-housing',
  'digital-nomad',
  'cohousing',
  'senior-coliving',
  'short-term',
]);

const fetchSpaces = async () => {
  const route = useRoute();
  const router = useRouter();
  const countrySlug = route.params.country;
  const categoryParam = route.params.category;

  // If user hits non-canonical /spaces/{category}/{country}, redirect to /spaces/{country}/{category}
  if (CATEGORY_SLUGS.has(countrySlug) && !CATEGORY_SLUGS.has(categoryParam)) {
    router.replace(`/spaces/${categoryParam}/${countrySlug}`);
    return;
  }

  const response = await fetch(`/api/spaces/${countrySlug}/category/${categoryParam}`);
  if (!response.ok) {
    window.location.href = '/404';
    return;
  }
  const data = await response.json();

  isLoading.value = false;
  country.value = data.country;
  category.value = data.category;
  categorySlug.value = data.categorySlug;
  spaces.value = data.spaces;

  document.title = category.value + ' in ' + country.value + ' - Coliving App';
};

onMounted(fetchSpaces);

const sortedSpaces = computed(() => {
  return spaces.value.sort((a, b) => a.name.localeCompare(b.name));
});
</script>

<style>
  .widget-space {
    clear:both;margin-top:10px;padding:10px;border:1px solid#aeaeae;border-radius:5px;min-height:90px;display:grid;grid-template-columns:90px 15px 1fr;
  }

  .widget-space-inner {
    width:90px;height:70px;overflow:hidden;text-align:center;
  }

  .img-fit-cover {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
  }
</style>


