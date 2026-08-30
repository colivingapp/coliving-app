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

    <div v-if="!isLoading && categoryDescription" style="margin-top:0px;margin-bottom:30px;color:#666;max-width:820px;line-height:1.5;">
      {{ categoryDescription }}
    </div>
    
    <div v-if="isLoading">
      Loading...
    </div>

    <div class="space-grid">
      <SpaceCard v-for="space in sortedSpaces" :key="space.id" :space="space" />
    </div>

    <div style="height:50px;"></div>
    
    <div style="text-align:center;">
        <router-link to="/space/new"><button type="button" class="btn btn-lg btn-primary">Add New Space</button></router-link>
    </div>
    
    <div style="height:30px;"></div>
  </div>
</template>

<script setup>
import "/node_modules/flag-icons/css/flag-icons.min.css";

import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import MapByCountry from '../map/MapByCountry.vue';
import SpaceCard from '../components/SpaceCard.vue';
import { getCategoryDescription } from '../spaceTaxonomy.js';

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
  'popup-temporary',
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

const categoryDescription = computed(() => {
  if (!categorySlug.value) return '';
  return getCategoryDescription(categorySlug.value);
});
</script>

<style>
  .img-fit-cover {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
  }
</style>


