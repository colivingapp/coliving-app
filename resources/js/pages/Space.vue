<script setup>
import { ref, onMounted, computed, watch  } from 'vue';
import { useRoute } from 'vue-router';

import MapSpace from '../map/Map.vue';

import ProfilePic from '../components/ProfilePic.vue';
import Icons from '../components/Icons.vue';

const space = ref(null);
const route = useRoute();

const rating = ref('Positive');
const ratings = ['Positive', 'Negative'];
let ratingsAll = ref([]);
const referenceText = ref('');
let ratingEdit = ref('');
let referenceTextEdit = ref('');
let currentUserRated = ref(false);
let editRating = ref([]);
const maxCharacterCount = 200;
let isSocial = ref(false);
let coliveFitScore = ref(null);

let isJoined = ref(false);

const userLoggedIn = window.userLoggedIn;

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

let isLoaded = ref(false);
let ratingSubmitted = ref(false);

let caUser = null;
if (window.caUser) {
  caUser = window.caUser;
}

let mate = ref('');
if (userLoggedIn) {
  mate.value = window.mate;

  checkIfJoined();
}

function checkIfJoined(spaceUID = null) {
  if (!window.mate) return;

  const currentMate = window.mate;
  const currentSpaceUID = spaceUID || route.params.uid;

  const isInCurrentSpaces = currentMate.myColivingSpaces.some(
    space => space.space_uid === currentSpaceUID
  );

  isJoined.value = isInCurrentSpaces;

  if (isInCurrentSpaces) {
    console.log("This space is in user's myColivingSpaces.");
  } else {
    console.log("This space is NOT in user's myColivingSpaces.");
  }
}

const fullSize = ref('');

