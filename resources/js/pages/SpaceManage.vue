<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';

import ModalFeedback from '../components/ModalFeedback.vue';
import ProfilePic from '../components/ProfilePic.vue';
import { spacePhotoUrl } from '../mediaUrls.js';

const route = useRoute();
const { uid } = route.params;

const space = ref(null);
const isLoadingSpace = ref(true);

const mates = ref([]);
const isLoadingMates = ref(true);

const jr = ref([]);
const isLoadingJr = ref(true);
const jrActionLoading = ref({});
const jrActionError = ref({});

const activeTab = ref('members');
const membersFilter = ref('current');
const requestsFilter = ref('pending');

const modalFeedback = ref(false);

function mateDisplayName(mate) {
  if (mate.account_deleted) return 'Deleted account';
  if (mate.name) return mate.name;
  if (mate.username) return mate.username;
  return mate.uid;
}

const currentMembers = computed(() => mates.value.filter(m => m.status !== 'left'));
const pastMembers = computed(() => mates.value.filter(m => m.status === 'left'));

const pendingRequestsCount = computed(() => jr.value.filter(r => r.status === 'pending').length);

const requestCounts = computed(() => ({
  pending: jr.value.filter(r => r.status === 'pending').length,
  accepted: jr.value.filter(r => r.status === 'accepted').length,
  declined: jr.value.filter(r => r.status === 'declined').length,
  all: jr.value.length,
}));

const filteredRequests = computed(() => {
  const sorted = [...jr.value].reverse();
  if (requestsFilter.value === 'all') return sorted;
  return sorted.filter(r => r.status === requestsFilter.value);
});

const fetchSpace = async () => {
  isLoadingSpace.value = true;
  try {
    const response = await axios.get(`/api/space/${uid}`);
    space.value = response.data.space;
  } catch (error) {
    console.error(error);
  } finally {
    isLoadingSpace.value = false;
  }
};

function fetchMembers() {
  isLoadingMates.value = true;
  axios.get('/api/space/' + uid + '/space-data')
    .then((result) => {
      if (result.data == 'unauthenticated' || result.data == 'not_a_member') {
        mates.value = [];
      } else {
        const list = result.data[0] || [];
        list.forEach(mate => {
          mate.account_deleted = !!mate.account_deleted;
          mate.displayName = mateDisplayName(mate);
        });
        mates.value = list;
      }
    })
    .catch((error) => console.error(error))
    .finally(() => { isLoadingMates.value = false; });
}

function fetchJoinRequests() {
  isLoadingJr.value = true;
  axios.get(`/api/space/${uid}/jr`)
    .then((result) => {
      jr.value = Array.isArray(result.data) ? result.data : [];
    })
    .catch((error) => console.error(error))
    .finally(() => { isLoadingJr.value = false; });
}

function acceptRequest(request) {
  if (jrActionLoading.value[request.id]) return;
  jrActionLoading.value[request.id] = true;
  jrActionError.value[request.id] = '';

  axios.post(`/api/join_requests/${request.id}/accept`)
    .then((resp) => {
      if (resp.data === 'success') {
        request.status = 'accepted';
        fetchMembers();
      } else {
        jrActionError.value[request.id] = resp.data || 'Something went wrong.';
      }
    })
    .catch(() => { jrActionError.value[request.id] = 'Something went wrong.'; })
    .finally(() => { jrActionLoading.value[request.id] = false; });
}

function declineRequest(request) {
  if (jrActionLoading.value[request.id]) return;
  if (!confirm('Decline this join request?')) return;

  jrActionLoading.value[request.id] = true;
  jrActionError.value[request.id] = '';

  axios.post(`/api/join_requests/${request.id}/${request.mate_uid}/decline`)
    .then((resp) => {
      if (resp.data === 'success') {
        request.status = 'declined';
      } else {
        jrActionError.value[request.id] = resp.data || 'Something went wrong.';
      }
    })
    .catch(() => { jrActionError.value[request.id] = 'Something went wrong.'; })
    .finally(() => { jrActionLoading.value[request.id] = false; });
}

onMounted(() => {
  fetchSpace();
  fetchMembers();
  fetchJoinRequests();
});
</script>

