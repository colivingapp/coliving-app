<template>
  <div class="content">
    <div class="convo-page-header">
      <router-link :to="inboxBackTo" class="convo-back" aria-label="Back to inbox">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
          <path d="M14.5 5.5 8 12l6.5 6.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </router-link>
      <div class="convo-page-title">
        <h1>Conversation</h1>
        <span v-if="$route.params.uid != mate.uid" class="convo-with-line">
          <span class="convo-with-label">With {{ conversationType }}:</span>
          <router-link :to="conversationWithUrl" class="convo-with-name">{{ conversationWithName }}</router-link>
        </span>
      </div>
    </div>
    <div style="height:40px;"></div>

    <div v-if="userLoggedIn">
      <div v-if="$route.params.uid == mate.uid">
        One cannot chat with oneself.
      </div>
      
      <div v-else class="convo-grid" >
        <div v-if="isLoading" class="spinner-container">
          <div class="spinner"></div>
        </div>
        <div v-else>
          <div v-if="!conversation.messages.length" class="info-bubble">
            ✉️ To start the conversation, simply send a message.
            <span v-if="conversationType == 'space'">
              <br><br>
              <b>🏠 To join the space, include a join request in your message.</b>
            </span>
          </div>
          <div v-if="!conversation.messages.length && !isSpaceClaimed" class="info-bubble">
            ⚠️ Attention: This space hasn't been claimed yet. Your message will be forwarded, but replies are less likely. If you don't hear back, try contacting the space directly.
          </div>
          <div v-else style="min-height:100px;max-height:100%;overflow-y:auto;" id="conv-messages">
            <!-- Display conversation content -->
            <div
              v-for="message in conversation.messages"
              :key="message.id"
              class="msg-row"
              :class="{ 'msg-own': isOwnMessage(message) }"
            >
              <div class="msg-avatar" :class="{ 'msg-avatar-is-space': participants[message.sender_uid] && participants[message.sender_uid].type != 'mate' }">
                <profile-pic
                  v-if="participants[message.sender_uid] && participants[message.sender_uid].type == 'mate'"
                  :profile_pic="participants[message.sender_uid].profile_pic"
                  :avatar="participants[message.sender_uid].avatar"
                  :photo="participants[message.sender_uid].photo"
                  style="width:100%;height:100%;overflow:hidden;border-radius:50%;"
                />
                <div v-else class="msg-avatar-space">
                  <img
                    :src="spaceAvatarSrc(participants[message.sender_uid])"
                    @error="$event.target.src = '/img/coliving-app-logo-symbol.png'"
                  >
                </div>
              </div>

              <div class="msg-col">
                <div class="msg-meta">
                  <span class="msg-sender">{{ getDisplayName(message.sender_uid) }}</span>
                  <span class="msg-time">{{ convertToDate(message.created_at) }}</span>
                </div>

                <div class="msg-bubble">{{ message.content }}</div>

                <!-- Join request widget -->
                <div v-if="message.join_request" class="jr-card" :class="'jr-status-' + message.join_request.status">
                  <div class="jr-card-head">
                    <span class="jr-card-title">🏠 Join Request</span>
                    <span class="jr-badge">{{ jrStatusLabel(message.join_request.status) }}</span>
                  </div>
                  <div class="jr-card-body">
                    <div v-if="message.join_request.status == 'pending'">
                      <span v-if="route.name == 'ConversationSpacePOV'">
                        <span v-if="jrProcessing" class="jr-note">Please wait...</span>
                        <span v-else class="jr-actions">
                          <button type="button" class="btn btn-sm btn-join" @click="joinRequestAccept(message.join_request.id)">Accept</button>
                          <button type="button" class="btn btn-sm btn-danger" @click="joinRequestDecline(message.join_request.id)">Decline</button>
                        </span>
                      </span>
                      <span v-else class="jr-note">Awaiting a response from the space.</span>
                    </div>
                    <div v-else class="jr-note jr-date">{{ convertToDate(message.join_request.updated_at) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div></div>
        <div>
          <button type="button" class="btn btn-md btn-join btn-block" @click="newMessageModal = true">Send new message</button>
        </div>
      </div>

      <div v-if="newMessageModal">
        <modal-new-message @close="newMessageModal = false" @close-fetch="closeFetch();" :showJR="showJR" />
      </div>

    </div>

    <div v-else>
      <router-link to="/login">Login</router-link>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

import { useRoute } from 'vue-router';
const route = useRoute();

const inboxBackTo = computed(() => {
  if (route.name === 'ConversationSpacePOV') {
    return { name: 'inboxMySpaces' };
  }
  return { name: 'inbox' };
});

import ProfilePic from '../components/ProfilePic.vue';
// import Multiavatar from '../components/Multiavatar.vue';
import ModalNewMessage from './ModalNewMessage.vue';
import { spacePhotoUrl } from '../mediaUrls.js';

let receiverId = ref('');
let senderId = ref('');
let newMessageModal = ref(false);
const mate = ref(window.mate);


let conversationWithUrl = ref('');
let conversationWithName = ref('');
let conversationType = ref('');
let isSpaceClaimed = ref(true);

let showJR = ref(true);
let jrProcessing = ref(false);

let isLoading = ref(true);

const userLoggedIn = window.userLoggedIn;

const conversation = ref({
  messages: [],
});

const participants = ref({});

// When viewing a conversation as the space (admin replying on behalf of a space),
// "self" is the space uid. Otherwise "self" is the logged-in mate.
function isOwnMessage(message) {
  if (route.name === 'ConversationSpacePOV') {
    return message.sender_uid === route.params.uid;
  }
  return message.sender_uid === mate.value.uid;
}

function jrStatusLabel(status) {
  if (status == 'pending') return 'Pending';
  if (status == 'declined') return 'Declined';
  if (status == 'expired') return 'Expired';
  return 'Accepted';
}

function spaceAvatarSrc(participant) {
  if (participant?.first_photo) {
    return spacePhotoUrl(participant.first_photo);
  }
  return '/img/coliving-app-logo-symbol.png';
}

function convertToDate(originalDate) {
  const date = new Date(originalDate);
  const timeOptions = { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true };
  const time = date.toLocaleString('en-US', timeOptions);
  const dateOptions = { month: 'numeric', day: 'numeric', year: 'numeric' };
  const formattedDate = date.toLocaleString('en-US', dateOptions);
  return `${time}, ${formattedDate}`;
}

function closeFetch() {
  newMessageModal.value = false
  jrProcessing.value = false;
  retrieveConversation();
}

function getDisplayName(uid) {
  let type = 'mate_mate';

  if (typeof(participants.value[uid]) == 'undefined') {
    for (const mate in participants.value) {
      if (participants.value[mate].type == 'space') {
        uid = mate;
        type = 'mate_space';
      }
    }
  }

  if (participants.value[uid].type == 'space') {
    return participants.value[uid].name || participants.value[uid].username || '';
  }
  else {
    if (!participants.value[uid].name) {
      return uid;
    }
    else {
      return participants.value[uid].name;
    }
  }
}

let intervalId;

onMounted(() => {
  retrieveConversation();

  // // Set up an interval to retrieve the conversation messages every 30 seconds
  // intervalId = setInterval(retrieveConversation, 30000);
});

const retrieveConversation = () => {
  let url = '';
  if (route.name == 'ConversationMateMate') {
    url = '/api/conversation/mate/'+route.params.uid;
    conversationWithUrl.value = '/mate/'+route.params.uid;
  }
  else if (route.name == 'ConversationSpacePOV') {
    url = '/api/conversation/space/'+route.params.uid+'/mate/'+route.params.mate_uid
    conversationWithUrl.value = '/mate/'+route.params.mate_uid;
    // console.log(route.params.mate_uid, mate.value.uid)
    if (route.params.mate_uid != mate.value.uid) {
      showJR.value = false;
    }
  }
  else {
    url = '/api/conversation/space/'+route.params.uid;
    conversationWithUrl.value = '/space/'+route.params.uid;
  }
  axios.get(url)
  .then(response => {
    // console.log(response.data)

    participants.value = response.data.participants;

    // console.log(route.name)
    if (route.name == 'ConversationMateMate') {
      conversationType.value = 'mate';
    }
    else if (route.name == 'ConversationMateSpace') {
      conversationWithName.value = getDisplayName(route.params.mate_uid);
      conversationType.value = 'space';

      const participantsObj = participants.value;

      // Find the space participant
      const spaceEntry = Object.values(participantsObj).find(p => p.type === 'space');

      // Check if it's claimed
      isSpaceClaimed.value = Number(spaceEntry?.claimed) === 1;
      // console.log(isSpaceClaimed.value)
    }
    else if (route.name == 'ConversationSpacePOV') {
      conversationWithName.value = getDisplayName(route.params.mate_uid);
      conversationType.value = 'mate';
    }

    if (route.name == 'ConversationMateMate' || route.name == 'ConversationMateSpace') {
      conversationWithName.value = getDisplayName(route.params.uid);
    }
    else if (route.name == 'ConversationSpacePOV') {
      conversationWithName.value = getDisplayName(route.params.mate_uid);
    }
      
    const thread = response.data.conversation;
    if (thread && thread !== 'Conversation has not been started.') {
      conversation.value.messages = Object.values(thread);
      conversation.value.messages = conversation.value.messages.reverse();
    }

    isLoading.value = false;
    toBottom();
  })
  .catch(error => {
    console.error(error);
    isLoading.value = false;
  });
}

function toBottom() {
    setTimeout(() => {
      var objDiv = document.getElementById("conv-messages");
      if (objDiv) {
        objDiv.scrollTop = objDiv.scrollHeight;
      }
    }, 100);
  }
            
const checkEnterKey = (e) => {
  if (!e.shiftKey) {
    e.preventDefault();
    sendMessage();
  }
}

function joinRequestAccept(id) {
  jrProcessing.value = true;
  axios.post('/api/join_requests/'+id+'/accept')
  .then(closeFetch)
  .catch(() => { jrProcessing.value = false; })
}

function joinRequestDecline(id) {
  jrProcessing.value = true;
  axios.post('/api/join_requests/'+id+'/'+route.params.mate_uid+'/decline')
  .then(closeFetch)
  .catch(() => { jrProcessing.value = false; })
}
</script>


<style scoped>
.convo-page-header {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-top: -10px;
}

.convo-back {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  margin-top: 19px;
  flex-shrink: 0;
  border-radius: 50%;
  color: #444;
  text-decoration: none;
  transition: background .15s ease, color .15s ease;
}

.convo-back:hover {
  background: var(--surface);
  color: #111;
}

.convo-page-title h1 {
  margin: 0;
  padding-top: 19px;
  margin-bottom: 10px;
}

.convo-page-title {
  min-width: 0;
}

.convo-with-line {
  display: flex;
  align-items: baseline;
  gap: 4px;
  max-width: 100%;
  min-width: 0;
}

.convo-with-label {
  flex-shrink: 0;
}

.convo-with-name {
  display: inline-block;
  max-width: min(42ch, 100%);
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  vertical-align: bottom;
}

.info-bubble {
  background-color: #f1f1f1;
  color: #333;
  padding: 15px 20px;
  border-radius: 12px;
  font-size: 14px;
  line-height: 1.5;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

/* CHAT MESSAGES */
.msg-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 22px;
}

.msg-avatar {
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  border-radius: 50%;
  overflow: hidden;
  background: #f2f2f2;
}

.msg-avatar-is-space {
  border-radius: 5px;
  border: 2px solid #fff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .14);
}

.msg-avatar-space {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.msg-avatar-space img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.msg-col {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  max-width: 78%;
}

.msg-meta {
  display: flex;
  align-items: baseline;
  gap: 7px;
  margin-bottom: 5px;
  font-size: 11.5px;
}

.msg-sender {
  font-weight: 700;
  color: #444;
}

.msg-time {
  color: #a5a5a5;
}

.msg-bubble {
  background: #eef0f3;
  color: #222;
  padding: 10px 14px;
  border-radius: 15px;
  font-size: 14px;
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-word;
}

.msg-row.msg-own .msg-bubble {
  background: var(--sky);
  color: #fff;
}

/* JOIN REQUEST WIDGET */
.jr-card {
  margin-top: 8px;
  width: 100%;
  border-radius: 12px;
  border: 1px solid var(--border-strong);
  background: #fff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
  overflow: hidden;
}

.jr-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 10px 14px;
  background: var(--surface);
  border-bottom: 1px solid #ececec;
}

.jr-card-title {
  font-size: 13px;
  font-weight: 700;
  color: #222;
  white-space: nowrap;
}

.jr-badge {
  font-size: 10.5px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: var(--pill);
  text-transform: uppercase;
  letter-spacing: .03em;
  white-space: nowrap;
}

.jr-status-pending .jr-badge {
  background: rgba(255, 155, 0, .15);
  color: #b06b00;
}

.jr-status-accepted .jr-badge {
  background: rgba(116, 213, 98, .22);
  color: #1a9d3d;
}

.jr-status-declined .jr-badge {
  background: var(--danger-bg);
  color: var(--danger);
}

.jr-status-expired .jr-badge {
  background: #eee;
  color: #888;
}

.jr-card-body {
  padding: 12px 14px;
  font-size: 13px;
}

.jr-actions {
  display: flex;
  gap: 8px;
}

.jr-note {
  color: #8a8a8a;
}

@media screen and (max-width: 450px) {
  h1 {
    padding-top: 0px;
  }
}

.convo-grid {
  display:grid;grid-template-rows:calc(100vh - 370px) 20px 30px;
}

@media screen and (max-width:640px) {
  .convo-grid {
    display:grid;grid-template-rows:calc(100vh - 340px) 20px 30px;
  }

  .convo-with-name {
    max-width: min(18ch, 100%);
  }
}
</style>
