<template>
  <div class="content">
    <h1>My Spaces</h1>
    
    <div v-if="userLoggedIn">
      <router-link to="/space/new"><button class="button">Add New Space</button></router-link>
      
      <div style="height:30px;"></div>

      Below you will find all the spaces where you hold the administrator role.
      
      <div style="height:10px;"></div>

      <div v-if="isLoading">
        <br>
        Loading...
      </div>
      <div v-else-if="mySpaces.length == 0 && !isLoading">
        <br>
        You have no spaces to manage.
      </div>
      <div v-else >
        <div style="clear:both;margin-top:10px;padding:10px;border:1px solid#999;border-radius:5px;" v-for="space in mySpaces" >
          <div style="float:left;width:150px;height:100px;overflow:hidden;margin-right:15px;text-align:center;">
            <img v-if="space.first_photo" :src="'/storage/spaces/'+space.first_photo" class="img-fit-cover"><img v-else style="height:100px;" src="/img/coliving-app-logo-symbol.png">
          </div>

          <router-link :to="'/spaces/'+space.country">{{ space.country }}</router-link>
          <br>
          <router-link :to="'/space/'+space.uid" style="font-size:17px;">{{ space.name }}</router-link>
          <br>
          Mates: {{ space.mates }}
          <br>
          <br>
          <router-link :to="'/space/'+space.uid+'/edit'" style="font-size:15px;">Edit</router-link>&nbsp;&nbsp;|&nbsp;&nbsp;<router-link :to="'/space/'+space.uid+'/dashboard'" style="font-size:15px;">Manage</router-link> 
        </div>
      </div>
      <br>
      <br>
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
