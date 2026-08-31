<template>
    <div class="content" >
        <h1>Inbox</h1>
        Inbox is where you'll find your personal conversations with spaces and mates. Additionally, if you are an admin of a space, you will also see here messages and join requests from mates sent to your spaces.
        <div style="height:10px;"></div>

        <div v-if="userLoggedIn">

            <div style="height:35px;"></div>

            <div class="inbox-tabs noselect">
                <div class="inbox-tabs-track">
                    <div class="page-menu-button-outer">
                        <div class="page-menu-button" @click="inboxTypeChange('personal')">
                            <span class="inbox-type-link" :class="{ active : inboxType == 'personal' }">
                                Personal ({{ conversations_mate_pov.length }})
                                <span v-if="hasNewPersonal" style="color:red;">●</span>
                            </span>
                        </div>
                        <div class="page-menu-underline" :class="{ active: inboxType == 'personal' }"></div>
                    </div>
                    <div class="page-menu-button-outer">
                        <div class="page-menu-button" @click="inboxTypeChange('spaces')">
                            <span class="inbox-type-link" :class="{ active : inboxType == 'spaces' }">
                                My Spaces ({{ conversations.length }})
                                <span v-if="hasNewSpaces" style="color:red;">●</span>
                            </span>
                        </div>
                        <div class="page-menu-underline" :class="{ active: inboxType == 'spaces' }"></div>
                    </div>
                </div>
            </div>

            <div v-if="loaded">
                <div style="height:30px;"></div>

                <div class="convo-list" v-if="inboxType == 'personal'">
                    <div v-if="!personalConversations.length" class="convo-empty">
                        <div class="convo-empty-icon">✉️</div>
                        No personal conversations yet.
                    </div>
                    <router-link
                        v-for="conv in personalConversations"
                        :key="personalConvoKey(conv)"
                        :to="personalConvoTo(conv)"
                        @click="markAsRead(conv)"
                        class="convo-link"
                    >
                        <div class="convo-card" :class="{ 'convo-new' : conv.new }">
                            <div class="convo-avatar-combo">
                                <div class="convo-avatar-badge">
                                    <profile-pic :profile_pic="myMate.profile_pic" :avatar="myMate.avatar" :photo="myMate.photo" style="width:100%;height:100%;overflow:hidden;border-radius:50%;"/>
                                </div>
                                <div class="convo-avatar-primary" :class="{ 'convo-avatar-space': conv.type == 'space_mate' }">
                                    <img
                                        v-if="conv.type == 'space_mate'"
                                        :src="spaceAvatarSrc(conv.participants.receiver)"
                                        class="convo-avatar-img"
                                        @error="$event.target.src = '/img/coliving-app-logo-symbol.png'"
                                    >
                                    <profile-pic
                                        v-else
                                        :avatar="conv.participants.receiver.avatar"
                                        style="width:100%;height:100%;overflow:hidden;border-radius:50%;"
                                    />
                                </div>
                            </div>
                            <div class="convo-main">
                                <div class="convo-row">
                                    <div class="convo-name">
                                        <span v-if="conv.participants.receiver.name">{{ conv.participants.receiver.name }}</span>
                                        <span v-else-if="conv.participants.receiver.username">{{ conv.participants.receiver.username }}</span>
                                        <span v-else>{{ conv.type == 'space_mate' ? conv.user2 : conv.participants.receiver.uid }}</span>
                                    </div>
                                    <span class="convo-time">{{ formatDate(conv.updated_at) }}</span>
                                </div>
                                <div class="convo-sub">{{ conv.type == 'space_mate' ? 'Space' : 'Mate' }}</div>
                            </div>
                            <span v-if="conv.new" class="convo-dot"></span>
                        </div>
                    </router-link>
                </div>

                <div class="convo-list" v-else>
                    <div v-if="!spaceConversations.length" class="convo-empty">
                        <div class="convo-empty-icon">🏠</div>
                        No messages or join requests from mates yet.
                    </div>
                    <router-link
                        v-for="conv in spaceConversations"
                        :key="spaceConvoKey(conv)"
                        :to="'/inbox/space/' + conv.user2 + '/mate/' + conv.user1"
                        @click="markAsRead(conv)"
                        class="convo-link"
                    >
                        <div class="convo-card" :class="{ 'convo-new' : conv.new }">
                            <div class="convo-avatar-combo">
                                <div class="convo-avatar-badge convo-avatar-space">
                                    <img
                                        :src="spaceAvatarSrc(conv.participants.receiver)"
                                        @error="$event.target.src = '/img/coliving-app-logo-symbol.png'"
                                    >
                                </div>
                                <div class="convo-avatar-primary">
                                    <profile-pic :avatar="conv.participants.creator.avatar" style="width:100%;height:100%;overflow:hidden;border-radius:50%;"/>
                                </div>
                            </div>
                            <div class="convo-main">
                                <div class="convo-row">
                                    <div class="convo-name">
                                        <span v-if="conv.participants.creator.name">{{ conv.participants.creator.name }}</span>
                                        <span v-else-if="conv.participants.creator.username">{{ conv.participants.creator.username }}</span>
                                        <span v-else>{{ conv.participants.creator.uid }}</span>
                                    </div>
                                    <span class="convo-time">{{ formatDate(conv.updated_at) }}</span>
                                </div>
                                <div class="convo-sub">
                                    <span v-if="conv.participants.receiver.name">{{ conv.participants.receiver.name }}</span>
                                    <span v-else>{{ conv.user2 }}</span>
                                </div>
                            </div>
                            <span v-if="conv.new" class="convo-dot"></span>
                        </div>
                    </router-link>
                </div>
            </div>
            <div v-else class="spinner-container">
                <div class="spinner" style="margin-top:50px;"></div>
            </div>
        </div>
        <div v-else>
            <div style="height:20px;"></div>

            Log in to continue
            <div style="height:10px;"></div>
            <router-link to="/login"><button type="button" class="btn btn-md btn-primary">Login</button></router-link>

            <div style="height:30px;"></div>

            Create a free account
            <div style="height:10px;"></div>
            <router-link to="/register"><button type="button" class="btn btn-md btn-primary">Sign Up</button></router-link> 
        </div>
    </div>
