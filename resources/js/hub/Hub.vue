<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import ProfilePic from '../components/ProfilePic.vue';
import HubCalendar from './HubCalendar.vue';
import HubMemberCard from './HubMemberCard.vue';

const route = useRoute();
const router = useRouter();
const uid = computed(() => route.params.uid);

const userLoggedIn = ref(window.userLoggedIn);
const isMember = ref('loading');
const isLoadingHub = ref(true);
const isLoadingTopics = ref(true);
const isLoadingThread = ref(false);
const isLoadingMates = ref(true);
const isLoadingJr = ref(false);
const isLoadingEvents = ref(false);

const space = ref(null);
const isAdmin = ref(false);
const currentUserId = ref(null);
const stats = ref({ current_members: 0, left_members: 0, deleted_members: 0, past_members: 0, pending_join_requests: 0 });
const mates = ref([]);
const threads = ref([]);
const selectedThread = ref(null);
const posts = ref([]);
const events = ref([]);
const joinRequests = ref([]);

const membersFilter = ref('current');
const requestsFilter = ref('pending');
const showNewThread = ref(false);
const newThread = ref({ title: '', content: '' });
const newReply = ref('');
const spaceInfoDraft = ref('');
const spaceInfoEditor = ref(null);
const actionError = ref('');
const actionLoading = ref(false);
const adminCandidateId = ref('');
const adminSearchQuery = ref('');
const adminSearchOpen = ref(false);
const jrActionLoading = ref({});
const jrActionError = ref({});

const activeSection = computed(() => {
  if (route.name === 'HubMates') return 'members';
  if (route.name === 'HubRequests') return 'requests';
  if (route.name === 'HubCalendar') return 'calendar';
  if (route.name === 'HubSettings') return 'settings';
  return 'topics';
});

const currentMembers = computed(() => mates.value.filter(mate => mate.status !== 'left'));
const pastMembers = computed(() => mates.value.filter(mate => mate.status === 'left'));
const visibleMembers = computed(() => {
  if (membersFilter.value === 'past') return pastMembers.value;
  return currentMembers.value;
});

const filteredRequests = computed(() => {
  if (requestsFilter.value === 'all') return joinRequests.value;
  return joinRequests.value.filter(request => request.status === requestsFilter.value);
});

const requestCounts = computed(() => ({
  pending: joinRequests.value.filter(request => request.status === 'pending').length,
  accepted: joinRequests.value.filter(request => request.status === 'accepted').length,
  declined: joinRequests.value.filter(request => request.status === 'declined').length,
  all: joinRequests.value.length,
}));

const stickyThreads = computed(() => threads.value.filter(thread => thread.is_sticky));
const topicThreads = computed(() => threads.value.filter(thread => !thread.is_sticky));
const spaceInfoThread = computed(() => threads.value.find(thread => thread.type === 'space_info') || null);
const currentAdmins = computed(() => currentMembers.value.filter(mate => mate.role === 'admin'));
const makeAdminCandidates = computed(() => currentMembers.value.filter(mate => (
  mate.role !== 'admin' && !mate.account_deleted
)));
const adminPredictions = computed(() => {
  const query = adminSearchQuery.value.trim().toLowerCase();
  if (query.length < 1) return [];

  return makeAdminCandidates.value.filter((mate) => {
    const name = String(mate.name || '').trim().toLowerCase();
    const username = String(mate.username || '').trim().toLowerCase();
    if (name.startsWith(query) || username.startsWith(query)) return true;
    return name.split(/\s+/).some(part => part.startsWith(query));
  }).slice(0, 8);
});
const selectedAdminCandidate = computed(() => (
  makeAdminCandidates.value.find(item => Number(item.user_id) === Number(adminCandidateId.value)) || null
));
const canLeaveAsAdmin = computed(() => currentAdmins.value.length > 1);
const isListingOwner = computed(() => (
  space.value?.user_id != null
  && currentUserId.value != null
  && Number(space.value.user_id) === Number(currentUserId.value)
));
const postsWithAuthors = computed(() => posts.value.map(post => {
  const author = mateByUid(post.author_uid);
  return {
    ...post,
    author,
    authorName: mateDisplayName(author),
    authorTo: mateProfileTo(author),
  };
}));

function resizeSpaceInfoEditor() {
  const editor = spaceInfoEditor.value;
  if (!editor) return;
  editor.style.height = 'auto';
  editor.style.height = `${Math.max(120, editor.scrollHeight)}px`;
}

function spaceInfoTopicTo() {
  if (spaceInfoThread.value?.id) {
    return `/space/${uid.value}/hub/topics/${spaceInfoThread.value.id}`;
  }
  return `/space/${uid.value}/hub`;
}

function isEmptySpaceInfoFirstPost(post) {
  if (selectedThread.value?.type !== 'space_info') return false;
  const first = postsWithAuthors.value[0];
  if (!first || first.id !== post.id) return false;
  return !String(post.content || '').trim();
}

function hubLink(section) {
  const base = `/space/${uid.value}/hub`;
  if (section === 'topics') return base;
  if (section === 'members') return `${base}/mates`;
  return `${base}/${section}`;
}

function mateDisplayName(mate) {
  if (!mate) return 'Member';
  if (mate.account_deleted) return 'Deleted account';
  return mate.name || mate.username || mate.uid || 'Member';
}

function mateByUid(authorUid) {
  return mates.value.find(item => item.uid === authorUid) || null;
}

function mateProfileTo(mate) {
  if (!mate || mate.account_deleted) return null;
  return '/mate/' + (mate.username || mate.uid);
}

