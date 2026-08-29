<template>
  <div class="content">
    <h1>Coliving Hub</h1>
    Hub is where you'll find the private spaces you are coliving in. Open a space hub to see topics, members, and the calendar. Additionally, if you are an admin of a space, you will also see join requests and admin settings.
    <div style="height:10px;"></div>

    <div v-if="userLoggedIn">

      <div style="height:35px;"></div>
      
      <div style="height:40px;border-bottom:1px solid #ccc;" class="hub-all-tabs">
        <div class="page-menu-button-outer">
          <div class="page-menu-button" @click="hubType = 'coliving'">
            <span class="inbox-type-link" :class="{active : hubType == 'coliving'}">
              Coliving ({{ myColivingSpaces.length }})
            </span>
          </div>
          <div class="page-menu-underline" v-if="hubType == 'coliving'"></div>
        </div>
        <div v-if="vacantColivingSpaces.length > 0" class="page-menu-button-outer" style="margin-left:20px;">
          <div class="page-menu-button" @click="hubType = 'vacant'">
            <span class="inbox-type-link" :class="{active : hubType == 'vacant'}">
              Vacant ({{ vacantColivingSpaces.length }})
            </span>
          </div>
          <div class="page-menu-underline" v-if="hubType == 'vacant'"></div>
        </div>
        <div v-if="pastColivingSpaces.length > 0" class="page-menu-button-outer" style="margin-left:20px;">
          <div class="page-menu-button" @click="hubType = 'past'">
            <span class="inbox-type-link" :class="{active : hubType == 'past'}">
              Past ({{ pastColivingSpaces.length }})
            </span>
          </div>
          <div class="page-menu-underline" v-if="hubType == 'past'"></div>
        </div>
      </div>

      <div style="height:30px;"></div>
      
      <div v-if="hubType == 'coliving'">
        <div v-if="isLoading">Loading...</div>
        <div v-else-if="myColivingSpaces.length > 0" class="space-grid">
          <SpaceCard
            v-for="space in myColivingSpaces"
            :key="space.uid || space.space_uid"
            :space="space"
            :to="'/space/' + (space.uid || space.space_uid) + '/hub'"
          />
        </div>
        <div v-else>
          <div style="height:10px;"></div>
          You are not currently coliving. When you join or administer a coliving space, your space hub will be available here.
          <div style="height:10px;"></div>
        </div>
      </div>

      <div v-else-if="hubType == 'vacant'">
        <div v-if="isLoading">Loading...</div>
        <div v-else-if="vacantColivingSpaces.length > 0" class="space-grid">
          <SpaceCard
            v-for="space in vacantColivingSpaces"
            :key="space.uid || space.space_uid"
            :space="space"
            :to="'/space/' + (space.uid || space.space_uid) + '/hub'"
          />
        </div>
        <div v-else>
          <div style="height:10px;"></div>
          You have no vacant spaces.
          <div style="height:10px;"></div>
        </div>
      </div>
      
      <div v-else>
        <div v-if="isLoading">Loading...</div>
        <div v-else-if="pastColivingSpaces.length > 0" class="space-grid">
          <SpaceCard
            v-for="space in pastColivingSpaces"
            :key="space.uid || space.space_uid"
            :space="space"
          />
        </div>
        <div v-else>
          <div style="height:10px;"></div>
          You have no past coliving experiences yet.
          <div style="height:10px;"></div>
        </div>
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
  import { ref, watch } from 'vue';
  import SpaceCard from '../components/SpaceCard.vue';

  let userLoggedIn = ref(window.userLoggedIn);
  let myColivingSpaces = ref([]);
  let vacantColivingSpaces = ref([]);
  let pastColivingSpaces = ref([]);
  let hubType = ref('coliving');
  let isLoading = ref(true);

  const activityTime = (space) => {
    if (!space.last_activity_at) {
      return 0;
    }
    const time = new Date(space.last_activity_at).getTime();
    return Number.isNaN(time) ? 0 : time;
  };

  const sortByRecentActivity = (spaces) => {
    return [...(spaces || [])].sort((a, b) => activityTime(b) - activityTime(a));
  };

  watch([vacantColivingSpaces, pastColivingSpaces], () => {
    if (hubType.value === 'vacant' && vacantColivingSpaces.value.length === 0) {
      hubType.value = 'coliving';
    }
    if (hubType.value === 'past' && pastColivingSpaces.value.length === 0) {
      hubType.value = 'coliving';
    }
  });

  if (userLoggedIn.value) {
    if (window.myColivingSpaces) {
      myColivingSpaces.value = sortByRecentActivity(window.myColivingSpaces);
      vacantColivingSpaces.value = sortByRecentActivity(window.vacantColivingSpaces || []);
      pastColivingSpaces.value = sortByRecentActivity(window.pastColivingSpaces || []);
      isLoading.value = false;
    } else {
      const fetchMySpaces = async () => {
        const response = await fetch('/api/hub-all');
        const data = await response.json();
        myColivingSpaces.value = sortByRecentActivity(data.myColivingSpaces);
        vacantColivingSpaces.value = sortByRecentActivity(data.vacantColivingSpaces || []);
        pastColivingSpaces.value = sortByRecentActivity(data.pastColivingSpaces);
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