</template>

<script setup>
import { ref, computed, inject, watch } from 'vue';
const inboxStatus = inject('inboxStatus')

const conversations = ref([]);
const conversations_mate_pov = ref([]);
let userLoggedIn = ref(window.userLoggedIn);
let loaded = ref(false);
let inboxType = ref('personal');

function isRenderable(conv, type) {
    if (type === 'personal') {
        return (
            (conv.type === 'space_mate' && conv.participants?.receiver) ||
            conv.participants?.receiver
        );
    }
    if (type === 'spaces') {
        return conv.participants?.creator && conv.participants?.receiver;
    }
    return false;
}

function markAsRead(conv) {
  if (conv.new) {
    conv.new = false
  }
}

function formatDate(date) {
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
}

function spaceAvatarSrc(participant) {
    if (participant?.first_photo) {
        return spacePhotoUrl(participant.first_photo);
    }
    return '/img/coliving-app-logo-symbol.png';
}

function personalConvoKey(conv) {
    return conv.uid || `${conv.type}-${conv.user1}-${conv.user2}`;
}

function spaceConvoKey(conv) {
    return conv.uid || `${conv.user1}-${conv.user2}`;
}

function personalConvoTo(conv) {
    if (conv.type === 'space_mate') {
        return '/inbox/space/' + conv.user2;
    }
    return '/inbox/mate/' + conv.participants.receiver.uid;
}

const personalConversations = computed(() => {
    return conversations_mate_pov.value.filter(conv => isRenderable(conv, 'personal'));
});

const spaceConversations = computed(() => {
    return conversations.value.filter(conv => isRenderable(conv, 'spaces'));
});

const hasNewPersonal = computed(() => {
    return conversations_mate_pov.value
        .filter(conv => isRenderable(conv, 'personal'))
        .some(conv => conv.new);
});

const hasNewSpaces = computed(() => {
    return conversations.value
        .filter(conv => isRenderable(conv, 'spaces'))
        .some(conv => conv.new);
});

watch(hasNewPersonal, (val) => {
  if (inboxStatus) inboxStatus.hasUnreadPersonal = val
})

watch(hasNewSpaces, (val) => {
  if (inboxStatus) inboxStatus.hasUnreadSpaces = val
})


import { useRoute } from 'vue-router';

const route = useRoute();
if (route.name === 'inboxMySpaces') {
    inboxType.value = 'spaces';
}

import { useRouter } from 'vue-router';
const router = useRouter();

function inboxTypeChange(type) {
    inboxType.value = type;
    if (type == 'spaces')
        router.push({ name: 'inboxMySpaces' });
    else
        router.push({ name: 'inbox' });
}


import ProfilePic from '../components/ProfilePic.vue';
import { spacePhotoUrl } from '../mediaUrls.js';

// "My own" avatar, used as the small badge on every conversation row
const myMate = window.mate || {};


