<template>
  <div class="modal-window">

    <div class="modal-content">

      <div class="modal-top">
        <div class="modal-top-inner">

          <div class="modal-title">Feedback</div>

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

        <div v-if="userLoggedIn" class="modal-content-bottom">
          
          <div v-if="feedbackPrep" class="modal-content-bottom-inner">

            <div style="font-size:14px;text-align:left;padding:20px;">
              We'd love to hear your feedback on the Coliving App. Share your thoughts here to help us enhance your experience.
            </div>

            <div style="padding:20px;line-height:160%;">
            <form class="message-input-form" @submit.prevent="sendMessage">
              <div>
                <textarea class="textarea" v-model="newMessage" placeholder="Type your message..." autofocus style="min-height:260px;"></textarea>
              </div>


              <div style="float:right;">
                <button v-if="!buttonClicked" class="message-send-button" type="submit" style="width:80px;" @click="submitFeedback">Send</button>
                <div v-else style="text-align:center;width:80px;">Sending...</div>
              </div>

              <div style="height:40px;"></div>
            </form>
            </div>
          
          </div>

          <div v-else-if="feedbackSuccess" style="padding-top:60px;padding-bottom:60px;">
            Success
          </div>

          <div v-else-if="feedbackError">
            Error
          </div>

        </div>

        <div v-else class="modal-content-bottom" style="height:200px;padding-top:40px;font-size:14px;">
          To submit a feedback, please <router-link to="/login" @click="$emit('close')">LOG IN</router-link>
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

let feedbackSuccess = ref(false);
let feedbackError = ref(false);
let feedbackPrep = ref(true);

const userLoggedIn = window.userLoggedIn;

onMounted(() => {
  document.documentElement.style.overflow = 'hidden';

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


function submitFeedback() {
    buttonClicked.value = true;

    axios({
        method: 'post',
        url: '/api/feedback',
        data: {feedback: newMessage.value}
    })
    .then((response) => {
        if (response.data == 'success') {
            feedbackSuccess.value = true;
            feedbackError.value = false;
            feedbackPrep.value = false;
        }
        else {
            feedbackSuccess.value = false;
            feedbackError.value = true;
            feedbackPrep.value = false;
        }
    });

}

</script>