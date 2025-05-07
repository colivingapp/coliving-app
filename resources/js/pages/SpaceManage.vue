<script setup>
import { ref, onMounted, computed  } from 'vue';
import { useRoute } from 'vue-router';

import ModalFeedback from '../components/ModalFeedback.vue';

import ProfilePic from '../components/ProfilePic.vue';

const route = useRoute();
const space = ref(null);
const claim = ref(false);

let jr = ref(null);
let comments = ref('');
let isMember = ref(false);
let mates = ref([])
let matesCount = ref(0);
let matesLeftCount = ref(0);
let modalFeedback = ref(false);

const userLoggedIn = window.userLoggedIn;

let mate = ref('');
if (userLoggedIn) {
  mate.value = window.mate;
}

const { uid } = route.params;

const fetchSpace = async () => {
  try {
    const response = await axios.get(`/api/space/${uid}`);
    space.value = response.data.space;
  } catch (error) {
    console.error(error);
  }
};

function spaceFetch() {
  axios.get('/api/space/'+route.params.uid+'/space-data')
  .then((result) => {
    if (result.data == 'unauthenticated') {
      isMember.value = 'unauthenticated';
      console.log("Unauthenticated.");
    }
    else if (result.data == 'not_a_member') {
      isMember.value = false;
      console.log("Not a member.");
    }
    else {
      isMember.value = true;
      mates.value = result.data[0];
      mates.value.forEach(mate => {
        if (mate.status == 'active') {
          matesCount.value++;
        }
        else if (mate.status == 'left') {
          matesLeftCount.value++;
        }
        if (mate.name != '') {
          mate.displayName = mate.name;
        }
        else if (mate.username != '') {
          mate.displayName = mate.username;
        }
        else {
          mate.displayName = mate.uid;
        }
      }) 
    }
  })
  .catch(function(error) {
      console.log(error);
  });
}

const fetchJoinRequests = async () => {
  try {
    const response = await axios.get(`/api/space/${uid}/jr`);
    console.log(response.data)
    jr.value = response.data;
  } catch (error) {
    console.error(error);
  }
};

function fetch() {
  spaceFetch();
  fetchSpace();
  fetchJoinRequests();
}

onMounted(fetch);
</script>

<template>
  <div class="content">
    <div style="height:20px;"></div>
   
    <div v-if="space">
      <div class="space-name">Coliving Space Dashboard</div>

      <h2 style="margin-top:10px;"><router-link :to="'/space/'+uid">{{ space.name }}</router-link></h2>

      <div style="height:40px;"></div>

      <div style="margin-top:0px;margin-bottom:30px;font-size:25px;">Current Coliving Mates: <b>{{ matesCount }}</b></div>

      <div v-if="matesCount > 0" v-for="mate in mates" :key="mate.id" >
          <div v-if="mate.status != 'left'">
              <router-link :to="'/mate/'+ (mate.username ? mate.username : mate.uid)" style="display:grid;grid-template-columns:25px 1fr;height:25px;margin-bottom:10px;">
                  <profile-pic :profile_pic="mate.profile_pic" :avatar="mate.avatar" :photo="mate.photo" style="height:25px;width:25px;overflow:hidden;"/>
                  <span style="padding-left:5px;font-size:18px;">{{ mate.displayName }}</span>
              </router-link>
              <div style="padding-left:28px;margin-top:-5px;margin-bottom:15px;">Joined: {{ mate.joined_at }}</div>
          </div>
      </div>
      <div v-else>
        You have no current coliving mates yet.
      </div>

      <div style="height:70px;"></div>

      <div style="margin-top:0px;margin-bottom:30px;font-size:25px;">Past Coliving Mates: <b>{{ matesLeftCount }}</b></div>

      <div v-if="matesLeftCount > 0" v-for="mate in mates" :key="mate.id" >
          <div v-if="mate.status == 'left'">
              <router-link :to="'/mate/'+ (mate.username ? mate.username : mate.uid)" style="display:grid;grid-template-columns:25px 1fr;height:25px;margin-bottom:10px;">
                  <profile-pic :profile_pic="mate.profile_pic" :avatar="mate.avatar" :photo="mate.photo" style="height:25px;width:25px;overflow:hidden;"/>
                  <span style="padding-left:5px;font-size:18px;">{{ mate.displayName }}</span>
              </router-link>
              <div style="padding-left:28px;margin-top:-5px;margin-bottom:15px;">Joined: {{ mate.joined_at }}<br>Left: {{ mate.left_at }}</div>
          </div>
      </div>
      <div v-else>
        You have no past coliving mates yet.
      </div>

      <div style="height:70px;"></div>

      <div style="margin-top:0px;margin-bottom:30px;font-size:25px;">Join Requests: <b>{{jr.length}}</b></div>

      <div v-if="jr.length > 0" v-for="joinRequest in [...jr].reverse()">
          {{ joinRequest.created_at }} | Space: <a :href="'/space/'+joinRequest.space_uid" target="_blank">{{ joinRequest.space_uid }}</a> | Status: <b>{{ joinRequest.status }}</b> | Mate: <router-link :to="'/mate/'+joinRequest.mate_uid">{{ joinRequest.mate_uid }}</router-link> | <router-link :to="'/inbox/space/'+joinRequest.space_uid+'/mate/'+joinRequest.mate_uid">INBOX</router-link>
          <br><br>
      </div>
      <div v-else>
        You have no join requests yet.
      </div>

      
      <!-- <div style="height:70px;"></div>

      <div style="margin-top:0px;margin-bottom:30px;font-size:25px;">Events Log</div>

      Coming soon... -->


      <div style="height:70px;"></div>

      <div style="margin-top:0px;margin-bottom:30px;font-size:25px;">Your Feedback</div>

      As a coliving space host, <span style="font-weight:bold;color:rgb(0, 134, 218)!important;"  class="link-footer" @click="modalFeedback = true">let us know</span> what additional data or tools you would like to see in this coliving space dashboard.

      <div style="height:20px;"></div>
    </div>
  </div>

<div v-if="modalFeedback">
    <modal-feedback @close="modalFeedback = false" />
</div>
</template>

<style>
.space-name {
  font-size: 22px;
  font-weight: bold;
}

.space-photo {
  width: 100%;
}

.space-photo-small {
  width: 100px;
  float: left;
  display: inline;
}

.link-footer {
    font-size: 14px;
    color: #e2e2e2 !important;
    line-height: 22px;
    border-bottom: 0 !important;
    cursor: pointer;
}

.link-footer:hover {
    text-decoration: underline !important;
}
</style>