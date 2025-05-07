<script setup>
const caUser = window.caUser;
const userLoggedIn = window.userLoggedIn;

function logOut() {
  axios.post('/logout')
  .then(response => {
    window.location = '/';
  })
}
</script>

<template>
	<div v-if="!userLoggedIn" class="content">
		<h1>Account Settings</h1>
		<br>
		<router-link to="/login">Log in</router-link>
	</div>
	<div v-else class="content">
		<h1>Account</h1>
		Hey {{  caUser.userEmail  }}
		<br><br><br>
		<router-link to="/mate/update">Profile</router-link>
		<br><br>
		<router-link to="/bookmarks">Bookmarks</router-link>
		<br><br>
		<router-link to="/my-spaces">My Spaces</router-link>
		<br><br>
		<router-link to="/account/settings">Settings</router-link>
		<br><br>
		<br>
		<span v-if="userLoggedIn" @click="logOut()" class="link">Log Out</span>
	</div>
</template>

<style scoped>

</style>