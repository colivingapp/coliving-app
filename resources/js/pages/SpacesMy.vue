<template>
  <div class="content">
    <h1>My Spaces</h1>
    
    <div v-if="userLoggedIn" class="my-spaces">
      <router-link to="/space/new"><button class="button">Add New Space</button></router-link>

      <p class="my-spaces-intro">
        Below you will find all the spaces where you hold the administrator role.
      </p>

      <div v-if="isLoading">
        Loading...
      </div>
      <div v-else-if="mySpaces.length == 0 && !isLoading">
        You have no spaces to manage.
      </div>
      <div v-else class="space-grid">
        <SpaceCard v-for="space in mySpaces" :key="space.uid" :space="space" manageable />
      </div>
    </div>
    <div v-else>
      To see and manage your coliving spaces, please log in to your account.

      <div style="height:30px;"></div>

      Log in to continue
      <div style="height:10px;"></div>
      <router-link to="/login"><button class="auth-button">Login</button></router-link>

      <div style="height:30px;"></div>

      Create a free account
      <div style="height:10px;"></div>
      <router-link to="/register"><button class="auth-button">Sign Up</button></router-link> 
    </div>
  </div>
</template>

<script setup>
  import { ref } from 'vue';
  import SpaceCard from '../components/SpaceCard.vue';

  let userLoggedIn = ref(window.userLoggedIn);
  let mySpaces = ref([]);
  let myColivingSpaces = ref([]);
  let pastColivingSpaces = ref([]);
  let isLoading = ref('true');

  if (userLoggedIn.value) {
    const fetchMySpaces = async () => {
      const response = await fetch('/api/my-spaces');
      const data = await response.json();
      // console.log(data)
      mySpaces.value = data.mySpaces.sort((a, b) => a.country_slug.localeCompare(b.country_slug));
      isLoading.value = false;
      // window.mySpaces = data;
    };

    fetchMySpaces();
  }
</script>

<style scoped>
.my-spaces .button {
  margin-top: 0;
  margin-bottom: 0;
}

.my-spaces-intro {
  margin: 12px 0 40px;
}
</style>
