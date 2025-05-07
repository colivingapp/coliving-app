<template>
    <div v-if="userLoggedIn && isMember == 1" style="height:100%;">
        <div class="content height100" style="margin-top:0px;padding-top:30px;">
            
            <div v-if="space" style="width:100%;height:30px;border-radius:3px;background:#f1f1f1;margin-bottom:20px;margin-top:-20px;padding:5px;" class="mobile-menu">
                <router-link to="/hub">Hub</router-link> > <router-link :to="'/space/'+space_uid">{{ space.name }}</router-link>
            </div>

            <!-- <div class="mobile-menu overflow-dots" style="margin-top:-15px;margin-bottom:10px;">
                <b>HUB:</b>&nbsp;&nbsp;<router-link v-if="space" :to="'/space/'+$route.params.uid" target="_blank">{{ space.name }}</router-link>
            </div> -->

            <div style="height:47px;margin-bottom:20px;border:1px solid #ccc;border-radius:5px;padding-top:6px;background-color:#f6f6f6;" class="mobile-menu">

                <router-link :to="'/space/'+$route.params.uid+'/hub'">
                    <div class="page-menu-button-outer" style="margin-left:20px;">
                        <div class="page-menu-button" @click="$route.name == 'HubInfo'"><span class="inbox-type-link" :class="{active : $route.name == 'HubInfo'}">Info</span></div>
                        <div class="page-menu-underline" v-if="$route.name == 'HubInfo'"></div>
                    </div>
                </router-link>

                <router-link :to="'/space/'+$route.params.uid+'/hub/mates'">
                    <div class="page-menu-button-outer" style="margin-left:20px;">
                        <div class="page-menu-button" @click="$route.name == 'HubMates'"><span class="inbox-type-link" :class="{active : $route.name == 'HubMates'}">Mates</span></div>
                        <div class="page-menu-underline" v-if="$route.name == 'HubMates'"></div>
                    </div>
                </router-link>

                <!-- <router-link :to="'/space/'+$route.params.uid+'/hub/voting'">
                    <div class="page-menu-button-outer" style="margin-left:20px;">
                        <div class="page-menu-button" @click="$route.name == 'HubVoting'"><span class="inbox-type-link" :class="{active : $route.name == 'HubVoting'}">Voting</span></div>
                        <div class="page-menu-underline" v-if="$route.name == 'HubVoting'"></div>
                    </div>
                </router-link> -->

                <router-link :to="'/space/'+$route.params.uid+'/hub/chat'">
                    <div class="page-menu-button-outer" style="margin-left:20px;">
                        <div class="page-menu-button" @click="$route.name == 'HubChat'"><span class="inbox-type-link" :class="{active : $route.name == 'HubChat'}">Chat</span></div>
                        <div class="page-menu-underline" v-if="$route.name == 'HubChat'"></div>
                    </div>
                </router-link>

                <router-link :to="'/space/'+$route.params.uid+'/hub/settings'">
                    <div class="page-menu-button-outer" style="margin-left:20px;">
                        <div class="page-menu-button" @click="$route.name == 'HubSettings'"><span class="inbox-type-link" :class="{active : $route.name == 'HubSettings'}">Settings</span></div>
                        <div class="page-menu-underline" v-if="$route.name == 'HubSettings'"></div>
                    </div>
                </router-link>

            </div>

            <div class="desktop-menu">
                <!-- <router-link :to="'/space/'+$route.params.uid" target="_blank">{{ $route.params.uid }}</router-link> -->
                <!-- <div style="display:grid;grid-template-columns:20px 10px 1fr;margin-bottom:20px;">
                    <div>
                        <img src="/img/hub.png" width="20">
                    </div>
                    <div></div>
                    <div style="padding-top:2px;">
                        <router-link v-if="space" :to="'/space/'+$route.params.uid" target="_blank">{{ space.name }}</router-link>
                    </div>
                </div> -->

                <!-- <div style="padding-left:5px;">
                    <div  style="display:flex;align-items: center;margin-bottom:10px;">
                        <img src="/img/hub.png" width="30">&nbsp;&nbsp;&nbsp;<span style="font-weight:bold;font-size:15px;">HUB</span>
                    </div>

                    <div class="overflow-dots"><router-link v-if="space" :to="'/space/'+$route.params.uid" target="_blank">{{ space.name }}</router-link></div>
                </div> -->


                <!-- <div>
                    <img src="/img/hub.png" width="20">
                    <div style="height:5px;"></div>
                    <router-link v-if="space" :to="'/space/'+$route.params.uid" target="_blank">{{ space.name }}</router-link>
                </div> -->
                
                <h1 style="padding-left:5px;margin-top:-18px;margin-bottom:15px;">HUB</h1>
                
                <router-link :to="'/space/'+$route.params.uid+'/hub'">
                    <div class="page-menu-button" @click="$route.name == 'HubInfo'" :class="{'btn-active' : $route.name == 'HubInfo'}"><span class="inbox-type-link" :class="{active : $route.name == 'HubInfo'}">Info</span></div>
                </router-link>

                <router-link :to="'/space/'+$route.params.uid+'/hub/mates'">
                    <div class="page-menu-button" @click="$route.name == 'HubMates'" :class="{'btn-active' : $route.name == 'HubMates'}"><span class="inbox-type-link" :class="{active : $route.name == 'HubMates'}">Mates</span></div>
                </router-link>

                <!-- <router-link :to="'/space/'+$route.params.uid+'/hub/voting'">
                    <div class="page-menu-button" @click="$route.name == 'HubVoting'"  :class="{'btn-active' : $route.name == 'HubVoting'}"><span class="inbox-type-link">Voting</span></div>
                </router-link> -->

                <router-link :to="'/space/'+$route.params.uid+'/hub/chat'">
                    <div class="page-menu-button" @click="$route.name == 'HubChat'" :class="{'btn-active' : $route.name == 'HubChat'}"><span class="inbox-type-link" >Chat</span></div>
                </router-link>
                
                <router-link :to="'/space/'+$route.params.uid+'/hub/settings'">
                    <div class="page-menu-button" @click="$route.name == 'HubSettings'" :class="{'btn-active' : $route.name == 'HubSettings'}"><span class="inbox-type-link" :class="{active : $route.name == 'HubSettings'}">Settings</span></div>
                </router-link>

            
                <div style="height:40px;"></div>

                
                <!-- <div style="display:grid;grid-template-columns:30px 10px 1fr;padding-left:5px;">
                    <div title="Hub">
                        <img src="/img/hub.png" width="30">
                    </div>
                    <div></div>
                    <div style="padding-top:7px;"><router-link v-if="space" :to="'/space/'+$route.params.uid" target="_blank">{{ space.name }}</router-link></div>
                </div> -->

                <div style="padding-left:5px;">
                    <b>Space</b>
                    <div style="padding-top:5px;"><router-link v-if="space" :to="'/space/'+$route.params.uid">{{ space.name }}</router-link></div>
                </div>

            </div>

            <div class="content-hub" style="height:100%;">

                <div v-if="route.name == 'HubChat'" style="height:100%;">
                    <h2 style="margin-top:0px;">Chat</h2>
                    <chat :mates="mates" />
                </div>

                <div v-if="route.name == 'HubMates'">
                    <h2 style="margin-top:0px;">Mates</h2>

                    <div v-for="mate in mates" :key="mate.id" >
                        <div v-if="mate.status != 'left'">
                            <router-link :to="'/mate/'+ (mate.username ? mate.username : mate.uid)" style="display:grid;grid-template-columns:25px 1fr;height:25px;margin-bottom:10px;">
                                <profile-pic :profile_pic="mate.profile_pic" :avatar="mate.avatar" :photo="mate.photo" style="height:25px;width:25px;overflow:hidden;"/>
                                <div style="padding-left:5px;font-size:18px;">{{ mate.displayName }}</div>
                            </router-link>
                        </div>
                    </div>

                    <div style="height:30px;display:block;"></div>

                    <!-- <div style="height:20px;"></div>
                    <h2>Join Requests</h2>

                    When there is a new join request to this space, you will see it here. You will be able to vote to accept new mates, or not. -->
                </div>

                <div v-if="route.name == 'HubInfo'">
                    
                    <h2 style="margin-top:0px;">Info</h2>

                    Here's some helpful information about your space, kindly provided by your host.

                    <div style="height:20px;"></div>

                    <div style="padding:20px;background:aliceblue;border:1px solid lightgray;">
                        <span v-if="space.info"  style="white-space: pre-line">{{ space.info }}</span>
                        <i v-else>No information available</i>
                    </div>

                    <div style="height:50px;display:inline-table;"></div>

                </div>

                <div v-if="route.name == 'HubSettings'" style="height:100%;">
                    <h2 style="margin-top:0px;">Settings</h2>
                    <button class="button-function" @click="leaveSpace">Leave this space</button>
                    <div style="height:20px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div v-if="isMember == 0" style="text-align:center;">
        <div style="height:50px;"></div>
        You are not a member of this space.
        <br><br>
        <router-link :to="'/inbox/space/'+space_uid"><button type="button" class="button">Join Request</button></router-link>
    </div>
    
    <div v-else-if="!userLoggedIn && isMember != 'loading'" >
        <div style="height:30px;"></div>
        <router-link to="/login">Log in</router-link>
    </div>
