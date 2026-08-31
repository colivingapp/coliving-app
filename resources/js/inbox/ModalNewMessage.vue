<template>

  <div class="mnm-overlay" @mousedown.self="$emit('close')">

    <div class="mnm-modal">

      <div class="mnm-modal-header">
        <div class="mnm-modal-title">New Message</div>
        <button type="button" class="mnm-modal-close" @click="$emit('close')" aria-label="Close">
          <svg viewBox="0 0 20.94 20.94" width="14" height="14">
            <g transform="translate(85.309 -17.5)">
              <path d="m-66.353 19.484-16.972 16.972" class="mnm-modal-close-line" />
              <path d="m-83.325 19.484 16.972 16.972" class="mnm-modal-close-line" />
            </g>
          </svg>
        </button>
      </div>

      <div class="mnm-modal-body">
        <form class="mnm-form" @submit.prevent="sendMessage">
          <div class="mnm-field">
            <textarea ref="messageTextarea" class="textarea mnm-textarea" v-model="newMessage" placeholder="Type your message..." autofocus></textarea>
          </div>

          <div
            v-if="$route.name != 'ConversationMateMate' && showJR"
            class="mnm-join-toggle"
            :class="{ 'is-active': requestToJoin }"
            @click="requestToJoin = !requestToJoin"
          >
            <div class="mnm-join-icon">🏠</div>
            <div class="mnm-join-text">
              <div class="mnm-join-title">Request to Join</div>
              <div class="mnm-join-hint">Send this message as a request to join the space</div>
            </div>
            <div class="mnm-join-switch"><span></span></div>
          </div>

          <div class="mnm-actions">
            <button v-if="!buttonClicked" class="btn btn-md btn-primary" type="submit">Send</button>
            <div v-else class="mnm-sending">Sending...</div>
          </div>
        </form>
      </div>

    </div>

  </div>
  
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount, defineProps, defineEmits } from 'vue';

import { useRoute } from 'vue-router';
const route = useRoute();

const emit = defineEmits(['close', 'close-fetch']);
const props = defineProps(['showJR']);

const newMessage = ref('');
const requestToJoin = ref(false); 
const visitDetails = ref(''); 

let buttonClicked = ref(false);

const messageTextarea = ref(null);

onMounted(() => {
  document.documentElement.style.overflow = 'hidden';

  // Focus the textarea
  if (messageTextarea.value) {
    messageTextarea.value.focus();
  }

  const handleKeyDown = (e) => {
    if (e.keyCode === 27) {
      emit('close');
    }
  };

  document.addEventListener('keydown', handleKeyDown);

  onBeforeUnmount(() => {
    document.documentElement.style.overflow = 'visible';
    document.removeEventListener('keydown', handleKeyDown);
  });
});

const sendMessage = () => {
  if (newMessage.value == '') {
    return(alert('Message cannot be empty.'));
  }
  buttonClicked.value = true;
  // let url = '/api/conversations/' + route.params.uid
  let url = '';
  if (route.name == 'ConversationMateMate') {
    url = '/api/conversation/mate/' + route.params.uid;
  }
  else if (route.name == 'ConversationSpacePOV') {
    url = '/api/conversation/space/'+route.params.uid+'/mate/'+route.params.mate_uid
  }
  else {
    url = '/api/conversation/space/' + route.params.uid;
  }
  
  let data = { content: newMessage.value };

  if (requestToJoin.value) {
    url = '/api/join_requests/' + route.params.uid;
    data = { 
      content: newMessage.value,
      visitDetails: visitDetails.value,
    };
  }

  axios.post(url, data)
    .then(response => {
      emit('close-fetch');
      // const { conversation: updatedConversation, message: newMessageData } = response.data;
      // console.log(response.data)
      // conversation.value.messages = updatedConversation;
      
      
      // participants.value = response.data.participants;
      // conversation.value.messages = Object.values(response.data.conversation);
      // conversation.value.messages = conversation.value.messages.reverse();
      // newMessage.value = ''; // Clear the newMessage value
      // if (requestToJoin.value) {
      //   requestToJoin.value = false;
      //   visitDetails.value = '';
      // }
    })
    .catch(error => {
      console.error(error);
      buttonClicked.value = false;
    });
};
</script>

<style scoped>
.mnm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 17, 21, .6);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  overflow-y: auto;
  z-index: 9999;
  animation: mnm-fade-in .15s ease;
}

.mnm-modal {
  width: 100%;
  max-width: 540px;
  max-height: calc(100vh - 48px);
  display: flex;
  flex-direction: column;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 25px 60px rgba(0, 0, 0, .35), 0 4px 14px rgba(0, 0, 0, .15);
  overflow: hidden;
  animation: mnm-pop-in .18s ease;
}

@keyframes mnm-fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes mnm-pop-in {
  from { opacity: 0; transform: translateY(10px) scale(.97); }
  to { opacity: 1; transform: none; }
}

.mnm-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 18px 22px;
  border-bottom: 1px solid #eee;
  flex-shrink: 0;
}

.mnm-modal-title {
  font-size: 17px;
  font-weight: 700;
  color: #1a1a1a;
}

.mnm-modal-close {
  width: 32px;
  height: 32px;
  flex-shrink: 0;
  border: none;
  border-radius: 50%;
  background: var(--surface);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
  transition: background .15s ease;
}

.mnm-modal-close:hover {
  background: #e7ebf0;
}

.mnm-modal-close-line {
  fill: none;
  stroke: #666;
  stroke-width: 2.4;
  stroke-linecap: round;
}

.mnm-modal-body {
  padding: 22px 22px 24px;
  overflow-y: auto;
}

.mnm-form {
  display: flex;
  flex-direction: column;
}

.mnm-textarea {
  min-height: 190px;
  border-radius: 12px;
  padding: 12px 14px;
}

.mnm-join-toggle {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 16px;
  padding: 14px 16px;
  border: 1px solid var(--border-strong);
  border-radius: 14px;
  background: #fff;
  cursor: pointer;
  user-select: none;
  transition: border-color .15s ease, background .15s ease;
}

.mnm-join-toggle:hover {
  border-color: #c6c6c6;
}

.mnm-join-toggle.is-active {
  border-color: var(--sky);
  background: #f4faff;
}

.mnm-join-icon {
  width: 38px;
  height: 38px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--surface);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 19px;
}

.mnm-join-text {
  flex: 1;
  min-width: 0;
}

.mnm-join-title {
  font-size: 14px;
  font-weight: 700;
  color: #222;
}

.mnm-join-hint {
  font-size: 12px;
  color: #888;
  margin-top: 2px;
  line-height: 1.4;
}

.mnm-join-switch {
  position: relative;
  width: 40px;
  height: 22px;
  flex-shrink: 0;
  border-radius: 999px;
  background: #d8d8d8;
  transition: background .15s ease;
}

.mnm-join-switch span {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 2px rgba(0, 0, 0, .25);
  transition: transform .15s ease;
}

.mnm-join-toggle.is-active .mnm-join-switch {
  background: var(--sky);
}

.mnm-join-toggle.is-active .mnm-join-switch span {
  transform: translateX(18px);
}

.mnm-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: 18px;
}

.mnm-sending {
  font-size: 14px;
  color: #888;
}

@media screen and (max-width: 480px) {
  .mnm-overlay {
    padding: 0;
    align-items: flex-end;
  }

  .mnm-modal {
    max-width: 100%;
    max-height: 92vh;
    border-radius: 20px 20px 0 0;
  }
}
</style>
