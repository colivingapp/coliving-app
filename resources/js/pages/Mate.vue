<template>
  <div class="content">

    <div v-if="mate && mate.private != 0" class="mp-private">
      This is a private profile. Only the owner can see it.
    </div>

    <div v-if="mate">
      <div class="mp-top">
        <aside class="mp-side">
          <div class="mp-card mp-identity">
            <profile-pic
              :profile_pic="mate.profile_pic"
              :avatar="mate.avatar"
              :photo="mate.photo"
              class="mp-avatar"
            />
            <h1 class="mp-name">{{ displayName }}</h1>
            <p v-if="mate.username" class="mp-handle">@{{ mate.username }}</p>

            <div v-if="countryCode" class="mp-place">
              <span :class="'fi fi-' + countryCode"></span>
              <span>{{ countryLabel }}</span>
            </div>

            <router-link
              v-if="currentMateUID != mate.uid && userLoggedIn"
              :to="'/inbox/mate/'+mate.uid"
              class="mp-msg-link"
            >
              <button type="button" class="btn btn-lg btn-primary mp-msg">Send message</button>
            </router-link>
            <router-link v-else-if="!userLoggedIn" to="/login" class="mp-msg-link">
              <button type="button" class="btn btn-lg btn-primary mp-msg">Send message</button>
            </router-link>

            <div v-if="showDetails && socials.length" class="mp-social">
              <a
                v-for="social in socials"
                :key="social.name"
                :href="social.href"
                :aria-label="social.label"
                target="_blank"
                rel="nofollow noopener"
                class="mp-social-btn buttons-hover"
              >
                <icons :iconName="social.name" />
              </a>
            </div>

          </div>
        </aside>

        <div class="mp-main">
          <p class="mp-eyebrow">Coliving mate</p>
          <h2 class="mp-about-title">{{ aboutHeading }}</h2>
          <div v-if="showDetails && mate.hobbies_interests" class="mp-bio">{{ mate.hobbies_interests }}</div>
          <div v-else-if="showDetails" class="mp-bio mp-bio-empty">No bio yet.</div>

          <div class="mp-facts">
            <div class="mp-fact">
              <b>{{ visitedCount }}</b>
              <span>Countries visited</span>
            </div>
            <div class="mp-fact">
              <b>{{ referenceCount }}</b>
              <span>References</span>
            </div>
            <div class="mp-fact">
              <b>{{ positivePercentLabel }}</b>
              <span>Positive</span>
            </div>
            <div class="mp-fact">
              <b>{{ memberSinceYear || '—' }}</b>
              <span>Member since</span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showDetails && mate.visited_countries && mate.visited_countries.length > 0">
        <div style="height: 60px;"></div>

        <h2 style="margin-top:0px;">Visited Countries</h2>
        <div style="height: 12px;"></div>

        <VisitedCountries :visitedCountries="mate.visited_countries" :type="'profile'"></VisitedCountries>
      </div>

      <div v-if="showDetails" class="mp-sec">
        <h2 class="mp-sec-title">Lifestyle Match</h2>
        <div class="mp-lifestyle">
          <div class="mp-lifestyle-head">
            <div v-if="coliveFitScore && canCompare" class="mp-lifestyle-score">
              {{ coliveFitScore.toFixed(0) }}%
              <span>compatibility with you</span>
            </div>
            <div v-else class="mp-lifestyle-score-spacer"></div>
            <div class="mp-radar-legend">
              <span class="mp-legend-item is-them">
                <i></i>{{ displayName }}
              </span>
              <span v-if="canCompare" class="mp-legend-item is-you">
                <i></i>You
              </span>
            </div>
          </div>

          <div class="mp-lifestyle-body">
            <div class="mp-radar" aria-hidden="true">
              <svg class="mp-radar-svg" viewBox="0 0 280 280" role="img">
                <polygon
                  v-for="(ring, index) in radarRings"
                  :key="'ring-'+index"
                  :points="ring"
                  class="mp-radar-ring"
                  :class="{ 'is-mid': index === 2 }"
                />
                <line
                  v-for="(spoke, index) in radarSpokes"
                  :key="'spoke-'+index"
                  :x1="spoke.x1"
                  :y1="spoke.y1"
                  :x2="spoke.x2"
                  :y2="spoke.y2"
                  class="mp-radar-spoke"
                />
                <polygon v-if="themRadar.points" :points="themRadar.points" class="mp-radar-fill is-them" />
                <polygon v-if="youRadar.points" :points="youRadar.points" class="mp-radar-fill is-you" />
                <circle
                  v-for="(dot, index) in themRadar.dots"
                  :key="'them-'+index"
                  :cx="dot.x"
                  :cy="dot.y"
                  r="4.5"
                  class="mp-radar-dot is-them"
                />
                <circle
                  v-for="(dot, index) in youRadar.dots"
                  :key="'you-'+index"
                  :cx="dot.x"
                  :cy="dot.y"
                  r="4.5"
                  class="mp-radar-dot is-you"
                />
              </svg>
            </div>

            <div class="mp-lifestyle-axes">
              <div v-for="axis in lifestyleAxes" :key="axis.key" class="mp-axis-row">
                <span>{{ axis.left }}</span>
                <span class="mp-axis-track">
                  <i class="mp-axis-dot is-them" :style="{ left: fitPercent(mate, axis.key) }"></i>
                  <i v-if="canCompare" class="mp-axis-dot is-you" :style="{ left: fitPercent(myFits, axis.key) }"></i>
                </span>
                <span>{{ axis.right }}</span>
              </div>
            </div>
          </div>

          <p v-if="lifestyleInsight" class="mp-lifestyle-insight">{{ lifestyleInsight }}</p>

          <div v-if="!userLoggedIn" class="mp-lock-note">
            To overlay your lifestyle and see a match score, please <router-link to="/register">sign up</router-link> or <router-link to="/login">log in</router-link>.
          </div>
        </div>
      </div>

      <div v-if="showDetails" class="mp-sec">
        <h2 class="mp-sec-title">References</h2>
        <p v-if="ratingsAll.length === 0 || !ratingsAll.some(rating => rating['rated_by_user'])" class="mp-empty">
          No references yet.
        </p>
        <div v-else class="mp-reviews">
          <template v-for="(item, index) in ratingsAll" :key="item.id || index">
            <article v-if="item['rated_by_user']" class="mp-rev">
              <profile-pic
                :profile_pic="item['rated_by_user']['profile_pic']"
                :avatar="item['rated_by_user']['avatar']"
                :photo="item['rated_by_user']['photo']"
                class="mp-rev-av"
              />
              <div class="mp-rev-body">
                <div class="mp-rev-head">
                  <router-link :to="'/mate/'+ (item['rated_by_user']['username'] ? item['rated_by_user']['username'] : item['rated_by_user']['uid'])">
                    <b>{{ item['rated_by_user']['name'] }}</b>
                  </router-link>
                  <span :class="item['rating'] == 1 ? 'mp-ok' : 'mp-bad'">· {{ item['rating'] == 1 ? 'Positive' : 'Negative' }}</span>
                </div>
                <div class="mp-rev-date">{{ formatDate(item['created_at']) }}</div>
                <div class="mp-rev-text" v-html="item['reference'].replace(/\n/g, '<br>')"></div>

                <div v-if="item['rated_by_user']['currentUser']" class="mp-rev-edit-toggle">
                  <span @click="editRating[index] = !editRating[index]" class="link">
                    <span v-if="!editRating[index]">Edit</span>
                    <span v-else>Close</span>
                  </span>
                </div>

                <div v-if="editRating[index]" class="mp-ref-form">
                  <h3>Edit your reference</h3>
                  <select v-model="ratingEdit">
                    <option value="1">Positive</option>
                    <option value="0">Negative</option>
                  </select>
                  <textarea class="textarea" placeholder="" v-model="referenceTextEdit"></textarea>
                  <div class="character-count">{{ characterCountEdit }}/{{ maxCharacterCount }}</div>
                  <button type="button" class="btn btn-md btn-join" @click="submitRatingEdit">Submit</button>
                </div>
              </div>
            </article>
          </template>
        </div>

        <div v-if="currentMateUID != mate.uid && !currentUserRated && userLoggedIn" class="mp-ref-form mp-ref-write">
          <h3>Write a reference</h3>
          <select v-model="rating">
            <option v-for="option in ratings" :key="option" :value="option">{{ option }}</option>
          </select>
          <textarea class="textarea" placeholder="" v-model="referenceText"></textarea>
          <div class="character-count">{{ characterCount }}/{{ maxCharacterCount }}</div>
          <button type="button" class="btn btn-md btn-join" @click="submitRating" v-if="!ratingSubmitted">Submit</button>
          <span v-else>Sending...</span>
        </div>
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
import "/node_modules/flag-icons/css/flag-icons.min.css";

