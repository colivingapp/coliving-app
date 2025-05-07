<template style="height:100%;">
    <!-- <div style="height:100%;" :class="{ 'grid-home': isHomeRoute, 'grid': !isHomeRoute && !isHubRoute, 'grid-hub': isHubRoute }"> -->
    <!-- <div style="height:100%;" :class="{ 'grid-home': isHomeRoute, 'grid': !isHomeRoute && !isHubRoute }"> -->
    <div style="height:100%;" :class="{  'grid': !isHubRoute }">
        <div>
            <app-header />
        </div>

        <div>
            <router-view />
        </div>

        <div>
            <app-footer />
        </div>
    </div>
</template>

<script setup>
    import AppHeader from "./layout/AppHeader.vue";
    import AppFooter from "./layout/AppFooter.vue";

    import { watch, ref, provide, reactive } from 'vue';

    const inboxStatus = reactive({
        hasUnreadPersonal: window.mate?.hasUnreadPersonal ?? false,
        hasUnreadSpaces: window.mate?.hasUnreadSpaces ?? false,
    })
    provide('inboxStatus', inboxStatus)

    import { useRoute, useRouter } from 'vue-router';
    
    const router = useRouter();
    const route = useRoute();
    let isHomeRoute = ref(false);
    let isHubRoute = ref(false);
    let isConversationRoute = ref(false);
    isHomeRoute.value = route.path === '/';

    window.vueVM3 = { navigateTo };

    function navigateTo(event, path) {
        if (!event.shiftKey && !event.ctrlKey && !event.altKey && !event.metaKey) {
            event.preventDefault();
            router.push({ path: path });
        }
    }

    watch(() => route.name, (newName) => {
        isHomeRoute.value = newName === 'Home';
        isConversationRoute.value = newName === 'ConversationMateSpace' || newName === 'ConversationMateMate' || newName === 'ConversationSpacePOV';
    });
</script>

<style>
    .grid-home {
        display: grid;
        grid-template-rows: 60px 1fr 80px;
        /* grid-template-rows: 60px 1fr 115px; */
        /* grid-template-rows: 60px 1fr 130px; */
        /* overflow: hidden; */
    }
    .grid {
        display: grid;
        grid-template-rows: 60px 1fr auto;
    }
    .grid-hub {
        display: grid;
        grid-template-rows: 60px 1fr 100px;
    }

    /* @media screen and (max-width: 700px) {
        .grid {
            grid-template-rows: 100px 1fr auto;
        }
    } */
</style>