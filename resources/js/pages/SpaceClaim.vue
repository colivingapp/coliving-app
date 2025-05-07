<script setup>
import { ref, onMounted, computed  } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const space = ref(null);
const claim = ref(false);

let comments = ref('');

const userLoggedIn = window.userLoggedIn;

let mate = ref('');
if (userLoggedIn) {
  mate.value = window.mate;
}

const { uid } = route.params;

const fetchSpace = async () => {
  try {
    const response = await axios.get(`/api/space/${uid}`);
    space.value = response.data.space;
  } catch (error) {
    console.error(error);
  }
};

onMounted(fetchSpace);

const isClaimed = async () => {
  try {
    const response = await axios.get(`/api/space/${uid}/claim`);
    claim.value = response.data;
  } catch (error) {
    console.error(error);
  }
};

onMounted(isClaimed);

function claimSpace() {
  claim.value = true;
  axios.post(`/api/space/${uid}/claim`, {
    comments: comments.value
  })
  .then(resp => {
    if (resp.data == 'approved') {
      claim.value = 'approved';
    }
    else if (resp.data != 'success') {
      claim.value = false;
    }
    else {
      claim.value = 'pending';
    }
  })
  .catch(error => console.log(error));
}

</script>

<template>
  <div class="content">
    <div style="height:20px;"></div>
   
    <div v-if="space">
      <div class="space-name">{{ space.name }}</div>

      <h2>Claim This Space</h2>
      <span v-if="!claim">
        This coliving space profile has not been claimed yet. If you represent this coliving space, claim your profile now! You will be able to update your profile with additional details, manage photos, set a unique username, accept coliving mates, and more.
        <br><br>
        For a quick approval, please claim the space from the account that is associated with the official space's email. If the official email is not available, you can provide additional details that can help us to validate that you are the legitimate representative of the space.
        <br><br>
        <textarea placeholder="Comments" v-model="comments" style="height:120px;padding:5px;"></textarea>
        <div style="height:30px;"></div>
        <button class="action-button button-1" @click="claimSpace" style="width:140px;">Claim</button>

        <div style="clear:both;height:40px;"></div>
      </span>
      <span v-else-if="claim == 'declined'">
        Your claim was declined. For more details or if you want to claim this space again, please contact us by email.
        <br><br>
        Status: <b>DECLINED</b>
      </span>
      <span v-else-if="claim == 'approved'">
        Your claim was approved. You can now manage your space profile!
        <br><br>
        Status: <b>APPROVED</b>
      </span>
      <span v-else-if="claim == 'pending'">
        Your claim was successfully received and it's currently pending.
        <br><br>
        Status: <b>PENDING</b>
      </span>
      <span v-else>
        Please wait...
      </span>
    </div>
  </div>
</template>

<style>
.space-name {
  font-size: 22px;
  font-weight: bold;
}

.space-photo {
  width: 100%;
}

.space-photo-small {
  width: 100px;
  float: left;
  display: inline;
}
</style>