import ProfilePic from '../components/ProfilePic.vue';
import Icons from '../components/Icons.vue';
import VisitedCountries from '../form/VisitedCountries.vue';
import { MATE_SOCIAL_NETWORKS, normalizeSocialUrl } from '../socialUrls.js';

const lifestyleAxes = [
  { key: 'orgSpont', left: 'Organized', right: 'Spontaneous' },
  { key: 'quietLoud', left: 'Quiet', right: 'Loud' },
  { key: 'earlyNight', left: 'Early bird', right: 'Night owl' },
  { key: 'workFun', left: 'Work', right: 'Fun' },
  { key: 'expTrad', left: 'Traditional', right: 'Experimental' },
  { key: 'multiLocal', left: 'Local', right: 'Global' },
];

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

const regionNames = typeof Intl !== 'undefined' && Intl.DisplayNames
  ? new Intl.DisplayNames(['en'], { type: 'region' })
  : null;

const showDetails = computed(() => {
  if (!mate.value) return false;
  if (Number(mate.value.private) === 0) return true;
  return !!currentMateUID.value && currentMateUID.value === mate.value.uid;
});

const displayName = computed(() => {
  if (!mate.value) return '';
  return mate.value.name || mate.value.username || 'Coliving mate';
});

const aboutHeading = computed(() => `About ${displayName.value}`);