function formatDate(value) {
  if (!value) return '';
  const date = new Date(value);
  return date.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

async function fetchHub() {
  isLoadingHub.value = true;
  try {
    const response = await axios.get(`/api/space/${uid.value}/hub`);
    if (response.data === 'not_a_member') {
      isMember.value = false;
      return;
    }

    space.value = response.data.space;
    isAdmin.value = !!response.data.is_admin;
    currentUserId.value = response.data.current_user_id ?? null;
    stats.value = response.data.stats || stats.value;
    spaceInfoDraft.value = response.data.space?.info || '';
    isMember.value = true;
  } catch (error) {
    console.error(error);
    isMember.value = false;
  } finally {
    isLoadingHub.value = false;
  }
}

async function fetchMembers() {
  isLoadingMates.value = true;
  try {
    const response = await axios.get(`/api/space/${uid.value}/space-data`);
    if (response.data === 'unauthenticated' || response.data === 'not_a_member') {
      mates.value = [];
      return;
    }

    mates.value = (response.data[0] || []).map(mate => ({
      ...mate,
      account_deleted: !!mate.account_deleted,
      displayName: mateDisplayName({ ...mate, account_deleted: !!mate.account_deleted }),
    }));
  } catch (error) {
    console.error(error);
  } finally {
    isLoadingMates.value = false;
  }
}

async function fetchThreads() {
  isLoadingTopics.value = true;
  try {
    const response = await axios.get(`/api/space/${uid.value}/hub/topics`);
    threads.value = response.data.threads || [];
  } catch (error) {
    console.error(error);
  } finally {
    isLoadingTopics.value = false;
  }
}

async function fetchThread(threadId) {
  if (!threadId) {
    selectedThread.value = null;
    posts.value = [];
    return;
  }

  isLoadingThread.value = true;
  try {
    const response = await axios.get(`/api/space/${uid.value}/hub/topics/${threadId}`);
    selectedThread.value = response.data.thread;
    posts.value = response.data.posts || [];
  } catch (error) {
    console.error(error);
    selectedThread.value = null;
    posts.value = [];
  } finally {
    isLoadingThread.value = false;
  }
}

async function createThread() {
  if (!newThread.value.title.trim() || !newThread.value.content.trim()) {
    actionError.value = 'Add a title and first post.';
    return;
  }

  actionLoading.value = true;
  actionError.value = '';
  try {
    const response = await axios.post(`/api/space/${uid.value}/hub/topics`, newThread.value);
    newThread.value = { title: '', content: '' };
    showNewThread.value = false;
    await fetchThreads();
    router.push(`/space/${uid.value}/hub/topics/${response.data.thread.id}`);
  } catch (error) {
    console.error(error);
    actionError.value = 'Could not create the topic.';
  } finally {
    actionLoading.value = false;
  }
}

async function deleteThread() {
  if (!selectedThread.value?.can_delete) return;
  if (!confirm('Delete this topic and all of its replies?')) return;

  actionLoading.value = true;
  actionError.value = '';
  try {
    await axios.delete(`/api/space/${uid.value}/hub/topics/${selectedThread.value.id}`);
    selectedThread.value = null;
    posts.value = [];
    await fetchThreads();
    router.push(`/space/${uid.value}/hub`);
  } catch (error) {
    console.error(error);
    actionError.value = error.response?.data?.message || 'Could not delete the topic.';
  } finally {
    actionLoading.value = false;
  }
}

async function createReply() {
  if (!newReply.value.trim() || !selectedThread.value) return;

  actionLoading.value = true;
  actionError.value = '';
  try {
    await axios.post(`/api/space/${uid.value}/hub/topics/${selectedThread.value.id}/posts`, { content: newReply.value });
    newReply.value = '';
    await fetchThread(selectedThread.value.id);
    await fetchThreads();
  } catch (error) {
    console.error(error);
    actionError.value = 'Could not post the reply.';
  } finally {
    actionLoading.value = false;
  }
}

async function fetchJoinRequests() {
  if (!isAdmin.value) return;

  isLoadingJr.value = true;
  try {
    const response = await axios.get(`/api/space/${uid.value}/jr`);
    joinRequests.value = Array.isArray(response.data) ? response.data.reverse() : [];
  } catch (error) {
    console.error(error);
  } finally {
    isLoadingJr.value = false;
  }
}

async function acceptRequest(request) {
  if (jrActionLoading.value[request.id]) return;

  jrActionLoading.value[request.id] = true;
  jrActionError.value[request.id] = '';
  try {
    const response = await axios.post(`/api/join_requests/${request.id}/accept`);
    if (response.data.message === 'success' || response.data === 'success') {
      request.status = 'accepted';
      await fetchMembers();
      await fetchHub();
    } else {
      jrActionError.value[request.id] = response.data.message || 'Something went wrong.';
    }
  } catch (error) {
    console.error(error);
    jrActionError.value[request.id] = 'Something went wrong.';
  } finally {
    jrActionLoading.value[request.id] = false;
  }
}

async function declineRequest(request) {
  if (jrActionLoading.value[request.id] || !confirm('Decline this join request?')) return;

  jrActionLoading.value[request.id] = true;
  jrActionError.value[request.id] = '';
  try {
    const response = await axios.post(`/api/join_requests/${request.id}/${request.mate_uid}/decline`);
    if (response.data.message === 'success' || response.data === 'success') {
      request.status = 'declined';
      await fetchHub();
    } else {
      jrActionError.value[request.id] = response.data.message || 'Something went wrong.';
    }
  } catch (error) {
    console.error(error);
    jrActionError.value[request.id] = 'Something went wrong.';
  } finally {
    jrActionLoading.value[request.id] = false;
  }
}

async function fetchEvents() {
  isLoadingEvents.value = true;
  try {
    const response = await axios.get(`/api/space/${uid.value}/hub/events`);
    events.value = response.data.events || [];
  } catch (error) {
    console.error(error);
  } finally {
    isLoadingEvents.value = false;
  }
}

async function saveSpaceInfo() {
  actionLoading.value = true;
  actionError.value = '';
  try {
    const response = await axios.put(`/api/space/${uid.value}/hub/space-info`, { info: spaceInfoDraft.value });
    space.value = response.data.space;
    await fetchThreads();
    if (selectedThread.value?.type === 'space_info') {
      await fetchThread(selectedThread.value.id);
    }
  } catch (error) {
    console.error(error);
    actionError.value = 'Could not save Space Info.';
  } finally {
    actionLoading.value = false;
  }
}

async function leaveSpace() {
  if (!confirm('Are you sure you want to leave this space?')) return;

  actionLoading.value = true;
  actionError.value = '';
  try {
    await axios.post(`/api/space/${uid.value}/leave`);
    router.push('/hub');
  } catch (error) {
    console.error(error);
    actionError.value = error.response?.data?.message || 'Could not leave this space.';
  } finally {
    actionLoading.value = false;
  }
}

function pickAdminCandidate(mate) {
  adminCandidateId.value = mate.user_id;
  adminSearchQuery.value = mateDisplayName(mate);
  adminSearchOpen.value = false;
  actionError.value = '';
}

function onAdminSearchInput() {
  adminCandidateId.value = '';
  adminSearchOpen.value = adminSearchQuery.value.trim().length > 0;
}

function onAdminSearchBlur() {
  setTimeout(() => {
    adminSearchOpen.value = false;
  }, 150);
}

async function makeAdmin() {
  const mate = selectedAdminCandidate.value;
  if (!mate?.user_id) {
    actionError.value = 'Search and pick a member to make admin.';
    return;
  }
  if (!confirm(`Make ${mateDisplayName(mate)} an admin of this space?`)) return;

  actionLoading.value = true;
  actionError.value = '';
  try {
    await axios.post(`/api/space/${uid.value}/hub/admins`, { user_id: mate.user_id });
    adminCandidateId.value = '';
    adminSearchQuery.value = '';
    adminSearchOpen.value = false;
    await fetchMembers();
  } catch (error) {
    console.error(error);
    actionError.value = error.response?.data?.message || 'Could not make this member an admin.';
  } finally {
    actionLoading.value = false;
  }
}

async function leaveAsAdmin() {
  if (!canLeaveAsAdmin.value) {
    actionError.value = 'Make another member an admin before you leave.';
    return;
  }

  if (!confirm('This will remove you as admin and also leave the space. Continue?')) return;

  actionLoading.value = true;
  actionError.value = '';
  try {
    await axios.post(`/api/space/${uid.value}/hub/leave-admin`);
    router.push('/hub');
  } catch (error) {
    console.error(error);
    actionError.value = error.response?.data?.message || 'Could not leave as admin.';
  } finally {
    actionLoading.value = false;
  }
}

async function loadHub() {
  if (!userLoggedIn.value) return;

  await fetchHub();
  if (isMember.value !== true) return;

  await Promise.all([fetchMembers(), fetchThreads(), fetchEvents()]);
  await fetchJoinRequests();
  await fetchThread(route.params.threadId);
}

watch(() => route.params.threadId, threadId => {
  fetchThread(threadId);
});

watch(activeSection, section => {
  if (section !== 'topics') showNewThread.value = false;
  if (section === 'requests') fetchJoinRequests();
  if (section === 'calendar') fetchEvents();
  if (section === 'settings') nextTick(resizeSpaceInfoEditor);
});

watch(spaceInfoDraft, () => nextTick(resizeSpaceInfoEditor));

onMounted(loadHub);
</script>

<template>
  <div v-if="isMember === 'loading' || isLoadingHub" class="spinner-container">
    <div class="spinner"></div>
  </div>

  <div v-else-if="userLoggedIn && isMember === true" class="content hub-shell">
    <div v-if="space" class="hub-header">
      <router-link :to="'/space/' + uid" class="hub-header-photo">
        <img
          :src="space.first_photo ? '/storage/spaces/' + space.first_photo : '/img/coliving-app-logo-symbol.png'"
          @error="$event.target.src = '/img/coliving-app-logo-symbol.png'"
          :alt="space.name"
        >
      </router-link>
      <div class="hub-header-info">
        <div class="hub-eyebrow">Coliving Hub</div>
        <h1 class="hub-title">{{ space.name }}</h1>
        <div v-if="space.address || space.country" class="hub-subtitle">{{ space.address || space.country }}</div>
      </div>
    </div>

    <div class="hub-tabs">
      <div class="hub-tabs-track">
        <div class="page-menu-button-outer">
          <router-link :to="hubLink('topics')">
            <div class="page-menu-button">
              <span class="inbox-type-link" :class="{ active: activeSection === 'topics' }">Topics</span>
            </div>
          </router-link>
          <div class="page-menu-underline" :class="{ active: activeSection === 'topics' }"></div>
        </div>

        <div class="page-menu-button-outer">
          <router-link :to="hubLink('members')">
            <div class="page-menu-button">
              <span class="inbox-type-link" :class="{ active: activeSection === 'members' }">Members</span>
            </div>
          </router-link>
          <div class="page-menu-underline" :class="{ active: activeSection === 'members' }"></div>
        </div>

        <div v-if="isAdmin" class="page-menu-button-outer">
          <router-link :to="hubLink('requests')">
            <div class="page-menu-button">
              <span class="inbox-type-link" :class="{ active: activeSection === 'requests' }">
                <span class="hub-tab-full">Join Requests</span>
                <span class="hub-tab-short">Requests</span>
                <span v-if="stats.pending_join_requests > 0" class="hub-badge">{{ stats.pending_join_requests }}</span>
              </span>
            </div>
          </router-link>
          <div class="page-menu-underline" :class="{ active: activeSection === 'requests' }"></div>
        </div>

        <div class="page-menu-button-outer">
          <router-link :to="hubLink('calendar')">
            <div class="page-menu-button">
              <span class="inbox-type-link" :class="{ active: activeSection === 'calendar' }">Calendar</span>
            </div>
          </router-link>
          <div class="page-menu-underline" :class="{ active: activeSection === 'calendar' }"></div>
        </div>

        <div class="page-menu-button-outer">
          <router-link :to="hubLink('settings')">
            <div class="page-menu-button">
              <span class="inbox-type-link" :class="{ active: activeSection === 'settings' }">Settings</span>
            </div>
          </router-link>
          <div class="page-menu-underline" :class="{ active: activeSection === 'settings' }"></div>
        </div>
      </div>
    </div>

    <main class="hub-main">
        <section v-if="activeSection === 'topics'">
          <div v-if="route.name === 'HubThread'" class="hub-panel">
            <div v-if="isLoadingThread" class="hub-empty">Loading thread...</div>
            <template v-else-if="selectedThread">
              <div class="hub-thread-header">
                <h2 class="hub-section-title">{{ selectedThread.title }}</h2>
                <div v-if="selectedThread.can_delete" class="hub-row-actions">
                  <span v-if="actionError" class="hub-error">{{ actionError }}</span>
                  <button
                    type="button"
                    class="button-function"
                    :disabled="actionLoading"
                    @click="deleteThread"
                  >{{ actionLoading ? 'Deleting...' : 'Delete topic' }}</button>
                </div>
              </div>

              <div class="hub-post-list">
                <article v-for="post in postsWithAuthors" :key="post.id" class="hub-post">
                  <div class="hub-post-meta">
                    <div class="hub-post-author">
                      <component
                        :is="post.authorTo ? 'router-link' : 'span'"
                        :to="post.authorTo"
                        class="hub-post-author-identity"
                      >
                        <img
                          v-if="!post.author || post.author.account_deleted"
                          src="/img/coliving-app-logo-symbol.png"
                          alt=""
                          class="hub-post-avatar hub-post-avatar-placeholder"
                        >
                        <profile-pic
                          v-else
                          :profile_pic="post.author.profile_pic"
                          :avatar="post.author.avatar"
                          :photo="post.author.photo"
                          class="hub-post-avatar"
                        />
                        <span>{{ post.authorName }}</span>
                      </component>
                    </div>
                    <span>{{ formatDate(post.created_at) }}</span>
                  </div>
                  <div class="hub-post-content">
                    <span v-if="String(post.content || '').trim()" style="white-space: pre-wrap;">{{ post.content }}</span>
                    <i v-else-if="isAdmin && isEmptySpaceInfoFirstPost(post)">
                      Add Space Info in
                      <router-link :to="hubLink('settings')">Settings</router-link>.
                    </i>
                    <i v-else>No information available yet.</i>
                  </div>
                </article>
              </div>

              <div class="hub-reply-box">
                <label for="reply">Reply</label>
                <textarea id="reply" v-model="newReply" class="textarea" placeholder="Write a reply..." style="height:130px;"></textarea>
                <div class="hub-form-actions">
                  <span v-if="actionError" class="hub-error">{{ actionError }}</span>
                  <button type="button" class="button" :disabled="actionLoading" @click="createReply">{{ actionLoading ? 'Posting...' : 'Post reply' }}</button>
                </div>
              </div>
            </template>
            <div v-else class="hub-empty">Thread not found.</div>
          </div>

          <div v-else class="hub-panel">
            <div v-if="isLoadingTopics" class="hub-empty">Loading topics...</div>
            <template v-else>
              <button
                type="button"
                class="button hub-new-topic"
                @click="showNewThread = !showNewThread"
              >{{ showNewThread ? 'Cancel' : 'New Topic' }}</button>

              <div v-if="showNewThread" class="hub-form-card">
                <label for="thread-title">Title</label>
                <input id="thread-title" v-model="newThread.title" type="text" class="input" placeholder="Topic title">
                <label for="thread-content">First post</label>
                <textarea id="thread-content" v-model="newThread.content" class="textarea" placeholder="Start the discussion..." style="min-height:140px;"></textarea>
                <div class="hub-form-actions">
                  <span v-if="actionError" class="hub-error">{{ actionError }}</span>
                  <button type="button" class="button" :disabled="actionLoading" @click="createThread">{{ actionLoading ? 'Creating...' : 'Create topic' }}</button>
                </div>
              </div>

              <div v-if="threads.length === 0" class="hub-empty">No topics yet.</div>
              <div v-else class="hub-topic-list">
                <router-link
                  v-for="thread in stickyThreads"
                  :key="thread.id"
                  :to="'/space/' + uid + '/hub/topics/' + thread.id"
                  class="hub-topic-row hub-topic-row--sticky"
                >
                  <div class="hub-topic-main">
                    <div class="hub-topic-title">
                      <span class="hub-sticky-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M12 17v5"/>
                          <path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H8a2 2 0 0 0 0 4 1 1 0 0 1 1 1z"/>
                        </svg>
                      </span>
                      {{ thread.title }}
                    </div>
                    <div class="hub-topic-meta">Last activity {{ formatDate(thread.last_post_at || thread.updated_at) }}</div>
                  </div>
                  <div class="hub-topic-count">
                    <b>{{ thread.replies_count }}</b>
                    <span>replies</span>
                  </div>
                </router-link>
                <router-link
                  v-for="thread in topicThreads"
                  :key="thread.id"
                  :to="'/space/' + uid + '/hub/topics/' + thread.id"
                  class="hub-topic-row"
                >
                  <div class="hub-topic-main">
                    <div class="hub-topic-title">{{ thread.title }}</div>
                    <div class="hub-topic-meta">Last activity {{ formatDate(thread.last_post_at || thread.updated_at) }}</div>
                  </div>
                  <div class="hub-topic-count">
                    <b>{{ thread.replies_count }}</b>
                    <span>replies</span>
                  </div>
                </router-link>
              </div>
            </template>
          </div>
        </section>

        <section v-if="activeSection === 'members'" class="hub-panel">
          <div class="hub-chips">
            <button type="button" class="hub-chip" :class="{ active: membersFilter === 'current' }" @click="membersFilter = 'current'">Current ({{ currentMembers.length }})</button>
            <button type="button" class="hub-chip" :class="{ active: membersFilter === 'past' }" @click="membersFilter = 'past'">Past ({{ pastMembers.length }})</button>
          </div>

          <div v-if="isLoadingMates" class="hub-empty">Loading members...</div>
          <div v-else-if="membersFilter === 'current' && currentMembers.length === 0" class="hub-empty">No current members yet.</div>
          <div v-else-if="membersFilter === 'past' && pastMembers.length === 0" class="hub-empty">No past members yet.</div>
          <div v-else class="hub-member-grid">
            <HubMemberCard
              v-for="mate in visibleMembers"
              :key="mate.id"
              :mate="mate"
            />
          </div>
        </section>

        <section v-if="activeSection === 'requests'" class="hub-panel">
          <template v-if="isAdmin">
            <div class="hub-chips">
              <button type="button" class="hub-chip" :class="{ active: requestsFilter === 'pending' }" @click="requestsFilter = 'pending'">Pending ({{ requestCounts.pending }})</button>
              <button type="button" class="hub-chip" :class="{ active: requestsFilter === 'accepted' }" @click="requestsFilter = 'accepted'">Accepted ({{ requestCounts.accepted }})</button>
              <button type="button" class="hub-chip" :class="{ active: requestsFilter === 'declined' }" @click="requestsFilter = 'declined'">Declined ({{ requestCounts.declined }})</button>
              <button type="button" class="hub-chip" :class="{ active: requestsFilter === 'all' }" @click="requestsFilter = 'all'">All ({{ requestCounts.all }})</button>
            </div>

            <div v-if="isLoadingJr" class="hub-empty">Loading join requests...</div>
            <div v-else-if="filteredRequests.length === 0" class="hub-empty">No join requests here.</div>
            <div v-else class="hub-request-list">
              <div v-for="request in filteredRequests" :key="request.id" class="hub-request-row">
                <profile-pic :profile_pic="request.mate_profile_pic" :avatar="request.mate_avatar" :photo="request.mate_photo" class="hub-avatar" />
                <div class="hub-request-body">
                  <div class="hub-member-name">
                    <router-link :to="'/mate/' + request.mate_uid">{{ request.mate_name || request.mate_username || request.mate_uid }}</router-link>
                  </div>
                  <div class="hub-member-meta">Requested {{ formatDate(request.created_at) }}</div>
                  <div v-if="jrActionError[request.id]" class="hub-error">{{ jrActionError[request.id] }}</div>
                </div>
                <div class="hub-request-aside">
                  <span class="hub-status" :class="'status-' + request.status">{{ request.status }}</span>
                  <div class="hub-row-actions">
                    <template v-if="request.status === 'pending'">
                      <button type="button" class="button-function" :disabled="jrActionLoading[request.id]" @click="acceptRequest(request)">Accept</button>
                      <button type="button" class="button-function" :disabled="jrActionLoading[request.id]" @click="declineRequest(request)">Decline</button>
                    </template>
                    <router-link :to="'/inbox/space/' + request.space_uid + '/mate/' + request.mate_uid">
                      <button type="button" class="button-function">View chat</button>
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </template>
          <div v-else class="hub-empty">Only Admins can view join requests.</div>
        </section>

        <section v-if="activeSection === 'calendar'" class="hub-panel">
          <HubCalendar
            :uid="uid"
            :is-admin="isAdmin"
            :events="events"
            :loading="isLoadingEvents"
            @refresh="fetchEvents"
          />
        </section>

        <section v-if="activeSection === 'settings'" class="hub-panel">
          <div class="hub-settings-block">
            <h3 class="hub-block-title">Space Info</h3>
            <template v-if="isAdmin">
              <p>This content is shown as the first post in the sticky Space Info topic. Members can reply to it like any other thread.</p>
              <textarea
                ref="spaceInfoEditor"
                v-model="spaceInfoDraft"
                class="textarea hub-space-info-editor"
                placeholder="Space info"
                @input="resizeSpaceInfoEditor"
              ></textarea>
              <div class="hub-form-actions">
                <span v-if="actionError" class="hub-error">{{ actionError }}</span>
                <button type="button" class="button" :disabled="actionLoading" @click="saveSpaceInfo">{{ actionLoading ? 'Saving...' : 'Save Space Info' }}</button>
              </div>
            </template>
            <template v-else>
              <p>
                You can find all the important info in the
                <router-link :to="spaceInfoTopicTo()">Space Info topic</router-link>.
              </p>
              <p>If you want, you can also contact the space directly by sending a message.</p>
              <router-link :to="'/inbox/space/' + uid">
                <button type="button" class="button-function">Message space</button>
              </router-link>
            </template>
          </div>

          <div class="hub-settings-block">
            <h3 class="hub-block-title">Admins</h3>
            <div v-if="currentAdmins.length" class="hub-admin-list">
              <component
                v-for="admin in currentAdmins"
                :key="admin.id"
                :is="mateProfileTo(admin) ? 'router-link' : 'span'"
                :to="mateProfileTo(admin)"
                class="hub-admin-person"
              >
                <img
                  v-if="admin.account_deleted"
                  src="/img/coliving-app-logo-symbol.png"
                  alt=""
                  class="hub-post-avatar hub-post-avatar-placeholder"
                >
                <profile-pic
                  v-else
                  :profile_pic="admin.profile_pic"
                  :avatar="admin.avatar"
                  :photo="admin.photo"
                  class="hub-post-avatar"
                />
                <span>{{ mateDisplayName(admin) }}</span>
              </component>
            </div>
            <p v-else-if="isLoadingMates" class="hub-empty">Loading admins...</p>
            <template v-if="isAdmin">
              <div v-if="makeAdminCandidates.length" class="hub-admin-picker">
                <div class="hub-admin-search">
                  <input
                    v-model="adminSearchQuery"
                    type="text"
                    class="input"
                    placeholder="Search member by name or username"
                    autocomplete="off"
                    @input="onAdminSearchInput"
                    @focus="adminSearchOpen = adminSearchQuery.trim().length > 0"
                    @blur="onAdminSearchBlur"
                  >
                  <div v-if="adminSearchOpen && adminSearchQuery.trim()" class="hub-admin-predictions">
                    <button
                      v-for="mate in adminPredictions"
                      :key="mate.user_id"
                      type="button"
                      class="hub-admin-prediction"
                      @mousedown.prevent="pickAdminCandidate(mate)"
                    >
                      <profile-pic
                        :profile_pic="mate.profile_pic"
                        :avatar="mate.avatar"
                        :photo="mate.photo"
                        class="hub-post-avatar"
                      />
                      <span>{{ mateDisplayName(mate) }}</span>
                      <span v-if="mate.username && mate.username !== mateDisplayName(mate)" class="hub-admin-prediction-username">@{{ mate.username }}</span>
                    </button>
                    <div v-if="adminPredictions.length === 0" class="hub-admin-prediction-empty">No matching members</div>
                  </div>
                </div>
                <button type="button" class="button-function" :disabled="actionLoading || !adminCandidateId" @click="makeAdmin">Make admin</button>
              </div>
              <p v-if="!canLeaveAsAdmin">Make another member an admin before you leave. Leaving as admin also leaves the space.</p>
              <p v-else>Leaving as admin also leaves this space.</p>
              <div v-if="actionError" class="hub-error hub-admin-error">{{ actionError }}</div>
              <button type="button" class="button-function" :disabled="actionLoading || !canLeaveAsAdmin" @click="leaveAsAdmin">Leave as admin</button>
            </template>
          </div>

          <div v-if="isAdmin && isListingOwner" class="hub-settings-block">
            <router-link :to="'/space/' + uid + '/edit'"><button type="button" class="button-function">Edit public space profile</button></router-link>
          </div>

          <div v-if="!isAdmin" class="hub-settings-block">
            <h3 class="hub-block-title">Membership</h3>
            <span v-if="actionError" class="hub-error">{{ actionError }}</span>
            <button class="button-function" :disabled="actionLoading" @click="leaveSpace">Leave this space</button>
          </div>
        </section>
    </main>
  </div>

  <div v-else-if="isMember === false" class="content hub-empty">
    You are not a member of this space.
    <br><br>
    <router-link :to="'/inbox/space/' + uid"><button type="button" class="button">Join Request</button></router-link>
  </div>

  <div v-else class="content">
    <router-link to="/login">Log in</router-link>
  </div>
</template>

<style scoped>
.hub-shell {
  margin-bottom: 24px;
  min-width: 0;
  overflow-x: hidden;
}

.hub-shell a,
.hub-shell button,
.hub-shell input,
.hub-shell textarea {
  max-width: 100%;
}

.hub-header {
  display: grid;
  grid-template-columns: 84px 1fr;
  gap: 18px;
  align-items: center;
  margin-bottom: 24px;
}

.hub-header-photo {
  width: 84px;
  height: 84px;
  border-radius: 14px;
  overflow: hidden;
  background: #f2f2f2;
  display: block;
}

.hub-header-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.hub-header-info {
  min-width: 0;
}

.hub-eyebrow {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: #00abff;
  margin-bottom: 2px;
}

.hub-title {
  margin: 0;
  padding-top: 0;
  font-size: 26px;
  line-height: 1.2;
  overflow-wrap: anywhere;
}

.hub-subtitle,
.hub-section-subtitle,
.hub-member-meta,
.hub-topic-meta {
  color: rgb(110, 110, 110);
  font-size: 13px;
}

.hub-form-actions,
.hub-row-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
}