const fetchSpace = async () => {
  space.value = null;
  const { uid } = route.params;
  try {
    const response = await axios.get(`/api/space/${uid}`);
    // console.log(response.data.space.photos)

    // Extract values from the object
    if (response.data.space.photos) {
      const dataArray = Object.values(response.data.space.photos);

      // Check if there are values with 'pos' equal to 0
      const hasZeroPos = dataArray.some(item => item.pos === 0);

      // Sort only if there are no values with 'pos' equal to 0
      if (!hasZeroPos) {
        const sortedData = dataArray.sort((a, b) => a.pos - b.pos);
        response.data.space.photos = sortedData;
        // console.log(sortedData);
        
        fullSize.value = '/storage/spaces/' + sortedData[0].filename;
      }
      else {
        fullSize.value = '/storage/spaces/' + Object.values(response.data.space.photos)[0].filename;
      }
    }

    space.value = response.data.space;

    checkIfJoined(space.value.uid);

    // Colive Fit
    // console.log(mate.value, space.value)
    let user1 = {
      orgSpont: space.value.orgSpont,
      quietLoud: space.value.quietLoud,
      earlyNight: space.value.earlyNight,
      workFun: space.value.workFun,
      expTrad: space.value.expTrad,
      multiLocal: space.value.multiLocal
    }

    let user2 = {
      orgSpont: mate.value.orgSpont,
      quietLoud: mate.value.quietLoud,
      earlyNight: mate.value.earlyNight,
      workFun: mate.value.workFun,
      expTrad: mate.value.expTrad,
      multiLocal: mate.value.multiLocal
    }
    // console.log(user1, user2)
    coliveFitScore.value = calculateColiveFitScore(user1, user2)
    // console.log(coliveFitScore)

    document.title = space.value.name + ' - Coliving App';

    ratingsAll.value = response.data.ratings;
    for (let i = 0; i < ratingsAll.value.length; i++) {
      if (ratingsAll.value[i].rated_by_user.uid == window.mate.uid && userLoggedIn) {
        currentUserRated.value = true;
        ratingsAll.value[i].rated_by_user.currentUser = 'true';
        referenceTextEdit.value = ratingsAll.value[i].reference;
        ratingEdit.value = ratingsAll.value[i].rating;
      }
    }

    if (space.value.twitter != '' || space.value.facebook != ''  || space.value.instagram != ''  || space.value.youtube != ''  || space.value.tiktok != ''  || space.value.pinterest != ''  || space.value.linkedin != ''  || space.value.linktree != ''  || space.value.telegram != ''  || space.value.github != ''  || space.value.substack != ''  || space.value.medium != '' ) {
      isSocial.value = true;
    }

    // Set bookmark
    for (let space in mate.value.bookmarks) {
      if (mate.value.bookmarks[space] == route.params.uid) {
        isBookmarked.value = true;
      }
    }

    isLoaded.value = true;
  } catch (error) {
    console.error(error);
    // Handle error if the request fails
  }
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

let isBookmarked = ref(false);


onMounted(() => {
  fetchSpace();
});

// Watch for changes in the route parameters, and update space data on navigation
watch(() => route.params, fetchSpace);


const submitRating = () => {
  ratingSubmitted.value = true;
  axios
    .post(`/api/spaces/${route.params.uid}/ratings`, { rating: rating.value, reference: referenceText.value })
    .then((response) => {
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
    .post(`/api/spaces/${route.params.uid}/ratings/edit`, { rating: ratingEdit.value, reference: referenceTextEdit.value })
    .then((response) => {
      console.log(response.data);
      fetchSpace();
      editRating.value = [];
    })
    .catch((error) => {
      console.error(error);
    });
};

function shareSpace() {
  if(navigator.share) {
    navigator.share({
      title: 'Multiavatar',
      text: 'Avatar ID:',
      url: window.location
    })
    .then(() => console.log('Share complete'))
    .error((error) => console.error('Could not share at this time', error))
  }
  else {
    alert("Your device doesn't support native sharing. You can still share the space using its direct link.");
  }
}

function bookmark() {
  isBookmarked.value = !isBookmarked.value;
  axios.post('/api/bookmark/'+route.params.uid)
  .then(res => {})
  .catch(err => {})
}

const characterCount = computed(() => {
  return referenceText.value.length;
});

const characterCountEdit = computed(() => {
  return referenceTextEdit.value.length;
});


function generateAvatar(name) {
  return multiavatar(name);
}
</script>

<template>
  <div class="content">
    
    <div v-if="space && space.private != 0" style="margin-bottom:30px;background-color:red;color:white;padding:5px;border-radius:5px;">
      This is a private profile. Only the owner can see it.
    </div>

    <div v-if="space" style="width:100%;height:30px;border-radius:3px;background:#f1f1f1;margin-bottom:30px;margin-top:-20px;padding:5px;">
      <router-link to="/spaces">Spaces</router-link> > <router-link :to="'/spaces/'+space.country_slug">{{ space.country }}</router-link> > {{ space.name }}
    </div>

    <div v-if="space" style="position:relative;width:100%;max-width:640px;float:left;">

      <div style="display:grid;grid-template-columns:1fr 75px;">
        <div>
          <div class="space-name">{{ space.name }}</div>
          <div v-if="space.address">{{ space.address }}</div>
          <div v-else>{{ space.formatted_address }}</div>
        
          <div v-if="space.phone && space.phone != 'null'">
            {{ space.phone }}
          </div>

          <div v-if="space.whatsapp">
            <a :href="'https://wa.me/'+space.whatsapp" target="_blank">WhatsApp</a>
          </div>

          <div v-if="space.website">
            <br>
            <a :href="space.website" target="_blank">{{ space.website }}</a>
          </div>
        </div>

        <div>
          <div>
            <div v-if="!userLoggedIn"><router-link to="/login"><div class="button-bookmarks"><icons :iconName="'heart'" /></div></router-link></div>
            <div v-else class="button-bookmarks" @click="bookmark"><icons v-if="!isBookmarked" :iconName="'heart'" /><icons v-else :iconName="'heartRed'" /></div>
          </div>
        </div>
      </div>

      <div v-if="space.description">
        <h2>About</h2>
        <span style="white-space: pre-line">{{ space.description }}</span>
      </div>

      <span v-if="isSocial">
        <br>
        <h2>Social</h2>

        <span v-if="space.twitter != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.twitter" target="_blank"><icons :iconName="'twitter'" /></a></div>
        </span>
        <span v-if="space.facebook != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.facebook" target="_blank"><icons :iconName="'facebook'" /></a></div>
        </span>
        <span v-if="space.instagram != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.instagram" target="_blank"><icons :iconName="'instagram'" /></a></div>
        </span>
        <span v-if="space.pinterest != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.pinterest" target="_blank"><icons :iconName="'pinterest'" /></a></div>
        </span>
        <span v-if="space.telegram != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.telegram" target="_blank"><icons :iconName="'telegram'" /></a></div>
        </span>
        <span v-if="space.github != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.github" target="_blank"><icons :iconName="'github'" /></a></div>
        </span>
        <span v-if="space.youtube != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.youtube" target="_blank"><icons :iconName="'youtube'" /></a></div>
        </span>
        <span v-if="space.tiktok != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.tiktok" target="_blank"><icons :iconName="'tiktok'" /></a></div>
        </span>
        <span v-if="space.linkedin != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.linkedin" target="_blank"><icons :iconName="'linkedin'" /></a></div>
        </span>
        <span v-if="space.linktree != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.linktree" target="_blank"><icons :iconName="'linktree'" /></a></div>
        </span>
        <span v-if="space.substack != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.substack" target="_blank"><icons :iconName="'substack'" /></a></div>
        </span>
        <span v-if="space.medium != ''">
          <div style="display:inline-block;width:30px;margin-right:10px;" class="buttons-hover"><a :href="space.medium" target="_blank"><icons :iconName="'medium'" /></a></div>
        </span>
      </span>


      <div v-if="space.photo != 0">
        <br>

        <h2>Photos</h2>

        <img :src="fullSize" class="space-photo">
        <img v-for="(photo, index) in space.photos" :key="index" @click="fullSize = '/storage/spaces/'+photo.filename" :src="'/storage/spaces/'+photo.filename" class="space-photo-small cursor-pointer">

      </div>

      <div style="height:20px;clear:both;"></div>


      <h2>Location</h2>

      <div v-if="space.address">{{ space.address }}</div>

      <div v-else-if="space.formatted_address">Address: {{ space.formatted_address }}</div>

      Latitude: {{ space.latitude }}, Longitude: {{ space.longitude }}
      <br>
      See it on 
      <a v-if="space.google_url" :href="space.google_url" target="_blank">
        Google Maps
      </a>
      <a v-else :href="'https://www.google.com/maps/place/'+space.latitude+','+space.longitude" target="_blank">
        Google Maps
      </a>
      <div style="height:10px;"></div>

      <map-space :locations="[ { 'latitude': space.latitude, 'longitude': space.longitude } ]" />
      
      <div style="height:20px;"></div>

      <h3>References</h3>
      <span v-if="ratingsAll.length == 0">
        No references yet
      </span>
      <span v-else>
        <div v-for="(rating, index) in ratingsAll">
          <div v-if="rating['rated_by_user']" style="background-color:aliceblue;padding:15px;border:1px solid grey;border-radius:5px;margin-bottom:20px;">
            <div style="display:grid;grid-template-columns:20px 5px 1fr">
              <profile-pic :profile_pic="rating['rated_by_user']['profile_pic']" :avatar="rating['rated_by_user']['avatar']" :photo="rating['rated_by_user']['photo']" style="height:17px;width:17px;overflow:hidden;"/>
              <div></div>
              <div><router-link :to="'/mate/'+ (rating['rated_by_user']['username'] ? rating['rated_by_user']['username'] : rating['rated_by_user']['uid'])"><b>{{ rating['rated_by_user']['name'] }}</b></router-link> {{ formatTimestamp(rating['created_at']) }}</div>
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
              <!-- {{ edit rating }} -->
              
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
        </div>
      </span>
        
      <div v-if="!currentUserRated && userLoggedIn">
        <div style="height:20px;"></div>
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

      <div style="height:20px;"></div>

      <div v-if="caUser" style="clear:both;position:fixed;bottom:5px;right:15px;z-index:999;">
        <div style="height:20px;" ></div>
        <div v-if="Number(caUser.userId) === Number(space.user_id) || Number(caUser.userId) == 1">
          <router-link :to="'/space/'+space.uid+'/edit'">✎</router-link>
        </div>
      </div>

      <div style="clear:both;"></div>
    </div>
    <div v-else>
      <p>Loading space data...</p>
    </div>
    
    <div class="space-sidebar">

      <div style="border-radius: 5px;width:100%;padding:25px 10px 5px 10px;background-color: rgb(246, 246, 246);border: 1px solid rgb(204, 204, 204);">
        <h2 style="margin-top:0px;">CoMatch Score</h2>

        <div v-if="coliveFitScore && userLoggedIn" style="font-size:24px;font-weight:bold;">
          {{ coliveFitScore.toFixed(2) }}%
          <div style="height:10px;"></div>
        </div>

        <div>This score reflects how well your living habits match with this coliving space. Higher scores mean better compatibility.</div>

        <div v-if="!userLoggedIn">
          <div style="height:20px;"></div>
          <!-- <router-link :to="`/inbox/space/${$route.params.uid}`"><button class="action-button button-1" style="width:160px;">Login</button></router-link> -->
          <router-link to="/login"><b>Log in</b></router-link> to see your score
        </div>

        <div style="height:20px;"></div>
      </div>

      
      <div style="height:20px;"></div>

      <div v-if="space">
        <div v-if="space.email">
          <div v-if="!isJoined" style="border-radius: 5px;width:100%;padding:25px 10px 5px 10px;background-color: rgb(246, 246, 246);border: 1px solid rgb(204, 204, 204);">
            <h2 style="margin-top:0px;">Join This Space</h2>
            Start a conversation with the host. To join the space, include the join request.

            <div style="height:20px;"></div>
            
            <router-link v-if="userLoggedIn" :to="`/inbox/space/${$route.params.uid}`"><button class="action-button button-1" style="width:160px;">Message</button></router-link>
            <span v-else><router-link to="/login"><b>Log in</b></router-link> to join this space</span>

            <div style="height:20px;"></div>
          </div>
          
          <div v-else style="border-radius: 5px;width:100%;padding:25px 10px 5px 10px;background-color: rgb(246, 246, 246);border: 1px solid rgb(204, 204, 204);">
            <h2 style="margin-top:0px;">Currently Coliving</h2>
            Manage your coliving experience in the Hub.

            <div style="height:20px;"></div>
            
            <router-link v-if="userLoggedIn" :to="`/space/${$route.params.uid}/hub`"><button class="action-button button-1" style="width:160px;">Hub</button></router-link>

            <div style="height:20px;"></div>
          </div>

          <div style="height:20px;"></div>
        </div>
      </div>


      <div v-if="space">
        <div  v-if="space.claimed == 0" style="border-radius: 5px;width:100%;padding:25px 10px 5px 10px;background-color: rgb(246, 246, 246);border: 1px solid rgb(204, 204, 204);">
          <h2 style="margin-top:0px;">Claim This Space</h2>
          If you represent this coliving space, claim your profile now.

          <div style="height:20px;"></div>
          
          <router-link v-if="userLoggedIn" :to="`/space/${$route.params.uid}/claim`"><button class="action-button button-1" style="width:160px;">Claim</button></router-link>
          <span v-else><router-link to="/login"><b>Log in</b></router-link> to claim this space</span>

          <div style="height:20px;"></div>
        </div>
      </div>

    </div>

  </div>
</template>

<style>
.space-name {
  font-size: 22px;
  font-weight: bold;
}

.space-photo {
  width: 100%;
  border-radius: 5px;
}

.space-photo-small {
  width: 100px;
  float: left;
  display: inline;
  margin-right:2px;
  border-radius: 5px;
}

.button-bookmarks {
  position:absolute;right:0px;width:30px;margin-left:10px;cursor:pointer;
}

.button-bookmarks-2 {
  width:40px;cursor:pointer;margin:0 auto;margin-top:0px;
}

.space-sidebar {
  max-width:300px;float:right;text-align: center;position:relative;
}

@media screen and (max-width: 980px) {
  .space-sidebar {
    float: left;
    margin-bottom: 40px;
    max-width:100%;
    margin-top: 30px;
  }
}
</style>