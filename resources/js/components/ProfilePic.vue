<template>
    <div v-if="profile_pic == 'photo'" class="profile-photo">
      <img :src="photoSrc" class="profile-photo-img" alt="" @error="onPhotoError"/>
    </div>
    <div v-else><multiavatar :avatar="avatar" /></div>
</template>

<script>
import Multiavatar from "../components/Multiavatar.vue";
import { matePhotoUrl } from "../mediaUrls.js";

export default {
    props: [ 'profile_pic', 'photo', 'avatar' ],

    components: {
        Multiavatar,
    },

    data() {
        return {
            useThumb: true,
        };
    },

    computed: {
        photoSrc() {
            return matePhotoUrl(this.photo, this.useThumb ? 'thumb' : 'full');
        },
    },

    watch: {
        photo() {
            this.useThumb = true;
        },
    },

    methods: {
        onPhotoError(event) {
            if (this.useThumb) {
                this.useThumb = false;
                return;
            }
            event.target.style.display = 'none';
        },
    },
}
</script>

<style>
.profile-photo {
  border-radius: 50%;
  overflow: hidden;
}

.profile-photo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}
</style>