.hub-form-actions {
  justify-content: flex-start;
}

.hub-row-actions {
  justify-content: flex-end;
}

.hub-form-actions a,
.hub-row-actions a {
  max-width: 100%;
}

.hub-form-card,
.hub-post,
.hub-event-card,
.hub-settings-block {
  background: #fff;
  border: 1px solid #e6e6e6;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
}

.hub-tabs {
  height: 40px;
  border-bottom: 1px solid #ccc;
  margin-bottom: 24px;
  overflow: hidden;
}

.hub-tabs-track {
  height: 40px;
}

.hub-tabs .page-menu-button {
  border-radius: 5px;
  padding: 5px;
}

.hub-tabs .page-menu-button-outer {
  position: relative;
  float: left;
  height: 40px;
  border-radius: 5px;
  margin-left: 20px;
}

.hub-tabs .page-menu-button-outer:first-child {
  margin-left: 0;
}

.hub-tabs .page-menu-button:hover,
.hub-tabs .page-menu-button:active {
  cursor: pointer;
  background-color: rgb(232, 232, 232);
}

.hub-tabs .page-menu-underline {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  height: 2px;
  background-color: #393939;
  margin-top: 0;
  visibility: hidden;
}

.hub-tabs .page-menu-underline.active {
  visibility: visible;
}

