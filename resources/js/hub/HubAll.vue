<template>
  <div class="content">
    <h1>Hub</h1>
    The coliving hub is the place where you can find information about your coliving space, chat with your coliving space mates, customize your coliving settings, and participate in engaging coliving activities.
    <div style="height:10px;"></div>

    <div v-if="userLoggedIn">

      <div style="height:35px;"></div>
      
      <div style="height:40px;border-bottom:1px solid #ccc;">
        <div class="page-menu-button-outer">
          <div class="page-menu-button" @click="hubType = 'now'"><span class="inbox-type-link" :class="{active : hubType == 'now'}">Now</span></div>
          <div class="page-menu-underline" v-if="hubType == 'now'"></div>
        </div>
        <div class="page-menu-button-outer" style="margin-left:20px;">
          <div class="page-menu-button" @click="hubType = 'past'"><span class="inbox-type-link" :class="{active : hubType == 'past'}" >Past</span></div>
          <div class="page-menu-underline" v-if="hubType == 'past'"></div>
        </div>
      </div>

      <div style="height:30px;"></div>
      
      <div v-if="hubType == 'now'">
        <span v-if="myColivingSpaces.length > 0">
          <div v-for="space in myColivingSpaces">
            <router-link :to="'/space/'+space.space_uid+'/hub'" >
              <div style="width:100%;padding:10px;background-color: aliceblue;border: 1px solid #00abff;border-radius: 5px;display:grid;grid-template-columns:49px 1fr;margin-bottom:20px;">
                <div style="padding-left:3px;">
                  <img src="/img/hub.png" width="34">
                  <!-- <icons :icon-name="'space'" :width="34"></icons> -->
                </div>
                <div style="padding-top:1px;">  
                  <span style="font-size:16px;font-weight:bold;">{{  space.name  }}</span>
                  <br>
                  {{ space.joined_at }} - Now
                </div>
              </div>
            </router-link>
          </div>
        </span>        
        <span v-else>
          <div style="height:10px;"></div>
          <span v-if="isLoading">Loading...</span>
          <span v-else>
            You are not currently coliving. When you join or host a coliving space, your space hub will be available here.
          </span>
          <div style="height:10px;"></div>
        </span>
      </div>
      
      <div v-else>
        <span v-if="pastColivingSpaces.length > 0">
          <div v-for="space in pastColivingSpaces" >
            <router-link :to="'/space/'+space.space_uid" >
              <div style="width:100%;padding:10px;background-color: rgb(239, 239, 239);border-radius: 5px;margin-bottom:20px;display:grid;grid-template-columns:49px 1fr;border:1px solid grey;">
                <div style="padding-top:0px;padding-left:3px;">
                  <img src="/img/hub.png" width="34" style="-webkit-filter: grayscale(100%);filter: grayscale(100%);">
                  <!-- <icons :icon-name="'space'" :width="34"></icons> -->
                </div>
                <div style="color:grey;padding-top:3px;">  
                  <b>{{  space.name  }}</b>
                  <br>
                  Left: {{ space.left_at.slice(0, -3) }} - Joined: {{ space.joined_at.slice(0, -3) }}
                </div>
              </div>
            </router-link>
          </div>
        </span>        
        <span v-else>
          <div style="height:10px;"></div>
          <span v-if="isLoading">Loading...</span>
          <span v-else>
            You have no past coliving experiences yet.
          </span>
          <div style="height:10px;"></div>
        </span>
      </div>

    </div>
    <div v-else>
      <div style="height:20px;"></div>

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
  import { ref, onMounted } from 'vue';
  import Icons from '../components/Icons.vue';

  let userLoggedIn = ref(window.userLoggedIn);
  let mySpaces = ref([]);
  let myColivingSpaces = ref([]);
  let pastColivingSpaces = ref([]);
  let hubType = ref('now');
  let isLoading = ref(true);

  if (userLoggedIn.value) {
    if (window.mySpaces) {
      mySpaces.value = window.mySpaces.sort((a, b) => a.country.localeCompare(b.country));
      myColivingSpaces.value = window.myColivingSpaces.sort((a, b) => new Date(b.joined_at) - new Date(a.joined_at));
      pastColivingSpaces.value = window.pastColivingSpaces.sort((a, b) => new Date(b.left_at) - new Date(a.left_at));
    } else {
      const fetchMySpaces = async () => {
        const response = await fetch('/api/hub-all');
        const data = await response.json();
        myColivingSpaces.value = data.myColivingSpaces.sort((a, b) => new Date(b.joined_at) - new Date(a.joined_at));
        pastColivingSpaces.value = data.pastColivingSpaces.sort((a, b) => new Date(b.left_at) - new Date(a.left_at));
        isLoading.value = false;
      };
      fetchMySpaces();
    }
  }
</script>

<style>
  .page-menu-button {
    border-radius: 5px;
    padding:5px;
  }

  .page-menu-button-outer {
    float:left;
    border-radius: 5px;
    margin-left:10px;
  }

  .page-menu-button:hover, .page-menu-button:active {
    cursor:pointer;
    background-color: rgb(232, 232, 232);
  }

  .page-menu-underline {
    width:100%;
    height:2px;
    background-color:#393939;
    margin-top:7px;
  }
</style>