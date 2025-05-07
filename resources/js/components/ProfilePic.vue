<template>
    <div v-if="profile_pic == 'photo'" style="border-radius:50%;overflow:hidden;"><img :src="photo" class="img-fit-cover"/></div>
    <div v-else><multiavatar :avatar="avatar" /></div>
</template>

<script>
import Multiavatar from "../components/Multiavatar.vue";

export default {
    props: [ 'profile_pic', 'photo', 'avatar' ],

    components: {
        Multiavatar,
    },
}
</script>