.hub-tabs a {
  color: inherit;
  text-decoration: none;
  display: block;
}

.hub-tabs .inbox-type-link {
  cursor: pointer;
  font-size: 15px;
  white-space: nowrap;
}

.hub-tabs a:hover {
  color: inherit;
  text-decoration: none;
}

.hub-tab-short {
  display: none;
}

.hub-new-topic {
  display: block;
  margin: 0 0 24px;
}

.hub-main,
.hub-panel {
  min-width: 0;
  min-height: 0;
}

.hub-panel {
  width: 100%;
}

.hub-thread-header {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 16px;
}

.hub-section-title {
  margin: 0;
  padding-top: 0;
}

.hub-topic-list,
.hub-post-list,
.hub-request-list,
.hub-event-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.hub-topic-row {
  display: grid;
  grid-template-columns: 1fr 78px;
  gap: 15px;
  padding: 15px;
  border: 1px solid #ececec;
  border-radius: 12px;
  color: inherit;
  text-decoration: none;
  transition: border-color .12s ease, box-shadow .12s ease;
}

.hub-topic-row:hover {
  border-color: #cfe9ff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
  color: inherit;
  text-decoration: none;
}

.hub-topic-row--sticky {
  border-color: rgba(0, 171, 255, .22);
  background: linear-gradient(90deg, rgba(0, 171, 255, .07), rgba(0, 171, 255, .02) 42%, #fff);
  box-shadow: inset 3px 0 0 #00abff;
}

.hub-topic-row--sticky:hover {
  border-color: rgba(0, 171, 255, .4);
  box-shadow: inset 3px 0 0 #00abff, 0 2px 8px rgba(0, 0, 0, .06);
}

.hub-topic-main {
  min-width: 0;
}

.hub-topic-title {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-size: 17px;
  font-weight: 700;
  overflow-wrap: anywhere;
}

.hub-sticky-mark {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
  width: 26px;
  height: 26px;
  border-radius: 8px;
  background: rgba(0, 171, 255, .12);
  color: #0094dc;
}

.hub-sticky-mark svg {
  width: 14px;
  height: 14px;
}

.hub-topic-count {
  text-align: right;
  color: rgb(110, 110, 110);
  font-size: 12px;
}

.hub-topic-count b {
  display: block;
  color: #222;
  font-size: 20px;
}

.hub-post {
  padding: 16px;
}

.hub-post-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  color: rgb(110, 110, 110);
  font-size: 12px;
}

