<template>
  <div class="content">
    <div class="my-spaces-header">
      <div class="my-spaces-heading">
        <h1>My Spaces</h1>
        <p v-if="userLoggedIn" class="my-spaces-intro">
          Add a new listing, or manage the spaces you administer.
        </p>
      </div>
      <router-link v-if="userLoggedIn" to="/space/new" class="my-spaces-add">
        <button class="action-button button-1">Add New Space</button>
      </router-link>
    </div>

    <div v-if="userLoggedIn" class="my-spaces">

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
.my-spaces-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
  padding-top: 19px;
  margin-bottom: 40px;
}

.my-spaces-heading {
  flex: 1;
  min-width: 200px;
}

.my-spaces-header h1 {
  margin: 0;
  padding-top: 0;
}

.my-spaces-add {
  flex-shrink: 0;
}

.my-spaces-add .action-button {
  margin: 0;
}

.my-spaces-intro {
  margin: 6px 0 0;
}

@media screen and (max-width: 600px) {
  .my-spaces-add {
    width: 100%;
  }

  .my-spaces-add .action-button.button-1 {
    width: 100%;
  }
}
</style>