<template>
  <div class="content dash">

    <div v-if="isLoadingSpace" class="spinner-container">
      <div class="spinner"></div>
    </div>

    <div v-else-if="space">

      <div class="dash-header">
        <div class="dash-header-photo">
          <img
            :src="space.first_photo ? spacePhotoUrl(space.first_photo) : '/img/coliving-app-logo-symbol.png'"
            @error="$event.target.src = '/img/coliving-app-logo-symbol.png'"
            :alt="space.name"
          >
        </div>
        <div class="dash-header-info">
          <div class="dash-eyebrow">Space Dashboard</div>
          <h1 class="dash-title">{{ space.name }}</h1>
          <div v-if="space.address || space.country" class="dash-subtitle">{{ space.address || space.country }}</div>
        </div>
        <div class="dash-header-actions">
          <router-link :to="'/space/' + uid"><button type="button" class="btn btn-sm btn-quiet">View public page</button></router-link>
          <router-link :to="'/space/' + uid + '/edit'"><button type="button" class="btn btn-sm btn-quiet">Edit space</button></router-link>
        </div>
      </div>

      <div class="dash-stats">
        <div class="dash-stat-card">
          <div class="dash-stat-value">{{ currentMembers.length }}</div>
          <div class="dash-stat-label">Current members</div>
        </div>
        <div class="dash-stat-card">
          <div class="dash-stat-value">{{ pastMembers.length }}</div>
          <div class="dash-stat-label">Past members</div>
        </div>
        <div class="dash-stat-card" :class="{ 'dash-stat-highlight': pendingRequestsCount > 0 }">
          <div class="dash-stat-value">{{ pendingRequestsCount }}</div>
          <div class="dash-stat-label">Pending join requests</div>
        </div>
      </div>

      <div class="dash-tabs">
        <div class="page-menu-button-outer">
          <div class="page-menu-button" @click="activeTab = 'members'">
            <span class="inbox-type-link" :class="{ active: activeTab === 'members' }">Members</span>
          </div>
          <div class="page-menu-underline" v-if="activeTab === 'members'"></div>
        </div>
        <div class="page-menu-button-outer" style="margin-left:20px;">
          <div class="page-menu-button" @click="activeTab = 'requests'">
            <span class="inbox-type-link" :class="{ active: activeTab === 'requests' }">
              Join Requests
              <span v-if="pendingRequestsCount > 0" class="dash-badge">{{ pendingRequestsCount }}</span>
            </span>
          </div>
          <div class="page-menu-underline" v-if="activeTab === 'requests'"></div>
        </div>
        <div class="page-menu-button-outer" style="margin-left:20px;">
          <div class="page-menu-button" @click="activeTab = 'feedback'">
            <span class="inbox-type-link" :class="{ active: activeTab === 'feedback' }">Feedback</span>
          </div>
          <div class="page-menu-underline" v-if="activeTab === 'feedback'"></div>
        </div>
      </div>

      <!-- MEMBERS TAB -->
      <div v-if="activeTab === 'members'" class="dash-panel">
        <div class="dash-subtabs">
          <button type="button" class="dash-chip" :class="{ 'dash-chip-active': membersFilter === 'current' }" @click="membersFilter = 'current'">Current ({{ currentMembers.length }})</button>
          <button type="button" class="dash-chip" :class="{ 'dash-chip-active': membersFilter === 'past' }" @click="membersFilter = 'past'">Past ({{ pastMembers.length }})</button>
        </div>

        <div v-if="isLoadingMates" class="dash-empty">Loading members...</div>

        <div v-else-if="membersFilter === 'current'">
          <div v-if="currentMembers.length === 0" class="dash-empty">You have no current members yet.</div>
          <div v-else class="dash-list dash-members-grid">
            <router-link
              v-for="mate in currentMembers"
              :key="mate.id"
              :to="'/mate/' + (mate.username ? mate.username : mate.uid)"
              class="dash-row"
            >
              <profile-pic :profile_pic="mate.profile_pic" :avatar="mate.avatar" :photo="mate.photo" class="dash-row-avatar" />
              <div class="dash-row-body">
                <div class="dash-row-name">{{ mate.displayName }}</div>
                <div class="dash-row-meta">Joined {{ mate.joined_at }}</div>
              </div>
              <span class="dash-status dash-status-accepted">Active</span>
            </router-link>
          </div>
        </div>

        <div v-else>
          <div v-if="pastMembers.length === 0" class="dash-empty">No past members yet.</div>
          <div v-else class="dash-list dash-members-grid">
            <component
              :is="mate.account_deleted ? 'div' : 'router-link'"
              v-for="mate in pastMembers"
              :key="mate.id"
              :to="mate.account_deleted ? undefined : '/mate/' + (mate.username ? mate.username : mate.uid)"
              class="dash-row"
            >
              <img
                v-if="mate.account_deleted"
                src="/img/coliving-app-logo-symbol.png"
                alt=""
                class="dash-row-avatar dash-row-avatar-logo"
              >
              <profile-pic
                v-else
                :profile_pic="mate.profile_pic"
                :avatar="mate.avatar"
                :photo="mate.photo"
                class="dash-row-avatar"
              />
              <div class="dash-row-body">
                <div class="dash-row-name">{{ mate.displayName }}</div>
                <div class="dash-row-meta">
                  <span v-if="mate.joined_at">Joined {{ mate.joined_at }}</span>
                  <span v-if="mate.joined_at && mate.left_at"> &middot; </span>
                  <span v-if="mate.left_at">Left {{ mate.left_at }}</span>
                </div>
              </div>
              <span class="dash-status" :class="mate.account_deleted ? 'dash-status-left' : 'dash-status-declined'">
                {{ mate.account_deleted ? 'Account Deleted' : 'Left Space' }}
              </span>
            </component>
          </div>
        </div>
      </div>

      <!-- JOIN REQUESTS TAB -->
      <div v-if="activeTab === 'requests'" class="dash-panel">
        <div class="dash-subtabs">
          <button type="button" class="dash-chip" :class="{ 'dash-chip-active': requestsFilter === 'pending' }" @click="requestsFilter = 'pending'">Pending ({{ requestCounts.pending }})</button>
          <button type="button" class="dash-chip" :class="{ 'dash-chip-active': requestsFilter === 'accepted' }" @click="requestsFilter = 'accepted'">Accepted ({{ requestCounts.accepted }})</button>
          <button type="button" class="dash-chip" :class="{ 'dash-chip-active': requestsFilter === 'declined' }" @click="requestsFilter = 'declined'">Declined ({{ requestCounts.declined }})</button>
          <button type="button" class="dash-chip" :class="{ 'dash-chip-active': requestsFilter === 'all' }" @click="requestsFilter = 'all'">All ({{ requestCounts.all }})</button>
        </div>

        <div v-if="isLoadingJr" class="dash-empty">Loading join requests...</div>
        <div v-else-if="filteredRequests.length === 0" class="dash-empty">No join requests here.</div>

        <div v-else class="dash-list">
          <div v-for="request in filteredRequests" :key="request.id" class="dash-row dash-row-request">
            <profile-pic :profile_pic="request.mate_profile_pic" :avatar="request.mate_avatar" :photo="request.mate_photo" class="dash-row-avatar" />
            <div class="dash-row-body">
              <div class="dash-row-name">
                <router-link :to="'/mate/' + request.mate_uid">{{ request.mate_name || request.mate_username || request.mate_uid }}</router-link>
              </div>
              <div class="dash-row-meta">Requested {{ request.created_at }}</div>
              <div v-if="jrActionError[request.id]" class="dash-row-error">{{ jrActionError[request.id] }}</div>
            </div>
            <span class="dash-status" :class="'dash-status-' + request.status">{{ request.status }}</span>
            <div class="dash-row-actions">
              <template v-if="request.status === 'pending'">
                <button type="button" class="btn btn-sm btn-join" :disabled="jrActionLoading[request.id]" @click="acceptRequest(request)">Accept</button>
                <button type="button" class="btn btn-sm btn-danger" :disabled="jrActionLoading[request.id]" @click="declineRequest(request)">Decline</button>
              </template>
              <router-link :to="'/inbox/space/' + request.space_uid + '/mate/' + request.mate_uid">
                <button type="button" class="btn btn-sm btn-quiet">View chat</button>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- FEEDBACK TAB -->
      <div v-if="activeTab === 'feedback'" class="dash-panel">
        <div class="dash-feedback">
          <div class="dash-feedback-title">Your Feedback</div>
          <div class="dash-feedback-text">
            As a host, <span class="dash-feedback-link" @click="modalFeedback = true">let us know</span> what additional data or tools you would like to see in this space dashboard.
          </div>
        </div>
      </div>

      <div style="height:40px;"></div>
    </div>

    <div v-else class="dash-empty">Space not found.</div>

    <div v-if="modalFeedback">
      <modal-feedback @close="modalFeedback = false" />
    </div>
  </div>
