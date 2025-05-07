<template>
  <div class="content">
    
    <div v-if="mate && mate.private != 0" style="margin-bottom:30px;background-color:red;color:white;padding:5px;border-radius:5px;">
      This is a private profile. Only the owner can see it.
    </div>

    <div v-if="mate" >
      <div>
        <h1>Coliving Mate</h1>
        <div style="height:20px;"></div>
        <profile-pic :profile_pic="mate.profile_pic" :avatar="mate.avatar" :photo="mate.photo" style="height:100px;width:100px;overflow:hidden;"/>
        <div style="height:20px;"></div>
        <span style="font-size:15px;">
          <span v-if="mate">Name: <span v-if="mate.name"><b>{{ mate.name }}</b></span><span v-else>Not specified</span><br></span>
          <span v-if="mate">Username: <b>{{ mate.username }}</b></span>
          <br><br>
          <span style="white-space: pre-line">{{ mate.hobbies_interests }}</span>
        </span>

        <div style="height:20px;"></div>

        <span v-if="mate.twitter != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="mate.twitter" target="_blank" rel="nofollow noopener"><icons :iconName="'twitter'" /></a></div>
        </span>
        <span v-if="mate.facebook != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="mate.facebook" target="_blank" rel="nofollow noopener"><icons :iconName="'facebook'" /></a></div>
        </span>
        <span v-if="mate.instagram != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="mate.instagram" target="_blank" rel="nofollow noopener"><icons :iconName="'instagram'" /></a></div>
        </span>
        <span v-if="mate.pinterest != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="mate.pinterest" target="_blank" rel="nofollow noopener"><icons :iconName="'pinterest'" /></a></div>
        </span>
        <span v-if="mate.github != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="mate.github" target="_blank" rel="nofollow noopener"><icons :iconName="'github'" /></a></div>
        </span>
        <span v-if="mate.youtube != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="mate.youtube" target="_blank" rel="nofollow noopener"><icons :iconName="'youtube'" /></a></div>
        </span>
        <span v-if="mate.tiktok != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="mate.tiktok" target="_blank" rel="nofollow noopener"><icons :iconName="'tiktok'" /></a></div>
        </span>
        <span v-if="mate.linkedin != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="mate.linkedin" target="_blank" rel="nofollow noopener"><icons :iconName="'linkedin'" /></a></div>
        </span>
        <span v-if="mate.substack != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="mate.substack" target="_blank" rel="nofollow noopener"><icons :iconName="'substack'" /></a></div>
        </span>
        <span v-if="mate.medium != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="mate.medium" target="_blank" rel="nofollow noopener"><icons :iconName="'medium'" /></a></div>
        </span>


        <!-- Display other mate details here -->
        <div style="height: 20px;"></div>
        <router-link :to="'/inbox/mate/'+mate.uid" v-if="currentMateUID != mate.uid && userLoggedIn"><button class="action-button button-1">Send message</button></router-link>
        <router-link to="/login" v-else-if="!userLoggedIn"><button class="action-button button-1">Send message</button></router-link>
        

        <div style="height: 60px;"></div>

        <h2 style="margin-top:0px;">CoMatch Score</h2>
        <div style="border-radius: 5px;width:100%;padding:25px 10px 5px 10px;background-color: rgb(246, 246, 246);border: 1px solid rgb(204, 204, 204);">
          

          <span v-if="coliveFitScore && userLoggedIn" style="font-size:24px;font-weight:bold;">{{ coliveFitScore.toFixed(2) }}%</span>
          <span v-else-if="!userLoggedIn">To see the CoMatch score, please <router-link to="/register">sign up</router-link> or <router-link to="/login">log in</router-link>.</span>

          <div style="height:20px;"></div>

          This score reflects how well your living habits match with this coliving mate. Higher scores mean better compatibility.

          <div style="height:20px;"></div>
        </div>
        <div style="height: 20px;"></div>


        <h3>References</h3>
        <span v-if="ratingsAll.length === 0 || !ratingsAll.some(rating => rating['rated_by_user'])">
          No references yet.
        </span>
        <span v-else>
          <div v-for="(rating, index) in ratingsAll" >
            <div v-if="rating['rated_by_user']" style="background-color:aliceblue;padding:15px;border:1px solid grey;border-radius:5px;margin-bottom:20px;">
              <div style="display:grid;grid-template-columns:20px 5px 1fr">
                <profile-pic :profile_pic="rating['rated_by_user']['profile_pic']" :avatar="rating['rated_by_user']['avatar']" :photo="rating['rated_by_user']['photo']" style="height:20px;width:20px;overflow:hidden;"/>
                <div></div>
                <div><a :href="'/mate/'+ (rating['rated_by_user']['username'] ? rating['rated_by_user']['username'] : rating['rated_by_user']['uid'])"><b>{{ rating['rated_by_user']['name'] }}</b></a> <br> {{ formatTimestamp(rating['created_at']) }}</div>
              </div>
              <div style="height:10px;"></div>
              <div style="padding-left:25px;font-size:18px;position:relative;">
                <div v-if="rating['rating'] == 1" style="color:green;">Positive</div>
                <div v-else style="color:red;">Negative</div>
                
                <div style="height:10px;"></div>

                <div v-html="rating['reference'].replace(/\n/g, '<br>')" style="white-space: pre-line;"></div>
                <div style="position:absolute;right:0px;bottom:0px;font-size:14px;" v-if="rating['rated_by_user']['currentUser']">
                  <span @click="editRating[index] = !editRating[index]" class="link"><span v-if="!editRating[index]">Edit</span><span v-else>Close</span></span>
                </div>
                
                <div v-if="editRating[index]">
                    <h3>Edit your reference</h3>
                    <select v-model="ratingEdit">
                      <option value="1">Positive</option>
                      <option value="0">Negative</option>
                    </select>
                    <textarea class="textarea" placeholder="" v-model="referenceTextEdit"></textarea>
                    <div class="character-count">{{ characterCountEdit }}/{{ maxCharacterCount }}</div>
                    <button @click="submitRatingEdit">Submit</button>
                    <div style="height: 20px;"></div>
                  </div>
              </div>
            </div>
            <!-- <div v-else>Deleted</div> -->
          </div>
        </span>
        <div style="height: 20px;"></div>

        <div v-if="currentMateUID != mate.uid && !currentUserRated && userLoggedIn">
          <h3>Write a reference</h3>
          <select v-model="rating">
            <option v-for="option in ratings" :key="option" :value="option">{{ option }}</option>
          </select>
          <textarea class="textarea" placeholder="" v-model="referenceText"></textarea>
          <div class="character-count">{{ characterCount }}/{{ maxCharacterCount }}</div>
          <button @click="submitRating" v-if="!ratingSubmitted">Submit</button>
          <span v-else>Sending...</span>
          <div style="height: 20px;"></div>
        </div>
      </div>


      <div v-if="mate.visited_countries.length > 0">
        <div style="height: 60px;"></div>

        <h2 style="margin-top:0px;">Visited Countries</h2>

        Total: {{ mate.visited_countries.length }}
        <div style="height: 20px;"></div>

        <VisitedCountries :visitedCountries="mate.visited_countries" :type="'profile'"></VisitedCountries>
      </div>

    </div>
    <div v-else>
      <span v-if="!isLoaded">Loading...</span>
      <span v-else>This mate doesn't exist.</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';