const countryCode = computed(() => {
  const code = String(mate.value?.location_country || '').toLowerCase();
  return code && code !== 'none' ? code : '';
});

const countryLabel = computed(() => {
  if (!countryCode.value) return '';
  try {
    return regionNames?.of(countryCode.value.toUpperCase()) || countryCode.value.toUpperCase();
  } catch (error) {
    return countryCode.value.toUpperCase();
  }
});

const visitedCount = computed(() => {
  const list = mate.value?.visited_countries;
  return Array.isArray(list) ? list.length : 0;
});

const visibleRatings = computed(() => ratingsAll.value.filter(rating => rating && rating.rated_by_user));

const referenceCount = computed(() => visibleRatings.value.length);

const positivePercentLabel = computed(() => {
  const list = visibleRatings.value;
  if (!list.length) return '—';
  const positive = list.filter(item => Number(item.rating) === 1).length;
  return Math.round((positive / list.length) * 100) + '%';
});

const memberSinceYear = computed(() => {
  const stamp = mate.value?.created_at;
  if (!stamp) return '';
  const date = new Date(stamp);
  if (Number.isNaN(date.getTime())) return '';
  return String(date.getFullYear());
});

const socials = computed(() => {
  if (!mate.value) return [];
  return MATE_SOCIAL_NETWORKS
    .map(network => {
      const href = normalizeSocialUrl(network.name, mate.value[network.name]);
      return href ? { ...network, href } : null;
    })
    .filter(Boolean);
});

const myFits = computed(() => window.mate || null);

const canCompare = computed(() => (
  !!userLoggedIn.value
  && !!myFits.value
  && !!mate.value
  && currentMateUID.value !== mate.value.uid
));

const RADAR = { cx: 140, cy: 140, r: 118, n: 6 };

function clampFit(value) {
  const n = Number(value);
  return Number.isFinite(n) ? Math.min(10, Math.max(0, n)) : 5;
}