.hub-post-author-identity {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
  color: inherit;
  text-decoration: none;
}

.hub-post-author-identity span {
  color: #222;
  font-weight: 700;
  font-size: 14px;
}

a.hub-post-author-identity:hover span {
  text-decoration: underline;
}

.hub-post-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.hub-post-avatar :deep(div),
.hub-post-avatar :deep(img),
.hub-post-avatar :deep(svg) {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
}

.hub-post-avatar-placeholder {
  object-fit: contain;
  padding: 4px;
  box-sizing: border-box;
  background: #f7f7f7;
  opacity: .75;
}

.hub-post-content,
.hub-event-description {
  line-height: 1.6;
  overflow-wrap: anywhere;
}

.hub-form-card,
.hub-settings-block {
  margin-top: 0;
  padding: 16px;
}

.hub-form-card .input,
.hub-form-card .textarea,
.hub-settings-block .input,
.hub-settings-block .textarea,
.hub-reply-box .textarea {
  width: 100%;
}

.hub-settings-block h3,
.hub-block-title {
  margin: 0 0 6px;
  padding-top: 0;
}

.hub-settings-block p {
  margin: 0 0 12px;
}

.hub-admin-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 16px;
  margin: 0 0 14px;
}

.hub-admin-person {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
  color: inherit;
  font-weight: 600;
  text-decoration: none;
}