import ProfilePic from '../components/ProfilePic.vue';
import Icons from '../components/Icons.vue';
import VisitedCountries from '../form/VisitedCountries.vue';

const mate = ref(null);
const route = useRoute();

let userLoggedIn = ref(window.userLoggedIn);
const rating = ref('Positive');
const ratings = ['Positive', 'Negative'];
let ratingsAll = ref([]);
const referenceText = ref('');
let ratingEdit = ref('');
let referenceTextEdit = ref('');
let currentUserRated = ref(false);
let editRating = ref([]);
let currentMateUID = ref('');
const maxCharacterCount = 200;
let coliveFitScore = ref(null);

let isLoaded = ref(false);
let ratingSubmitted = ref(false);

onMounted(() => {
  fetchMate();
});

function formatTimestamp(timestamp) {
  const date = new Date(timestamp);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  });
}

const fetchMate = async () => {
  document.title =  'Coliving Mate - ' + route.params.mate;
  const response = await fetch('/api/mate/' + route.params.mate);
  const data = await response.json();
  // console.log('data', data)
  mate.value = data[0][0];
  try {
    mate.value.visited_countries = JSON.parse(data[0][0].visited_countries);
  } catch (error) {
    console.error('Error parsing visitedCountries:', error);
    mate.value.visited_countries = [];
  }

  ratingsAll.value = data[1];
  currentMateUID.value = data[2];
  for (let i = 0; i < ratingsAll.value.length; i++) {
    // console.log("zzz", ratingsAll.value[i])
    if (ratingsAll.value[i].rated_by_user && (ratingsAll.value[i].rated_by_user.uid == currentMateUID.value)) {
      currentUserRated.value = true;
      ratingsAll.value[i].rated_by_user.currentUser = 'true';
      referenceTextEdit.value = ratingsAll.value[i].reference;
      ratingEdit.value = ratingsAll.value[i].rating;
    }
    // ratings.forEach(rating => {
    //   console.log(rating)
    // })
  }
  isLoaded.value = true;
  
  let user1 = {
    orgSpont: mate.value.orgSpont,
    quietLoud: mate.value.quietLoud,
    earlyNight: mate.value.earlyNight,
    workFun: mate.value.workFun,
    expTrad: mate.value.expTrad,
    multiLocal: mate.value.multiLocal
  }

  let user2 = {
    orgSpont: window.mate.orgSpont,
    quietLoud: window.mate.quietLoud,
    earlyNight: window.mate.earlyNight,
    workFun: window.mate.workFun,
    expTrad: window.mate.expTrad,
    multiLocal: window.mate.multiLocal
  }
  
  // console.log(user1, user2)
  coliveFitScore.value = calculateColiveFitScore(user1, user2)
};