function fitPercent(source, key) {
  return (clampFit(source?.[key]) / 10) * 100 + '%';
}

function radarPolar(index, ratio) {
  const angle = (index / RADAR.n) * Math.PI * 2 - Math.PI / 2;
  return {
    x: RADAR.cx + Math.cos(angle) * RADAR.r * ratio,
    y: RADAR.cy + Math.sin(angle) * RADAR.r * ratio,
    angle,
  };
}

function radarShape(source) {
  if (!source) return { points: '', dots: [] };
  const dots = lifestyleAxes.map((axis, index) => radarPolar(index, clampFit(source[axis.key]) / 10));
  return {
    points: dots.map(dot => `${dot.x.toFixed(1)},${dot.y.toFixed(1)}`).join(' '),
    dots,
  };
}

const radarRings = [0.2, 0.4, 0.6, 0.8, 1].map(ratio => (
  lifestyleAxes.map((_, index) => {
    const p = radarPolar(index, ratio);
    return `${p.x.toFixed(1)},${p.y.toFixed(1)}`;
  }).join(' ')
));

const radarSpokes = lifestyleAxes.map((_, index) => {
  const end = radarPolar(index, 1);
  return { x1: RADAR.cx, y1: RADAR.cy, x2: end.x, y2: end.y };
});

const themRadar = computed(() => radarShape(mate.value));
const youRadar = computed(() => canCompare.value ? radarShape(myFits.value) : { points: '', dots: [] });

function poleWord(axis, source) {
  const value = clampFit(source?.[axis.key]);
  if (value <= 4) return axis.left;
  if (value >= 6) return axis.right;
  return `a mix of ${axis.left.toLowerCase()} and ${axis.right.toLowerCase()}`;
}

const lifestyleInsight = computed(() => {
  if (!canCompare.value || !mate.value || !myFits.value) return '';
  let maxDiff = -1;
  let maxAxis = lifestyleAxes[0];
  lifestyleAxes.forEach((axis) => {
    const diff = Math.abs(clampFit(mate.value[axis.key]) - clampFit(myFits.value[axis.key]));
    if (diff > maxDiff) {
      maxDiff = diff;
      maxAxis = axis;
    }
  });
  if (maxDiff <= 2) {
    return 'Your living habits sit close together on every scale.';
  }
  const youPole = poleWord(maxAxis, myFits.value);
  const themPole = poleWord(maxAxis, mate.value);
  if (youPole === themPole) {
    return `Closest overall, with the largest gap on ${maxAxis.left.toLowerCase()} vs ${maxAxis.right.toLowerCase()}.`;
  }
  return `Biggest difference: you lean ${youPole.toLowerCase()}, ${displayName.value} leans ${themPole.toLowerCase()}.`;
});

onMounted(() => {
  fetchMate();
});