.hub-admin-person:hover {
  color: #0094dc;
}

.hub-admin-picker {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  gap: 8px;
  margin: 0 0 14px;
}

.hub-admin-search {
  position: relative;
  flex: 1 1 240px;
  max-width: 320px;
}

.hub-admin-predictions {
  position: absolute;
  z-index: 5;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #ccc;
  border-radius: 8px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
  overflow: hidden;
}

.hub-admin-prediction {
  display: flex;
  flex-wrap: nowrap;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 8px 10px;
  border: 0;
  background: none;
  text-align: left;
  cursor: pointer;
}

.hub-admin-prediction:hover {
  background: rgb(232, 232, 232);
}

.hub-admin-prediction-username {
  color: rgb(110, 110, 110);
  font-size: 13px;
}

.hub-admin-prediction-empty {
  padding: 8px 10px;
  color: rgb(110, 110, 110);
  font-size: 13px;
}

.hub-admin-error {
  display: block;
  margin-top: 10px;
}

.hub-space-info-editor {
  display: block;
  height: auto;
  min-height: 120px;
  overflow: hidden;
  margin-bottom: 10px;
}

.hub-reply-box {
  margin-top: 18px;
  padding: 16px;
}

.hub-form-card + .hub-empty,
.hub-form-card + .hub-topic-list,
.hub-form-card + .hub-event-list,
.hub-settings-block + .hub-settings-block {
  margin-top: 18px;
}

