<template>
  <div class="content">

    <h1>Coliving Spaces</h1>

    <div>
      <div>

        <div class="my-spaces-popup-mob">
          <h2 style="margin-top:0px;">My Spaces</h2>
          Create a new coliving space or manage your existing ones in My Spaces

          <div style="height:10px;"></div>

          <router-link to="/space/new"><button class="button">Add New Space</button></router-link>
          <br>
          <router-link to="/my-spaces"><button class="button">My Spaces</button></router-link>

          <div style="height:10px;"></div>
        </div>

        <div style="max-width:621px;float:left;">
          <h2 class="mobile-only-h2">Coliving Spaces by Country</h2>
          Discover your perfect blend of community, comfort, and connection as you explore coliving spaces on Coliving App. Whether you seek the vibrant streets of Brazil or the serene landscapes of Sweden, embark on a journey of shared moments and unforgettable memories in the coliving spaces that resonate with you.
          <br><br>
          Countries: {{ countriesCount }}
          <br>
          Cities: {{ citiesCount }}
          <br>
          Spaces: {{ spacesCount }}
          <div style="height:30px;"></div>
          <div v-if="!isLoading" v-for="country in sortedCountries" :key="country.country_slug" style="line-height:40px;font-size:17px;">
            <router-link v-if="country.country_slug != 'n-a'" :to="`/spaces/${country.country_slug}`">
              <span :class="'fi fi-'+country.country_code.toLowerCase()"></span>&nbsp;&nbsp;{{ country.country }}&nbsp;<span style="color:#999;">({{ country.count }})</span>
            </router-link>
          </div>
          <div v-else style="min-height:500px;">
            Loading...
          </div>
        </div>

        <div class="my-spaces-popup">
          <h2 style="margin-top:0px;">My Spaces</h2>
          Create a new coliving space or manage your existing ones in My Spaces

          <div style="height:10px;"></div>

          <router-link to="/space/new"><button class="button">Add New Space</button></router-link>
          <br>
          <router-link to="/my-spaces"><button class="button">My Spaces</button></router-link>

          <div style="height:10px;"></div>
        </div>

        <div class="newest-space-popup" v-if="latestSpace != ''">
          <h2 style="margin-top:0px;">Newest Space</h2>
          <div style="padding-left:30px;padding-right:30px;padding-bottom:5px;">
            <router-link :to="'/space/'+latestSpace['uid']">
              <img :src="'/storage/spaces/'+latestSpace['space_photo']" style="max-width:100%;border-radius:5px;">
            </router-link>
          </div>
          <router-link :to="'/space/'+latestSpace['uid']"><b>{{ latestSpace['name'] }}</b></router-link>
          <div style="height:5px;"></div>
          <router-link :to="'/spaces/'+latestSpace['country_slug']"><span :class="'fi fi-'+latestSpace['country_code'].toLowerCase()"></span> {{ latestSpace['country'] }}</router-link>
          <div style="height:10px;"></div>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import "/node_modules/flag-icons/css/flag-icons.min.css";

import { ref, computed } from 'vue';

const countries = ref([]);

let spacesCount = ref(0);
let countriesCount = ref(0);
let citiesCount = ref(0);
let latestSpace = ref('');

const fetchCountries = async () => {
  const response = await fetch('/api/spaces');
  const data = await response.json();
  countries.value = data[0];
  countriesCount.value = data[1];
  citiesCount.value = data[2];
  spacesCount.value = data[3];
  latestSpace.value = data[4];

  isLoading.value = false;
};

const sortedCountries = computed(() => {
  return countries.value.sort((a, b) => a.country_slug.localeCompare(b.country_slug));
});

fetchCountries();

let isLoading = ref(true);
let userLoggedIn = ref(window.userLoggedIn);
let mySpaces = ref([]);
let myColivingSpaces = ref([]);
let pastColivingSpaces = ref([]);

if (userLoggedIn.value) {
  if (window.mySpaces) {
    mySpaces.value = window.mySpaces.sort((a, b) => a.country_slug.localeCompare(b.country_slug));
    myColivingSpaces.value = window.myColivingSpaces.sort((a, b) => a.country_slug.localeCompare(b.country_slug));
    pastColivingSpaces.value = window.pastColivingSpaces.sort((a, b) => a.country_slug.localeCompare(b.country_slug));
  } else {
    const fetchMySpaces = async () => {
      const response = await fetch('/api/my-spaces');
      const data = await response.json();
      // console.log(data)
      mySpaces.value = data.mySpaces.sort((a, b) => a.country_slug.localeCompare(b.country_slug));
      myColivingSpaces.value = data.myColivingSpaces.sort((a, b) => a.country_slug.localeCompare(b.country_slug));
      pastColivingSpaces.value = data.pastColivingSpaces.sort((a, b) => a.country_slug.localeCompare(b.country_slug));
      // window.mySpaces = data;
    };
    fetchMySpaces();
  }
}
</script>

<style>
.my-spaces-popup {
  padding:25px 10px 5px 10px;text-align: center;position:relative;border-radius: 5px;background-color: rgb(246, 246, 246);border: 1px solid rgb(204, 204, 204);max-width:320px;float:right;
}

.my-spaces-popup-mob {
  padding:25px 10px 5px 10px;text-align: center;position:relative;border-radius: 5px;background-color: rgb(246, 246, 246);border: 1px solid rgb(204, 204, 204);max-width:320px;float:left;display:none;
  margin-bottom:40px;
  min-width: 320px;
}

.newest-space-popup {
  padding:25px 10px 5px 10px;text-align: center;position:relative;border-radius: 5px;background-color: rgb(246, 246, 246);border: 1px solid rgb(204, 204, 204);max-width:320px;float:right;margin-top:30px;
  min-width: 320px;
}

@media screen and (max-width: 980px) {
  .my-spaces-popup {
    display:none;
  }
  .my-spaces-popup-mob {
    display:block;
  }
  .newest-space-popup {
    float:left;
    margin-top:40px;
  }
}

.mobile-only-h2 {
  display:none;
}

@media screen and (max-width: 700px) {
  .mobile-only-h2 {
    margin-top:15px;
    display:block;
  }
}
</style>