</template>

<style>
.dash {
  margin-bottom: 60px;
}

.dash-header {
  display: grid;
  grid-template-columns: 84px 1fr auto;
  gap: 18px;
  align-items: center;
  margin-bottom: 24px;
}

.dash-header-photo {
  width: 84px;
  height: 84px;
  border-radius: 14px;
  overflow: hidden;
  background: #f2f2f2;
  flex-shrink: 0;
}

.dash-header-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.dash-eyebrow {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: #00abff;
  margin-bottom: 2px;
}

.dash-title {
  margin: 0;
  padding-top: 0;
  font-size: 26px;
  line-height: 1.2;
}

.dash-subtitle {
  margin-top: 4px;
  color: rgb(110, 110, 110);
  font-size: 14px;
}

.dash-header-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-self: end;
}

.dash-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
  margin-bottom: 30px;
}

.dash-stat-card {
  background: #fff;
  border: 1px solid #e6e6e6;
  border-radius: 12px;
  padding: 16px 18px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
}

.dash-stat-highlight {
  border-color: #00abff;
  background: aliceblue;
}

.dash-stat-value {
  font-size: 28px;
  font-weight: 700;
  line-height: 1.1;
}

.dash-stat-label {
  margin-top: 4px;
  font-size: 13px;
  color: rgb(110, 110, 110);
}