.hub-chips {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin: 0 0 18px;
}

.hub-chip {
  border: 1px solid #d8d8d8;
  background: #fff;
  border-radius: 999px;
  padding: 6px 14px;
  font-size: 13px;
  color: #444;
  cursor: pointer;
}

.hub-chip.active,
.hub-chip:hover {
  background: #00abff;
  border-color: #00abff;
  color: #fff;
}

.hub-member-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: 18px;
}

.hub-request-row {
  display: grid;
  grid-template-columns: 40px 1fr auto;
  gap: 12px;
  align-items: center;
  padding: 12px 14px;
  border: 1px solid #ececec;
  border-radius: 10px;
  color: inherit;
  text-decoration: none;
}

.hub-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  overflow: hidden;
}

.hub-member-name {
  font-size: 15px;
  font-weight: 700;
  overflow-wrap: anywhere;
}

.hub-request-body {
  min-width: 0;
}

.hub-request-aside {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
  justify-content: flex-end;
}

.hub-status {
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  padding: 4px 10px;
}

.status-accepted {
  background: #e4f8ea;
  color: #1e8a44;
}

.status-pending {
  background: #fff2df;
  color: #b3720b;
}

.status-declined,
.status-left {
  background: #f3f3f3;
  color: #777;
}

.hub-badge {
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

.hub-event-card {
  padding: 15px;
}

.hub-event-date {
  color: #00abff;
  font-size: 13px;
  font-weight: 700;
}

.hub-event-title {
  margin-top: 4px;
  font-size: 17px;
  font-weight: 700;
}

.hub-two-col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.hub-empty {
  padding: 30px 10px;
  text-align: center;
  color: rgb(120, 120, 120);
  background: #fafafa;
  border: 1px dashed #ddd;
  border-radius: 10px;
}

.hub-error {
  color: #d0342c;
  font-size: 13px;
}

@media screen and (max-width: 900px) {
  .hub-member-grid {
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 200px), 1fr));
  }
}

