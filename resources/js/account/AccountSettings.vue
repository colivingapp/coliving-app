<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const caUser = window.caUser;
const userLoggedIn = window.userLoggedIn;

const notifySpaceJoinReq = ref(false);
const notifyNewConvo = ref(false);
const notifyNewReview = ref(false);

const deleteSpaces = ref(false);
const newsletter = ref(false);
const showDeleteAcc = ref(false);

const accPassword = ref('');
const loading = ref(false);
const wrongPassword = ref(false);

function getAccountSettings() {
  axios.get('/api/acc_settings')
  .then(r => {
    // console.log('Account settings received:', r.data);
    if (r.data && r.data.notifications && r.data.notifications.length > 0) {
      newsletter.value = r.data.newsletter == 1 ? true : false;
      notifySpaceJoinReq.value = r.data.notifications[0].new_join_request_received == 1 ? true : false;
      notifyNewConvo.value = r.data.notifications[0].new_conversation_started == 1 ? true : false;
      notifyNewReview.value = r.data.notifications[0].new_review_received == 1 ? true : false;
      console.log("Account settings updated");
      // console.log(notifyNewConvo.value);
    } else {
      console.error('Invalid data format:', r.data);
    }
  })
  .catch(error => {
    console.error('Error fetching account settings:', error);
  });
}

function updateSettings() {
  axios.post('/api/update_settings', {
    notifySpaceJoinReq: notifySpaceJoinReq.value,
    notifyNewConvo: notifyNewConvo.value,
    notifyNewReview: notifyNewReview.value,
    newsletter: newsletter.value
  })
  .then(response => {
    // console.log('Settings updated:', response);
  })
  .catch(error => {
    console.error('Error updating settings:', error);
  });
}

onMounted(() => {
  getAccountSettings();
  watch(notifySpaceJoinReq, updateSettings);
  watch(notifyNewConvo, updateSettings);
  watch(notifyNewReview, updateSettings);
  watch(newsletter, updateSettings);
  // console.log(notifyNewConvo.value);
});

function deleteUser() {
  wrongPassword.value = false;
  loading.value = true;
  axios.post('/user/delete', {password: accPassword.value})
  .then((response) => {
    // console.log('Delete user response:', response);
    if (response.data == 'Wrong password') {
      wrongPassword.value = true;
      loading.value = false;
    } else if (response.data == 'success') {
      window.location.href = '/';
    }
  })
  .catch((error) => {
    loading.value = false;
    console.error('Error deleting user:', error);
    accPassword.value = '';
    if (error.response.status == 409) {
      // Handle specific error
    }
  });
}
</script>

<template>
  <div v-if="!userLoggedIn" class="content">
    <div class="page-form-header">
      <h1>Account Settings</h1>
    </div>
    <router-link to="/login">Log in</router-link>
  </div>
  <div v-else class="content">
    <div class="page-form-header">
      <h1>Account Settings</h1>
      <p class="page-form-intro">Notifications, newsletter, and account options.</p>
    </div>
    
    <div class="form-section">
      <h2>Notifications</h2>
      <p class="form-hint">Choose which emails you want to receive.</p>
      
      <div class="settings-stack">
        <div class="appify-checkbox">
          <input id="notifyNewConvo" type="checkbox" name="notifyNewConvo" class="appify-input-checkbox" v-model="notifyNewConvo"/>
          <label for="notifyNewConvo">
            <div class="checkbox-box" style="margin-top:-1px;">  
              <svg><use xlink:href="#checkmark" /></svg>
            </div> 
            New conversation started
          </label>
        </div>
        
        <div class="appify-checkbox">
          <input id="notifySpaceJoinReq" type="checkbox" name="notifySpaceJoinReq" class="appify-input-checkbox" v-model="notifySpaceJoinReq"/>
          <label for="notifySpaceJoinReq">
            <div class="checkbox-box" style="margin-top:-1px;">  
              <svg><use xlink:href="#checkmark" /></svg>
            </div> 
            New join request received
          </label>
        </div>
        
        <div class="appify-checkbox">
          <input id="notifyNewReview" type="checkbox" name="notifyNewReview" class="appify-input-checkbox" v-model="notifyNewReview"/>
          <label for="notifyNewReview">
            <div class="checkbox-box" style="margin-top:-1px;">  
              <svg><use xlink:href="#checkmark" /></svg>
            </div> 
            New review received
          </label>
        </div>
      </div>
    </div>

    <div class="form-section">
      <h2>Newsletter</h2>
      <div class="settings-stack">
        <div class="appify-checkbox">
          <input id="newsletter" type="checkbox" name="newsletter" class="appify-input-checkbox" v-model="newsletter"/>
          <label for="newsletter">
            <div class="checkbox-box" style="margin-top:-1px;">  
              <svg><use xlink:href="#checkmark" /></svg>
            </div> 
            Subscribe to Coliving App newsletter
          </label>
        </div>
      </div>
    </div>
    
    <div class="form-section">
      <h2>Delete Account</h2>
      <p class="form-hint">This permanently removes your account. This cannot be undone.</p>
      
      <button type="button" class="form-danger-btn" @click="showDeleteAcc = true" v-if="!showDeleteAcc">Delete Account</button>
      
      <div v-if="showDeleteAcc">
        <div class="form-group">
          <label class="form-label" for="accPassword">Enter your password</label>
          <input id="accPassword" v-model="accPassword" type="password" class="input" style="max-width:220px;">
          <p v-if="wrongPassword" class="form-error">Wrong password</p>
        </div>
        <div class="form-actions">
          <button type="button" class="form-danger-btn" v-if="!loading" @click="deleteUser()">Delete Account</button>
          <span v-else>Please wait...</span>
          <button type="button" class="form-cancel-btn" @click="showDeleteAcc = false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>