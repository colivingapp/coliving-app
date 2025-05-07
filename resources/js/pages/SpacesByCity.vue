<!-- @TODO: Adapt for cities -->
<template>
  <div class="content">
    <h1 v-if="spaces[0] && !isLoading">
      <span :class="'fi fi-'+spaces[0].country_code.toLowerCase()"></span>&nbsp;&nbsp;Coliving in {{ country }}
    </h1>
    <h1 v-else>
      Please wait...
    </h1>

    <div v-if="country == 'australia'">
      <p>Welcome to Australia, an extraordinary coliving destination Down Under. Embrace the laid-back yet adventurous spirit of this vast country, where coliving experiences take you on a journey of exploration, connection, and unforgettable memories. Discover thoughtfully designed coliving spaces in vibrant cities like Sydney, Melbourne, or Brisbane, where you'll find a welcoming community of diverse individuals eager to collaborate and share experiences.</p>

      <p>Australia's coliving spaces reflect the nation's love for nature and modernity. Wake up to stunning views of pristine beaches, lush rainforests, or bustling cityscapes, depending on your chosen location. Equipped with top-notch amenities, high-speed internet, and comfortable communal areas, these coliving spaces cater to digital nomads, professionals, and creatives alike.</p>

      <p>Beyond the coliving spaces, Australia beckons you to immerse yourself in its unique culture and natural wonders. Snorkel in the Great Barrier Reef, surf on some of the world's best waves, or hike through ancient rainforests. Experience the friendliness and openness of Australians as you join local events, explore vibrant art scenes, and indulge in a melting pot of international cuisines.</p>

      <p>Use the Coliving App to unlocking the best coliving experiences in this breathtaking country. So, pack your bags, say g'day to new friends, and embark on an unforgettable coliving journey in the land of kangaroos and koalas. Welcome to Australia, where dreams, adventures, and friendships come alive like nowhere else. G'day mate!</p>
    </div>
    
    <div style="height:10px;"></div> 
    <map-by-country :locations="sortedSpaces" />
    <div style="height:10px;"></div> 

    <h2>Coliving Spaces in {{ country }}</h2>
    
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
      <!-- <router-link :to="`/space/${space.uid}`">
        {{ space.name }}
      </router-link> -->
    </div>

    <div v-if="country == 'argentina'">
     
     <h1>About</h1>
      <h2>Coliving in Argentina - A Growing Trend for Digital Nomads and Expats</h2>

     <p>Argentina, known for its vibrant culture and diverse landscapes, is becoming a popular destination for digital nomads and expats. The country's economic challenges have made it an affordable place for living and working remotely. Coliving spaces are thriving in cities like Buenos Aires, Córdoba, and Mendoza, offering modern amenities and community-driven environments.</p>

     <h3>Why Choose Argentina for Coliving?</h3>

     <ul>
       <li><strong>Affordable Living Costs:</strong> Monthly expenses, including rent, utilities, and groceries, range from $430 to $1,085 USD per person, making it a cost-effective option.</li>
       <li><strong>Vibrant Community:</strong> Coliving spaces host networking events, workshops, and social activities to help residents connect and collaborate.</li>
       <li><strong>Modern Amenities:</strong> High-speed internet, coworking areas, fitness facilities, and communal kitchens are common features.</li>
       <li><strong>Cultural Experience:</strong> Enjoy the rich culture, scenic beauty, and world-renowned cuisine of Argentina.</li>
     </ul>

     <h3>Popular Coliving Spaces</h3>

     <ul>
       <li><strong>Selina Palermo Soho (Buenos Aires):</strong> Located in a trendy neighborhood, offering private and shared rooms, coworking space, and wellness activities.</li>
       <li><strong>Casa Campus Pilar (Buenos Aires):</strong> Modern apartments, coworking spaces, and amenities like a gym and swimming pool.</li>
       <li><strong>Nomad Hub Buenos Aires:</strong> Modern apartments and coworking areas, known for community events and networking opportunities.</li>
       <li><strong>Huella Co-Living (Mendoza):</strong> Provides a serene environment with private and shared rooms, coworking space, and a garden.</li>
     </ul>

     <p>Coliving in Argentina offers a unique opportunity to live affordably while experiencing the country's rich culture and vibrant community. With numerous coliving spaces providing modern amenities and fostering a collaborative environment, Argentina is an ideal destination for digital nomads and expats looking to balance work and adventure.</p>

   </div>

    <!-- <div style="height:40px;"></div> -->
    
  </div>
 
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';

import MapByCountry from '../map/MapByCountry.vue';

const country = ref('');
const spaces = ref([]);
let isLoading = ref('true');

const fetchSpaces = async () => {
  const route = useRoute();
  const countrySlug = route.params.countrySlug;

  const response = await fetch(`/api/spaces/${countrySlug}`);
  const data = await response.json();

  isLoading.value = false;
  country.value = data.country;
  spaces.value = data.spaces;

  document.title = 'Coliving Spaces in ' + country.value + ' - Coliving App';
};

onMounted(fetchSpaces);

const sortedSpaces = computed(() => {
  return spaces.value.sort((a, b) => a.name.localeCompare(b.name));
});
</script>

<style>
  .photo-credits {
    font-size:10px;
    font-style: italic;
    text-align:right;
  }

  .widget-space {
    clear:both;margin-top:10px;padding:10px;border:1px solid#aeaeae;border-radius:5px;min-height:90px;display:grid;grid-template-columns:90px 15px 1fr;
  }

  .widget-space-inner {
    width:90px;height:70px;overflow:hidden;text-align:center;
  }

  .img-fit-cover {
    width: 100%;
    height: 100%;
    object-fit: cover; /* This makes the image cover the entire div */
    object-position: center; /* This centers the image in the div */
  }
</style>