@media screen and (max-width: 700px) {
  .hub-shell.content {
    margin-top: 22px;
    padding-left: 12px;
    padding-right: 12px;
  }

  .hub-header {
    grid-template-columns: 56px 1fr;
    gap: 12px;
    margin-bottom: 16px;
  }

  .hub-header-photo {
    width: 56px;
    height: 56px;
    border-radius: 10px;
  }

  .hub-title {
    font-size: 20px;
  }

  .hub-tabs {
    margin-bottom: 16px;
    overflow: hidden;
  }

  .hub-tabs-track {
    display: flex;
    flex-wrap: nowrap;
    align-items: flex-start;
    gap: 0;
    width: 100%;
    height: 40px;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
  }

  .hub-tabs-track::-webkit-scrollbar {
    display: none;
  }

  .hub-tabs .page-menu-button-outer {
    float: none;
    flex: 0 0 auto;
    height: 40px;
    margin-left: 12px;
  }

  .hub-tabs .page-menu-button-outer:first-child {
    margin-left: 0;
  }

  .hub-tabs .page-menu-button {
    padding: 5px 4px;
  }

  .hub-tabs .inbox-type-link {
    font-size: 14px;
  }

  .hub-tab-full {
    display: none;
  }

  .hub-tab-short {
    display: inline;
  }

  .hub-new-topic {
    min-height: 42px;
  }

  .hub-two-col {
    grid-template-columns: 1fr;
  }

  .hub-thread-header {
    flex-direction: column;
    align-items: stretch;
  }

  .hub-thread-header .hub-row-actions,
  .hub-thread-header .button-function {
    width: 100%;
  }

  .hub-topic-row {
    grid-template-columns: 1fr;
    gap: 8px;
    padding: 12px;
  }

  .hub-topic-count {
    display: flex;
    align-items: baseline;
    gap: 6px;
    text-align: left;
  }

  .hub-topic-count b {
    display: inline;
    font-size: 15px;
  }

  .hub-post-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }

  .hub-form-actions {
    justify-content: flex-start;
  }

  .hub-form-actions .button,
  .hub-form-actions .button-function {
    min-height: 40px;
  }

  .hub-member-grid {
    grid-template-columns: 1fr;
  }

  .hub-request-row {
    grid-template-columns: 40px 1fr;
    align-items: start;
  }

  .hub-request-aside {
    grid-column: 2;
    justify-content: flex-start;
  }
}

@media screen and (max-width: 480px) {
  .hub-title {
    font-size: 18px;
  }

  .hub-form-card,
  .hub-post,
  .hub-reply-box,
  .hub-settings-block {
    padding: 12px;
  }

  .hub-chips {
    flex-wrap: nowrap;
    overflow-x: auto;
    padding-bottom: 2px;
    -webkit-overflow-scrolling: touch;
  }

  .hub-chip {
    flex: 0 0 auto;
  }

  .hub-request-row {
    grid-template-columns: 36px 1fr;
    gap: 10px;
    padding: 12px;
  }

  .hub-avatar {
    width: 36px;
    height: 36px;
  }
}
</style>
