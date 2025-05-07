<template>
    <div class="content" >
        <h1>Inbox</h1>
        Inbox is where you'll find your personal conversations with spaces and mates. Additionally, if you are an admin of a space, you will also see here messages and join requests from mates sent to your spaces.
        <div style="height:10px;"></div>

        <div v-if="userLoggedIn">

            <div style="height:35px;"></div>
                
            <div style="height:40px;border-bottom:1px solid #ccc;" class="noselect">
                <div class="page-menu-button-outer">
                    <div class="page-menu-button" @click="inboxTypeChange('personal')">
                        <span class="inbox-type-link" :class="{active : inboxType == 'personal'}">
                            Personal ({{ conversations_mate_pov.length }})
                            <span v-if="hasNewPersonal" style="color:red;">●</span>
                        </span>
                    </div>
                    <div class="page-menu-underline" v-if="inboxType == 'personal'"></div>
                </div>
                <div class="page-menu-button-outer" style="margin-left:20px;">
                    <div class="page-menu-button" @click="inboxTypeChange('spaces')">
                        <span class="inbox-type-link" :class="{active : inboxType == 'spaces'}">
                            My Spaces ({{ conversations.length }})
                            <span v-if="hasNewSpaces" style="color:red;">●</span>
                        </span>
                    </div>
                    <div class="page-menu-underline" v-if="inboxType == 'spaces'"></div>
                </div>
            </div>

            <div v-if="loaded">
                <div style="height:30px;"></div>

                <div v-if="inboxType == 'personal'" v-for="conv in conversations_mate_pov">
                    <div style="line-height:200%;">
                        <!-- <router-link v-if="conv.type == 'space_mate' && conv.participants.receiver" :to="'/inbox/space/' + conv.user2"> -->
                        <router-link v-if="conv.type == 'space_mate' && conv.participants.receiver" :to="'/inbox/space/' + conv.user2" @click="markAsRead(conv)">

                            <div class="convo convo-space" :class="{ 'conv-new' : conv.new }" style="display:grid;grid-template-columns: 1fr 80px;">
                                <div>
                                    <icons :iconName="'space'" :width="11"></icons>&nbsp;
                                    <span v-if="conv.participants.receiver.name">{{ conv.participants.receiver.name }}</span>
                                    <span v-else-if="conv.participants.receiver.username">{{ conv.participants.receiver.username }}</span>
                                    <span v-else>{{ conv.participants.receiver.uid }}</span>
                                </div>
                                <div style="color:#333;font-size:11px;text-align:right;padding-right:2px;">
                                    {{
                                        new Date(conv.updated_at).toLocaleString('en-US', {
                                            month: 'short',   // "Jan", "Feb", etc.
                                            day: '2-digit',   // "01", "02", etc.
                                            hour: '2-digit',  // "01", "02", etc., 24-hour clock
                                            minute: '2-digit',// "01", "02", etc.
                                            hour12: false     // use 24-hour clock without AM/PM
                                        })
                                    }}
                                </div>
                            </div>
                        </router-link>
                        <!-- <router-link v-else-if="conv.participants.receiver" :to="'/inbox/mate/' + conv.participants.receiver.uid"> -->
                        <router-link v-else-if="conv.participants.receiver" :to="'/inbox/mate/' + conv.participants.receiver.uid" @click="markAsRead(conv)">

                            <div class="convo convo-mate" :class="{ 'conv-new' : conv.new }" style="display:grid;grid-template-columns: 1fr 80px;">
                                <div>
                                    <icons :iconName="'mate'" :width="11"></icons>&nbsp;
                                    <span v-if="conv.participants.receiver.name">{{ conv.participants.receiver.name }}</span>
                                    <span v-else-if="conv.participants.receiver.username">{{ conv.participants.receiver.username }}</span>
                                    <span v-else>{{ conv.participants.receiver.uid }}</span>
                                </div>
                                <div style="color:#333;font-size:11px;text-align:right;padding-right:2px;">
                                    {{
                                        new Date(conv.updated_at).toLocaleString('en-US', {
                                            month: 'short',   // "Jan", "Feb", etc.
                                            day: '2-digit',   // "01", "02", etc.
                                            hour: '2-digit',  // "01", "02", etc., 24-hour clock
                                            minute: '2-digit',// "01", "02", etc.
                                            hour12: false     // use 24-hour clock without AM/PM
                                        })
                                    }}
                                </div>
                            </div>
                        </router-link>
                    </div>
                </div>

                <div v-else v-for="conv in conversations">
                    <div style="line-height:200%;">
                        <!-- <router-link :to="'/inbox/space/' + conv.user2 + '/mate/' + conv.user1"> -->
                        <router-link :to="'/inbox/space/' + conv.user2 + '/mate/' + conv.user1" @click="markAsRead(conv)">

                            <div v-if="conv.participants.creator && conv.participants.receiver" class="convo convo-mate-space" :class="{ 'conv-new' : conv.new }" style="display:grid;grid-template-columns: 1fr 80px;">
                                <div>
                                    <icons :iconName="'space'" :width="11"></icons>&nbsp;
                                    <span v-if="conv.participants.receiver.name">{{ conv.participants.receiver.name }}</span>
                                    <span v-else-if="conv.participants.receiver.username">{{ conv.participants.receiver.username }}</span>
                                    <span v-else>{{ conv.participants.receiver.uid }}</span>  
                                    &nbsp;←&nbsp;<icons :iconName="'mate'" :width="11"></icons>&nbsp;
                                    <span v-if="conv.participants.creator.name">{{ conv.participants.creator.name }}</span>
                                    <span v-else-if="conv.participants.creator.username">{{ conv.participants.creator.username }}</span>
                                    <span v-else>{{ conv.participants.creator.uid }}</span>
                                </div>
                                <div style="color:#333;font-size:11px;text-align:right;padding-right:2px;">
                                    {{
                                        new Date(conv.updated_at).toLocaleString('en-US', {
                                            month: 'short',   // "Jan", "Feb", etc.
                                            day: '2-digit',   // "01", "02", etc.
                                            hour: '2-digit',  // "01", "02", etc., 24-hour clock
                                            minute: '2-digit',// "01", "02", etc.
                                            hour12: false     // use 24-hour clock without AM/PM
                                        })
                                    }}
                                </div>
                            </div>
                        </router-link>
                    </div>
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
            <router-link to="/login"><button class="auth-button">Login</button></router-link>

            <div style="height:30px;"></div>

            Create a free account
            <div style="height:10px;"></div>
            <router-link to="/register"><button class="auth-button">Sign Up</button></router-link> 
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


import Icons from '../components/Icons.vue';


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

<style>
.convo {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
    width: 100%;
    margin-bottom: 10px;
    padding-left:10px;
}

.convo-mate {
    background-color: azure;
}

.convo-space {
    background-color: rgb(237, 237, 237);
}

.convo:hover {
    border: 1px solid #444;
}

.convo-mate-space {
    background: linear-gradient(to right, azure, rgb(237, 237, 237));
}

.active {
    /* font-weight: bold; */
}

.inbox-type-link {
    cursor: pointer;
    font-size:15px;
}

.conv-new {
    /* outline: 2px solid rgb(255, 125, 125); */
    outline: 2px solid rgb(112, 112, 112);
}
</style>