.dash-tabs {
  height: 40px;
  border-bottom: 1px solid #ccc;
  margin-bottom: 24px;
}

.dash-badge {
  display: inline-block;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  margin-left: 6px;
  border-radius: 999px;
  background: #ff5a5f;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  line-height: 18px;
  text-align: center;
  vertical-align: middle;
}

.dash-panel {
  min-height: 200px;
}

.dash-subtabs {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 18px;
}

.dash-chip {
  border: 1px solid #d8d8d8;
  background: #fff;
  border-radius: 999px;
  padding: 6px 14px;
  font-size: 13px;
  color: #444;
  cursor: pointer;
}

.dash-chip:hover {
  background: #f5f5f5;
  color: #444;
}

.dash-chip-active,
.dash-chip-active:hover {
  background: #00abff;
  border-color: #00abff;
  color: #fff;
}

.dash-empty {
  padding: 30px 10px;
  text-align: center;
  color: rgb(120, 120, 120);
  background: #fafafa;
  border: 1px dashed #ddd;
  border-radius: 10px;
}

.dash-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.dash-members-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.dash-row {
  display: grid;
  grid-template-columns: 40px 1fr auto;
  align-items: center;
  gap: 14px;
  padding: 12px 14px;
  background: #fff;
  border: 1px solid #ececec;
  border-radius: 10px;
  color: inherit;
  text-decoration: none;
  transition: box-shadow .12s ease, border-color .12s ease;
}

a.dash-row:hover {
  border-color: #cfe9ff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
  text-decoration: none;
  color: inherit;
}

.dash-row-request {
  grid-template-columns: 40px 1fr auto auto;
}

.dash-row-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.dash-row-avatar-logo {
  object-fit: contain;
  padding: 6px;
  background: #f7f7f7;
  opacity: .7;
}

.dash-row-name {
  font-size: 15px;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dash-row-body {
  min-width: 0;
}

.dash-row-name a {
  color: inherit;
}

.dash-row-meta {
  margin-top: 2px;
  font-size: 12.5px;
  color: rgb(130, 130, 130);
}

.dash-row-error {
  margin-top: 4px;
  font-size: 12.5px;
  color: #d0342c;
}

.dash-row-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-self: end;
}

.dash-status {
  justify-self: end;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .03em;
  padding: 4px 10px;
  border-radius: 999px;
  white-space: nowrap;
}

.dash-status-active,
.dash-status-accepted {
  background: #e4f8ea;
  color: #1e8a44;
}

.dash-status-pending {
  background: #fff2df;
  color: #b3720b;
}

.dash-status-declined,
.dash-status-left {
  background: #f3f3f3;
  color: #777;
}

.dash-status-expired {
  background: #fdeaea;
  color: #c23636;
}

.dash-btn-accept {
  background-color: #d8f5cf;
  border-color: #7fce6a;
  color: #1e6b1e;
}

.dash-btn-accept:hover {
  background-color: #c4f0b7;
}

.dash-btn-decline {
  background-color: #fbe4e4;
  border-color: #e19a9a;
  color: #8a2626;
}

.dash-btn-decline:hover {
  background-color: #f7cfcf;
}

.dash-feedback {
  padding: 20px;
  background: aliceblue;
  border: 1px solid #cfe9ff;
  border-radius: 10px;
}

.dash-feedback-title {
  font-weight: 700;
  margin-bottom: 8px;
  font-size: 16px;
}

.dash-feedback-text {
  font-size: 14px;
  line-height: 1.6;
  color: #444;
}

.dash-feedback-link {
  color: rgb(0, 134, 218);
  cursor: pointer;
  font-weight: 600;
}

.dash-feedback-link:hover {
  text-decoration: underline;
}

@media screen and (max-width: 900px) {
  .dash-members-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media screen and (max-width: 700px) {
  .dash-header {
    grid-template-columns: 64px 1fr;
  }

  .dash-header-photo {
    width: 64px;
    height: 64px;
  }

  .dash-header-actions {
    grid-column: 1 / -1;
    justify-self: start;
  }

  .dash-stats {
    grid-template-columns: 1fr 1fr;
  }

  .dash-members-grid {
    grid-template-columns: 1fr;
  }

  .dash-row,
  .dash-row-request {
    grid-template-columns: 34px 1fr;
    row-gap: 8px;
  }

  .dash-row-avatar {
    width: 34px;
    height: 34px;
  }

  .dash-status {
    grid-column: 2;
    justify-self: start;
  }

  .dash-row-actions {
    grid-column: 1 / -1;
    justify-self: start;
  }
}
</style>