</template>

<script setup>
    import Chat from '../hub_chat/Main.vue';

    import ProfilePic from '../components/ProfilePic.vue';

    import Icons from '../components/Icons.vue';

    import { ref } from 'vue';
    import { useRoute } from 'vue-router';
    const route = useRoute();

    let userLoggedIn = ref(window.userLoggedIn);
    let spaceMates = ref(null);
    let space = ref(null);
    let view = ref('chat');

    let space_uid = route.params.uid;

    let isMember = ref('loading');
    let mates = ref('');

    function leaveSpace() {
        if (confirm('Are you sure you want to leave this space?')) {
            axios.post('/api/space/'+route.params.uid+'/leave')
            .then(resp => { console.log(resp); spaceFetch(); })
            .catch(err => console.log(err))
        }
    }

    function spaceFetch() {
        axios.get('/api/space/'+route.params.uid+'/space-data')
        .then((result) => {
            if (result.data == 'unauthenticated') {
                isMember.value = 'unauthenticated';
                console.log("Unauthenticated.");
            }
            else if (result.data == 'not_a_member') {
                isMember.value = false;
                console.log("Not a member.");
            }
            else {
                isMember.value = true;
                mates.value = result.data[0];
                mates.value.forEach(mate => {
                    if (mate.name != '') {
                        mate.displayName = mate.name;
                    }
                    else if (mate.username != '') {
                        mate.displayName = mate.username;
                    }
                    else {
                        mate.displayName = mate.uid;
                    }
                })
                    
            }
        })
        .catch(function(error) {
            console.log(error);
        });
    }
    
    if (userLoggedIn.value) {
        axios.get('/api/space/'+route.params.uid+'/hub')
        .then(resp => { spaceMates.value = resp.data.mates; space.value = resp.data.space; })
        .catch(err => console.log(err))

        spaceFetch();
    }
</script>

<style>
.square-button {
    width:30px;
    height:30px;
    background:rgb(227, 227, 227);
    border:1px solid rgb(190, 190, 190);
    float:left;
    text-align:center;
    padding:5px;
    border-radius:5px;
    cursor:pointer;
}

.btn-active {background-color: rgb(232, 232, 232);}

.mobile-menu {
    display:none;
}

.desktop-menu {
    float:left;
    width:100%;
    max-width:200px;
    padding-right:40px;
}

.content-hub {
    float:right;
    width:100%;
    max-width:760px;
}


.height100 {
    height: 100%;
}

/* @media screen and (max-width: 600px) {
    .height100 {height:calc(100% - 77px);}
} */

@media screen and (max-width: 1000px) {
    .mobile-menu {display:block;}
    .desktop-menu {display:none;}
    .height100 {height:calc(100% - 52px);}
    .height100b {height:calc(100% - 32px);}
    .content-hub {
        float: left;
        margin-top:10px;
    }
}
</style>