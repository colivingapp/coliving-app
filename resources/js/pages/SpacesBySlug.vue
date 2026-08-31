<template>
  <div class="content">
    <h1 v-if="!isLoading">
      <span v-if="mode === 'country'">
        <span v-if="spaces[0]" :class="'fi fi-'+spaces[0].country_code.toLowerCase()"></span>
        <span v-if="spaces[0]">&nbsp;&nbsp;</span>
        Coliving Spaces in {{ country }}
      </span>
      <span v-else-if="mode === 'category'">
        {{ category }} Spaces
      </span>
      <span v-else>
        Not found
      </span>
    </h1>
    <h1 v-else>
      Please wait...
    </h1>

    <div style="height:10px;"></div>

    <map-by-country :locations="isLoading ? [] : sortedSpaces" />

    <div style="height:10px;"></div>

    <h2 v-if="isLoading" style="font-weight: normal;">Loading...</h2>
    <div v-else>
      <h2 v-if="mode === 'country'">Coliving Spaces in {{ country }}</h2>
      <h2 v-else-if="mode === 'category'">{{ category }} Spaces</h2>

      <div v-if="mode === 'category' && categoryDescription" style="margin-top:-10px;margin-bottom:30px;color:#666;max-width:820px;line-height:1.5;">
        {{ categoryDescription }}
      </div>

      <div v-if="sortedSpaces.length === 0" style="margin-top:20px;">
        No spaces found.
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
  </div>
</template>

<script setup>
import "/node_modules/flag-icons/css/flag-icons.min.css";

import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import MapByCountry from '../map/MapByCountry.vue';
import SpaceCard from '../components/SpaceCard.vue';
import { CATEGORY_META, getCategoryDescription } from '../spaceTaxonomy.js';

const route = useRoute();
const router = useRouter();

const isLoading = ref(true);
const mode = ref(''); // 'country' | 'category'
const country = ref('');
const category = ref('');
const categorySlug = ref('');
const spaces = ref([]);

function isCategorySlug(slug) {
  return Object.prototype.hasOwnProperty.call(CATEGORY_META, slug);
}

async function fetchSpaces() {
  isLoading.value = true;
  country.value = '';
  category.value = '';
  categorySlug.value = '';
  spaces.value = [];

  const slug = route.params.countrySlug; // kept param name for backwards compatibility

  // If user lands on legacy /spaces/category/:category, routes.js will redirect here.

  try {
    if (isCategorySlug(slug)) {
      mode.value = 'category';
      categorySlug.value = slug;
      const response = await fetch(`/api/spaces/category/${slug}`);
      if (!response.ok) {
        mode.value = '';
        isLoading.value = false;
        return;
      }
      const data = await response.json();
      category.value = data.category;
      spaces.value = data.spaces;
      document.title = `${category.value} Spaces - Coliving App`;
    } else {
      mode.value = 'country';
      const response = await fetch(`/api/spaces/${slug}`);
      if (!response.ok) {
        // Might be an unknown slug. Show not found state.
        mode.value = '';
        isLoading.value = false;
        return;
      }
      const data = await response.json();
      country.value = data.country;
      spaces.value = data.spaces;
      document.title = `Coliving Spaces in ${country.value} - Coliving App`;
    }
  } catch (e) {
    mode.value = '';
  }

  isLoading.value = false;
}

onMounted(fetchSpaces);
watch(() => route.params.countrySlug, fetchSpaces);

const categoryDescription = computed(() => {
  if (mode.value !== 'category' || !categorySlug.value) return '';
  return getCategoryDescription(categorySlug.value);
});

const sortedSpaces = computed(() => {
  return (spaces.value || []).slice().sort((a, b) => (a.name || '').localeCompare(b.name || ''));
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
