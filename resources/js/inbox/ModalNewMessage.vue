<template>

  <div class="modal-window">

    <div class="modal-content">

      <div class="modal-top">
        <div class="modal-top-inner">
          
          <div class="modal-title">New Message</div>

          <span @click="$emit('close')" class="modal-close">
            <div class="modal-close-btn">
                <svg version="1.1" viewBox="0 0 20.94 20.94" xmlns="http://www.w3.org/2000/svg">
                    <g transform="translate(85.309 -17.5)">
                        <path d="m-66.353 19.484-16.972 16.972" class="modal-close-btn-line" />
                        <path d="m-83.325 19.484 16.972 16.972" class="modal-close-btn-line" />
                    </g>
                </svg>
            </div>
          </span>
        </div>
      </div>

      <div class="modal-bottom">

        <div class="modal-spacer" style="clear:both;height:25px;"></div>

        <div class="modal-content-bottom">
          
          <div class="modal-content-bottom-inner">

            <div style="padding:20px;line-height:160%;">
            <form class="message-input-form" @submit.prevent="sendMessage">
              <div>
                <textarea ref="messageTextarea" class="textarea" v-model="newMessage" placeholder="Type your message..." autofocus style="min-height:260px;"></textarea>
              </div>

              <div style="float:left;">
                <div class="join-request-form" v-if="$route.name != 'ConversationMateMate' && showJR">
                  <!-- <div style="width:140px;margin-right:10px;float:left;display:grid;grid-template-columns:20px 120px 1fr">
                    <input type="checkbox" id="requestToJoin" v-model="requestToJoin">
                    <label for="requestToJoin">Request to Join</label>
                  </div> -->

                  <div class="appify-checkbox" style="width:150px;margin-top:7px;">
                    <input id="joinreq" type="checkbox" name="joinreq" class="appify-input-checkbox" v-model="requestToJoin"/>
                    <label for="joinreq">
                    <div class="checkbox-box" style="margin-top:2px;">  
                        <svg><use xlink:href="#checkmark" /></svg>
                    </div> 
                    <span style="font-size:17px;font-weight:bold;">Request to Join</span>
                    </label>
                  </div>

                </div>
              </div>

              <div style="float:right;">
                <button v-if="!buttonClicked" class="message-send-button" type="submit" style="width:80px;">Send</button>
                <div v-else style="text-align:center;width:80px;">Sending...</div>
              </div>

              <div style="height:40px;"></div>
            </form>
            </div>
          
          </div>

        </div>

        <div class="modal-spacer"  style="height:60px;"></div>

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
    });
};
</script>