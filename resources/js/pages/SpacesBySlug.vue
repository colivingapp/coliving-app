<template>
  <div class="content">
    <h1 v-if="!isLoading">
      <span v-if="mode === 'country'">
        <span v-if="spaces[0]" :class="'fi fi-'+spaces[0].country_code.toLowerCase()"></span>
        <span v-if="spaces[0]">&nbsp;&nbsp;</span>
        Spaces in {{ country }}
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
      <h2 v-if="mode === 'country'">Spaces in {{ country }}</h2>
      <h2 v-else-if="mode === 'category'">{{ category }} Spaces</h2>

      <div v-if="sortedSpaces.length === 0" style="margin-top:20px;">
        No spaces found.
      </div>

      <div v-for="space in sortedSpaces" :key="space.id">
        <router-link :to="'/space/'+space.uid" style="font-size:17px;vertical-align: middle;">
          <div class="widget-space">
            <div class="widget-space-inner">
              <img v-if="space.first_photo" :src="'/storage/spaces/'+space.first_photo" class="img-fit-cover">
              <img v-else style="height:70px;" src="/img/coliving-app-logo-symbol.png">
            </div>
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
  </div>
</template>

<script setup>
import "/node_modules/flag-icons/css/flag-icons.min.css";

import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import MapByCountry from '../map/MapByCountry.vue';

const route = useRoute();
const router = useRouter();

const isLoading = ref(true);
const mode = ref(''); // 'country' | 'category'
const country = ref('');
const category = ref('');
const spaces = ref([]);

const CATEGORY_LABELS = {
  'managed-coliving': 'Managed Coliving',
  'shared-housing': 'Shared Housing',
  'student-housing': 'Student Housing',
  'digital-nomad': 'Digital Nomad Coliving',
  'cohousing': 'Cohousing / Communal Living',
  'senior-coliving': 'Senior Coliving',
  'short-term': 'Short-Term Coliving',
};

function isCategorySlug(slug) {
  return Object.prototype.hasOwnProperty.call(CATEGORY_LABELS, slug);
}

async function fetchSpaces() {
  isLoading.value = true;
  country.value = '';
  category.value = '';
  spaces.value = [];

  const slug = route.params.countrySlug; // kept param name for backwards compatibility

  // If user lands on legacy /spaces/category/:category, routes.js will redirect here.

  try {
    if (isCategorySlug(slug)) {
      mode.value = 'category';
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
      document.title = `Spaces in ${country.value} - Coliving App`;
    }
  } catch (e) {
    mode.value = '';
  }

  isLoading.value = false;
}

onMounted(fetchSpaces);
watch(() => route.params.countrySlug, fetchSpaces);

const sortedSpaces = computed(() => {
  return (spaces.value || []).slice().sort((a, b) => (a.name || '').localeCompare(b.name || ''));
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