function calculateCriterionScore(value1, value2) {
  const difference = Math.abs(value1 - value2);
  return (10 - difference) * 10; // Convert to percentage
}

function calculateColiveFitScore(user1, user2) {
  let totalScore = 0;

  totalScore += calculateCriterionScore(user1.orgSpont, user2.orgSpont);
  totalScore += calculateCriterionScore(user1.quietLoud, user2.quietLoud);
  totalScore += calculateCriterionScore(user1.earlyNight, user2.earlyNight);
  totalScore += calculateCriterionScore(user1.workFun, user2.workFun);
  totalScore += calculateCriterionScore(user1.expTrad, user2.expTrad);
  totalScore += calculateCriterionScore(user1.multiLocal, user2.multiLocal);

  return totalScore / 6; // Average score
}


const characterCount = computed(() => {
  return referenceText.value.length;
});

const characterCountEdit = computed(() => {
  return referenceTextEdit.value.length;
});


const submitRating = () => {
  ratingSubmitted.value = true;
  axios
    .post(`/api/mates/${mate.value.uid}/ratings`, { rating: rating.value, reference: referenceText.value })
    .then((response) => {
      // console.log(response.data);
      if (response.data == 'success') {
        location.reload();
      }
      else {
        ratingSubmitted.value = false;  
      }
    })
    .catch((error) => {
      console.error(error);
    });
};

const submitRatingEdit = () => {
  axios
    .post(`/api/mates/${mate.value.uid}/ratings/edit`, { rating: ratingEdit.value, reference: referenceTextEdit.value })
    .then((response) => {
      // console.log(response.data);
      fetchMate();
      editRating.value = [];
    })
    .catch((error) => {
      console.error(error);
    });
};
</script>

<style>
.space-name {
  font-size: 25px;
  font-weight: bold;
}

.space-photo {
  width: 100%;
}

.character-count {
  float: right;
  font-size: 12px;
  color: gray;
}
</style>