function formatDate(timestamp) {
  const date = new Date(timestamp);
  if (Number.isNaN(date.getTime())) return '';
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

const fetchMate = async () => {
  document.title =  'Coliving Mate - ' + route.params.mate;
  try {
    const response = await fetch('/api/mate/' + encodeURIComponent(route.params.mate));
    const data = await response.json();
    const profile = data?.[0]?.[0];
    if (!profile) {
      mate.value = null;
      return;
    }

    mate.value = profile;
    const rawVisited = profile.visited_countries;
    if (Array.isArray(rawVisited)) {
      mate.value.visited_countries = rawVisited;
    } else {
      try {
        const parsed = JSON.parse(rawVisited || '[]');
        mate.value.visited_countries = Array.isArray(parsed) ? parsed : [];
      } catch (error) {
        mate.value.visited_countries = [];
      }
    }

    ratingsAll.value = Array.isArray(data[1]) ? data[1] : [];
    currentMateUID.value = data[2] || '';
    for (let i = 0; i < ratingsAll.value.length; i++) {
      if (ratingsAll.value[i].rated_by_user && (ratingsAll.value[i].rated_by_user.uid == currentMateUID.value)) {
        currentUserRated.value = true;
        ratingsAll.value[i].rated_by_user.currentUser = 'true';
        referenceTextEdit.value = ratingsAll.value[i].reference;
        ratingEdit.value = ratingsAll.value[i].rating;
      }
    }

    if (window.mate) {
      coliveFitScore.value = calculateColiveFitScore({
        orgSpont: mate.value.orgSpont,
        quietLoud: mate.value.quietLoud,
        earlyNight: mate.value.earlyNight,
        workFun: mate.value.workFun,
        expTrad: mate.value.expTrad,
        multiLocal: mate.value.multiLocal
      }, {
        orgSpont: window.mate.orgSpont,
        quietLoud: window.mate.quietLoud,
        earlyNight: window.mate.earlyNight,
        workFun: window.mate.workFun,
        expTrad: window.mate.expTrad,
        multiLocal: window.mate.multiLocal
      });
    }
  } catch (error) {
    console.error(error);
    mate.value = null;
  } finally {
    isLoaded.value = true;
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
.mp-private {
  margin-bottom: 30px;
  background-color: red;
  color: white;
  padding: 5px;
  border-radius: 5px;
}

.mp-top {
  display: grid;
  grid-template-columns: 260px minmax(0, 1fr);
  gap: 28px;
  align-items: start;
}

.mp-side {
  position: sticky;
  top: 76px;
}

.mp-card {
  background: #fff;
  border: 1px solid #ececec;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(20, 20, 20, 0.05);
}

.mp-identity {
  padding: 24px 20px 20px;
  text-align: center;
}

.mp-avatar {
  width: 116px;
  height: 116px;
  overflow: hidden;
  border-radius: 50%;
  margin: 0 auto 14px;
  display: block;
  box-shadow: 0 0 0 4px #fff, 0 4px 18px rgba(0, 0, 0, 0.1);
}

.mp-avatar > div {
  width: 100%;
  height: 100%;
}

.mp-name {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.mp-handle {
  margin: 4px 0 0;
  color: #6e6e6e;
  font-size: 14px;
}

.mp-place {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 12px;
  font-size: 13px;
  color: #333;
}

.mp-place .fi {
  border-radius: 2px;
}

.mp-msg-link {
  display: block;
  margin-top: 16px;
}

.mp-msg {
  width: 100%;
}

.mp-social {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 16px;
}

.mp-social-btn {
  width: 30px;
  height: 30px;
  display: inline-block;
}

.mp-eyebrow {
  margin: 0 0 6px;
  font-size: 11px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #888;
  font-weight: 700;
}

.mp-about-title {
  margin: 0 0 12px;
  font-size: 22px;
  font-weight: 400;
}

.mp-bio {
  margin: 0 0 18px;
  color: #333;
  line-height: 1.65;
  white-space: pre-line;
  font-size: 16px;
}

.mp-bio-empty {
  color: #888;
}

.mp-facts {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.mp-fact {
  background: #fff;
  border: 1px solid #ececec;
  border-radius: 12px;
  padding: 12px 14px;
}

.mp-fact b {
  display: block;
  font-size: 18px;
}

.mp-fact span {
  font-size: 12px;
  color: #6e6e6e;
}

.mp-sec {
  margin-top: 48px;
  margin-bottom: 24px;
}

.mp-sec-title {
  margin: 0 0 14px;
  font-size: 18px;
}

.mp-lifestyle {
  background: #fff;
  border: 1px solid #ececec;
  border-radius: 16px;
  padding: 22px 20px 18px;
  box-shadow: 0 6px 20px rgba(20, 20, 20, 0.05);
}

.mp-lifestyle-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 12px;
}

.mp-lifestyle-score {
  font-size: 28px;
  font-weight: 800;
  color: #0086da;
}

.mp-lifestyle-score span {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: #777;
  margin-top: 2px;
}

.mp-lifestyle-body {
  display: grid;
  grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
  gap: 8px 28px;
  align-items: center;
}

.mp-radar-legend {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 6px;
}

.mp-legend-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #555;
  font-weight: 600;
}

.mp-legend-item i {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.mp-legend-item.is-them i,
.mp-axis-dot.is-them,
.mp-radar-dot.is-them {
  background: #0086da;
}

.mp-legend-item.is-you i,
.mp-axis-dot.is-you,
.mp-radar-dot.is-you {
  background: #e8833a;
}

.mp-radar {
  position: relative;
  width: 100%;
  max-width: 280px;
  margin: 0;
}

.mp-radar-svg {
  width: 100%;
  height: auto;
  display: block;
}

.mp-radar-ring {
  fill: none;
  stroke: #e6eef4;
  stroke-width: 1;
}

.mp-radar-ring.is-mid {
  stroke: #c5d8e8;
  stroke-dasharray: 4 4;
}

.mp-radar-spoke {
  stroke: #d7e4ee;
  stroke-width: 1;
}

.mp-radar-fill {
  fill-opacity: 0.22;
  stroke-width: 2;
  stroke-linejoin: round;
}

.mp-radar-fill.is-them {
  fill: #0086da;
  stroke: #0086da;
}

.mp-radar-fill.is-you {
  fill: #e8833a;
  stroke: #e8833a;
}

.mp-radar-dot {
  stroke: #fff;
  stroke-width: 1.5;
}

.mp-lifestyle-insight {
  font-size: 14px;
  color: #555;
  line-height: 1.55;
  margin: 14px 0 0;
}

.mp-lifestyle-axes {
  display: grid;
  gap: 14px;
  margin: 0;
}

.mp-axis-row {
  display: grid;
  grid-template-columns: 88px 1fr 88px;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  color: #999;
}

.mp-axis-row span:first-child {
  text-align: right;
}

.mp-axis-track {
  height: 6px;
  border-radius: 999px;
  background: #eef2f6;
  position: relative;
}

.mp-axis-dot {
  position: absolute;
  top: -3px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #0086da;
  transform: translateX(-50%);
  box-shadow: 0 0 0 2px #fff;
}

.mp-lock-note {
  font-size: 13px;
  color: #777;
  background: #f7fafd;
  border-radius: 10px;
  padding: 12px 14px;
  margin-top: 14px;
}

.mp-empty {
  color: #777;
}

.mp-reviews {
  display: grid;
  gap: 12px;
}

.mp-rev {
  display: grid;
  grid-template-columns: 36px 1fr;
  gap: 12px;
  background: #fff;
  border: 1px solid #ececec;
  border-radius: 14px;
  padding: 14px 16px;
  box-shadow: 0 6px 20px rgba(20, 20, 20, 0.04);
}

.mp-rev-av {
  width: 36px;
  height: 36px;
  overflow: hidden;
  border-radius: 50%;
}

.mp-rev-av > div {
  width: 100%;
  height: 100%;
}

.mp-rev-head {
  font-size: 14px;
}

.mp-ok {
  color: #1e8a44;
  font-size: 12px;
  font-weight: 700;
}

.mp-bad {
  color: #c0392b;
  font-size: 12px;
  font-weight: 700;
}

.mp-rev-date {
  font-size: 12px;
  color: #888;
  margin-top: 2px;
}

.mp-rev-text {
  margin: 8px 0 0;
  font-size: 14px;
  line-height: 1.55;
  white-space: pre-line;
}

.mp-rev-edit-toggle {
  margin-top: 8px;
  font-size: 14px;
  text-align: right;
}

.mp-ref-form {
  margin-top: 12px;
}

.mp-ref-write {
  margin-top: 24px;
}

.character-count {
  float: right;
  font-size: 12px;
  color: gray;
}

@media (max-width: 700px) {
  .mp-top,
  .mp-facts {
    grid-template-columns: 1fr;
  }

  .mp-side {
    position: static;
  }

  .mp-lifestyle-head {
    flex-direction: column;
    gap: 8px;
  }

  .mp-lifestyle-body {
    grid-template-columns: 1fr;
    justify-items: center;
    gap: 18px;
  }

  .mp-radar {
    max-width: 240px;
  }

  .mp-radar-legend {
    justify-content: flex-start;
  }

  .mp-lifestyle-axes {
    width: 100%;
  }

  .mp-axis-row {
    grid-template-columns: 74px 1fr 74px;
  }
}
</style>
