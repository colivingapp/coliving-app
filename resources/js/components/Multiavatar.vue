<template>
    <!-- Avatar SVG generated via multiavatar — safe from XSS -->
    <div v-html="avatarContent"></div>
</template>
  
<script>
  import { ref, onMounted, watch } from 'vue';
  // import multiavatar from '@multiavatar/multiavatar/esm';
  
  export default {
    props: {
      avatar: {
        type: String,
        required: true
      }
    },
    data() {
      return {
        avatarContent: '',
        multiavatar: window.multiavatar
      };
    },
    mounted() {
      this.generateAvatar();
    },
    watch: {
      avatar() {
        this.generateAvatar();
      }
    },
    methods: {
      generateAvatar() {
        this.avatarContent = multiavatar(this.avatar);
      }
    }
  };
</script>
  