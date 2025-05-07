<template>
  <div class="content">
    <h1 style="margin-top:-10px;margin-bottom:10px;">Conversation</h1>
    <span v-if="$route.params.uid != mate.uid">With {{ conversationType }}: <router-link :to="conversationWithUrl">{{ conversationWithName }}</router-link></span>
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
          <div v-else  style="min-height:100px;max-height:100%;overflow-y:auto;" id="conv-messages">
            <!-- Display conversation content -->
            <div v-for="message in conversation.messages" :key="message.id" >
              <div style="display:grid;grid-template-columns:25px 5px 1fr;">
                <div>
                  <!-- /img/coliving-app-logo-symbol.png -->
                  <profile-pic v-if="participants[message.sender_uid].type == 'mate'" :profile_pic="participants[message.sender_uid].profile_pic" :avatar="participants[message.sender_uid].avatar" :photo="participants[message.sender_uid].photo" style="height:25px;width:25px;overflow:hidden;"/>
                  <img v-else src="/img/coliving-app-logo-symbol.png" style="height:25px;width:25px;"/>
                </div>
                <div></div>
                <div style="padding-top:5px;">
                  <b>{{ getDisplayName(message.sender_uid) }}</b>
                  <br>
                  {{ convertToDate(message.created_at) }}
                </div>
              </div>
              <div style="display:grid;grid-template-columns:30px 1fr;margin-bottom:20px;">
                <div></div>
                <div style="font-size:14px;white-space: pre-wrap;">
                  <div style="height:10px;"></div>{{ message.content }}
                </div>

                <!-- Display join request data if it exists -->
                <div></div>
                <div v-if="message.join_request" class="join-request">
                  <div style="height:5px;"></div>
                  <div v-if="message.join_request.status == 'pending'">
                    <span style="color:#ff9b00;">Join request status: <b>Pending</b></span>
                    <span v-if="route.name == 'ConversationSpacePOV'">
                      <div style="height:5px;"></div>
                      <span v-if="jrProcessing">Please wait...</span>
                      <span v-else>
                        <button @click="joinRequestAccept(message.join_request.id)">Accept</button> <button @click="joinRequestDecline(message.join_request.id)">Decline</button>
                      </span>
                    </span>
                  </div>
                  <div v-else-if="message.join_request.status == 'declined'">
                    <span style="color:red;">
                      Join request status: <b>Declined</b>
                      <br>
                      {{ convertToDate(message.join_request.updated_at) }}
                    </span>
                  </div>
                  <div v-else-if="message.join_request.status == 'expired'">
                    <span style="color:#bfbfbf;">Join request status: <b>Expired</b></span>
                  </div>
                  <div v-else>
                    <span style="color:green;">
                      Join request status: <b>Accepted</b>
                      <br>
                      {{ convertToDate(message.join_request.updated_at) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div></div>
        <div>
          <button class="button" @click="newMessageModal = true" style="width:100%;">Send new message</button>
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
import { ref, onMounted, onBeforeUnmount } from 'vue';

import { useRoute } from 'vue-router';
const route = useRoute();

import ProfilePic from '../components/ProfilePic.vue';
// import Multiavatar from '../components/Multiavatar.vue';
import ModalNewMessage from './ModalNewMessage.vue';

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
    return participants.value[uid].name;
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
      isSpaceClaimed.value = spaceEntry?.claimed === 1;
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
      
    if(response.data.conversation != 'Conversation has not been started.') {
      conversation.value.messages = Object.values(response.data.conversation);
      conversation.value.messages = conversation.value.messages.reverse();
    }

    isLoading.value = false;
    toBottom();
  })
  .catch(error => {
    console.error(error);
  });
}

function toBottom() {
    setTimeout(() => {
      var objDiv = document.getElementById("conv-messages");
      objDiv.scrollTop = objDiv.scrollHeight;

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
}

function joinRequestDecline(id) {
  jrProcessing.value = true;
  axios.post('/api/join_requests/'+id+'/'+route.params.mate_uid+'/decline')
  .then(closeFetch)
}
</script>


<style scoped>
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

.conversation-area {
  height: 400px;
  overflow-y: auto;
  margin-bottom: 20px;
}

.message-row {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.avatar {
  margin-right: 10px;
}

.message-sender {
  font-weight: bold;
}

.message-content {
  margin-left: 35px;
}

/* .message-input-form {
  display: flex;
  gap: 10px;
} */

.message-input {
  flex-grow: 1;
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
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
}
</style>