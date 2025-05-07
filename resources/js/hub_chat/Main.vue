<template>
  <div >
    <div style="height:10px;"></div>

    <div v-if="userLoggedIn">
      <div v-if="$route.params.uid == mate.uid">
        One cannot chat with oneself.
      </div>
      
      <div v-else class="convo-grid" >
        <div>
          <div v-if="isLoading" class="spinner-container">
            <div class="spinner"></div>
          </div>
          <div v-else-if="!conversation.messages.length">
            The chat has not been started.
            <br>
            Send a new message to start the chat.
            <div style="height:20px;"></div>
          </div>
          <div v-else  style="min-height:100px;max-height:100%;overflow-y:auto;" id="conv-messages">
            <!-- Display conversation content -->
            <div v-for="message in conversation.messages" :key="message.id" >
              <div style="display:grid;grid-template-columns:25px 5px 1fr;">
                <div>
                  <!-- /img/coliving-app-logo-symbol.png -->
                  <profile-pic :profile_pic="indexedMates[message.sender_uid].profile_pic" :avatar="indexedMates[message.sender_uid].avatar" :photo="indexedMates[message.sender_uid].photo" style="height:25px;width:25px;overflow:hidden;"/>
                </div>
                <div></div>
                <div style="padding-top:5px;">
                  <b>{{ getDisplayName(message.sender_uid) }}</b>
                  <br>{{ convertToDate(message.created_at) }}
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

let isLoading = ref(true);

const props = defineProps({
  mates: {
    type: Array,
    required: true,
    default: () => []
  }
});

// Transform the mates array into an object indexed by uid
const indexedMates = props.mates.reduce((acc, mate) => {
  acc[mate.uid] = mate;
  return acc;
}, {});

import ProfilePic from '../components/ProfilePic.vue';
import ModalNewMessage from './ModalNewMessage.vue';

let receiverId = ref('');
let senderId = ref('');
let newMessageModal = ref(false);
const mate = ref(window.mate);

let conversationWithUrl = ref('');
let conversationWithName = ref('');
let conversationType = ref('');

let showJR = ref(true);
let jrProcessing = ref(false);

const userLoggedIn = window.userLoggedIn;

const conversation = ref({
  messages: [],
});

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
  if (!indexedMates[uid].name) {
    return uid;
  }
  else {
    return indexedMates[uid].name;
  }
}

let intervalId;

onMounted(() => {
  retrieveConversation();

  // // Set up an interval to retrieve the conversation messages every 30 seconds
  // intervalId = setInterval(retrieveConversation, 30000);
});

const retrieveConversation = () => {
  let url = '/api/conversation/hub/'+route.params.uid;

  axios.get(url)
    .then(response => {
      conversationType.value = 'mate';

      conversation.value.messages = Object.values(response.data.conversation);
      conversation.value.messages = conversation.value.messages.reverse();

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
  display:grid;grid-template-rows:calc(100vh - 400px) 20px 30px;
  /* display:grid;grid-template-rows:480px 20px 30px; */
}

@media screen and (max-width:640px) {
  .convo-grid {
    display:grid;grid-template-rows:calc(100vh - 340px) 20px 30px;
    /* display:grid;grid-template-rows:calc(100vh - 280px) 20px 30px; */
  }
}
</style>