if (userLoggedIn.value) {
    let userUid = window.mate.uid;

    const fetchConversations = async () => {
        try {
            const response = await fetch('/api/conversations');
            const data = await response.json();
            conversations.value = data.conversations;
            conversations_mate_pov.value = data.conversations_mate_pov;
            
            conversations.value.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
            conversations_mate_pov.value.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));

            for (let i = 0; i < conversations_mate_pov.value.length; i++) {
                let user = '';
                if (conversations_mate_pov.value[i].user1 == userUid) {
                    user = '1';
                }
                else {
                    user = '2';
                }
                
                let userCheckedDate = '';
                let updatedAtDate = new Date(conversations_mate_pov.value[i].updated_at);
                if (user == '1') {
                    userCheckedDate = new Date(conversations_mate_pov.value[i].user1_checked+'.000000Z');
                }
                else {
                    if (conversations_mate_pov.value[i].user2_checked == null) {
                        userCheckedDate = null;
                    }
                    else {
                        userCheckedDate = new Date(conversations_mate_pov.value[i].user2_checked+'.000000Z');
                    }
                }

                // Compare the dates
                if (!userCheckedDate) {
                    conversations_mate_pov.value[i].new = true;
                }
                else if (updatedAtDate > userCheckedDate) {
                    // console.log("updated_at is later than user1_checked");
                    conversations_mate_pov.value[i].new = true;
                } 
                else if (updatedAtDate < userCheckedDate) {
                    // console.log("updated_at is earlier than user1_checked");
                    conversations_mate_pov.value[i].new = false;
                }
            }

            for (let i = 0; i < conversations.value.length; i++) {
                let user = '2';
                
                let userCheckedDate = '';
                let updatedAtDate = new Date(conversations.value[i].updated_at);
                if (user == '1') {
                    userCheckedDate = new Date(conversations.value[i].user1_checked+'.000000Z');
                }
                else {
                    if (conversations.value[i].user2_checked == null) {
                        userCheckedDate = null;
                    }
                    else {
                        userCheckedDate = new Date(conversations.value[i].user2_checked+'.000000Z');
                    }
                }

                // Compare the dates
                if (!userCheckedDate) {
                    conversations.value[i].new = true;
                }
                else if (updatedAtDate > userCheckedDate) {
                    // console.log("updated_at is later than user1_checked");
                    conversations.value[i].new = true;
                } 
                else if (updatedAtDate < userCheckedDate) {
                    // console.log("updated_at is earlier than user1_checked");
                    conversations.value[i].new = false;
                }
            }

            loaded.value = true;
            // window.conversations = data.conversations;
        } catch (error) {
            console.error(error);
        }
    };
    fetchConversations();
    // if (window.conversations) {
    //     conversations.value = window.conversations;
    // } else {
    //     fetchConversations();
    // }
}
</script>

<style scoped>
/* TABS — same pattern as Hub */
.inbox-tabs {
  height: 40px;
  border-bottom: 1px solid #ccc;
  margin-bottom: 0;
  overflow: hidden;
}

.inbox-tabs-track {
  height: 40px;
}

.inbox-tabs .page-menu-button {
  border-radius: 5px;
  padding: 5px;
}

.inbox-tabs .page-menu-button-outer {
  position: relative;
  float: left;
  height: 40px;
  border-radius: 5px;
  margin-left: 20px;
}

.inbox-tabs .page-menu-button-outer:first-child {
  margin-left: 0;
}

.inbox-tabs .page-menu-button:hover,
.inbox-tabs .page-menu-button:active {
  cursor: pointer;
  background-color: rgb(232, 232, 232);
}

.inbox-tabs .page-menu-underline {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  height: 2px;
  background-color: #393939;
  margin-top: 0;
  visibility: hidden;
}

.inbox-tabs .page-menu-underline.active {
  visibility: visible;
}

.inbox-tabs .inbox-type-link {
  cursor: pointer;
  font-size: 15px;
  white-space: nowrap;
}

/* CONVO LIST */
.convo-list {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
    width: 100%;
}

.convo-empty {
    text-align: center;
    color: #999;
    padding: 50px 20px;
    background: var(--surface);
    border-radius: var(--radius-md);
    font-size: 14px;
}

.convo-empty-icon {
    font-size: 26px;
    margin-bottom: 8px;
}

.convo-link {
    display: block;
    color: inherit;
    text-decoration: none;
}

.convo-link:hover,
.convo-link:active {
    color: inherit;
}

.convo-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 14px;
    border: 1px solid #e8ebf0;
    background: var(--surface);
    box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
    transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease, background .15s ease;
}

.convo-card:hover {
    background: #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, .08), 0 10px 20px rgba(0, 0, 0, .06);
    transform: translateY(-1px);
    border-color: #e0e0e0;
}

.convo-card.convo-new {
    border-left: 3px solid var(--sky);
    background: #f8fbff;
}

/* AVATARS — small (you) on the left, big (them) on the right */
.convo-avatar-combo {
    display: flex;
    align-items: flex-end;
    flex-shrink: 0;
    width: 54px;
    height: 44px;
}

.convo-avatar-badge {
    position: relative;
    z-index: 2;
    width: 22px;
    height: 22px;
    margin-right: -10px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #fff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, .2);
    overflow: hidden;
}

.convo-avatar-badge img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.convo-avatar-primary {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    border-radius: 50%;
    overflow: hidden;
    background: #f2f2f2;
}

.convo-avatar-space {
    border-radius: 5px;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .14);
}

.convo-avatar-badge.convo-avatar-space {
    border-radius: 3px;
}

.convo-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* TEXT */
.convo-main {
    flex: 1;
    min-width: 0;
    text-align: left;
}

.convo-row {
    display: flex;
    align-items: baseline;
    gap: 10px;
}

.convo-name {
    flex: 1;
    min-width: 0;
    font-size: 14.5px;
    font-weight: 600;
    color: #222;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.convo-sub {
    font-size: 12.5px;
    color: #8f8f8f;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.convo-time {
    flex-shrink: 0;
    font-size: 11.5px;
    color: #a0a0a0;
    white-space: nowrap;
}

.convo-dot {
    flex-shrink: 0;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ff5a5f;
}
</style>
