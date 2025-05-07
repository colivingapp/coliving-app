<template>
    <div class="content" >
        
        <h1>Coliving Mates</h1>

        <div>
            <div>
                <div class="my-spaces-popup">
                    <h2 style="margin-top:0px;">My Profile</h2>
                    You can edit your profile visibility and other options in your profile settings

                    <div style="height:10px;"></div>

                    <router-link v-if="userLoggedIn" to="/mate/update"><button class="button">Edit My Profile</button></router-link>
                    <router-link v-else to="/register"><button class="button">Edit My Profile</button></router-link>

                    <div style="height:10px;"></div>
                </div>

                <div style="max-width:620px;float:left;">

                    <h2 class="mobile-only-h2">Coliving Mates</h2>
                    Here you will find a list of coliving mates. To be visible in this list, your profile visibility needs to be set to public.
                    
                    <div style="height:30px;"></div>

                    <div v-if="isLoading">
                        Loading...
                    </div>
                    <div v-for="mate in mates" :key="mate.id" >
                        <div v-if="mate.status != 'left'">
                            <router-link :to="'/mate/'+ (mate.username ? mate.username : mate.uid)" style="display:inline-grid;grid-template-columns:25px 1fr;height:25px;margin-bottom:12px;">
                                <profile-pic :profile_pic="mate.profile_pic" :avatar="mate.avatar" :photo="mate.photo" style="height:25px;width:25px;overflow:hidden;"/>
                                <div style="padding-left:5px;font-size:18px;">{{ mate.displayName }}</div>
                            </router-link>
                        </div>
                    </div>

                    <div style="height:30px;display:block;"></div>
                </div>
            </div>
        </div>
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
    let view = ref('mates');

    let space_uid = route.params.uid;

    let isMember = ref('loading');
    let mates = ref('');

    let isLoading = ref(true);

    function leaveSpace() {
        if (confirm('Are you sure you want to leave this space?')) {
            axios.post('/api/space/'+route.params.uid+'/leave')
            .then(resp => { console.log(resp); spaceFetch(); })
            .catch(err => console.log(err))
        }
    }

    function spaceFetch() {
        axios.get('/api/mates')
        .then((result) => {
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
                // console.log(mate.name);
                // console.log(mate.username);
                // console.log(mate.user_id)
            })
            isLoading.value = false;
        })
        .catch(function(error) {
            console.log(error);
        });
    }

    spaceFetch();
</script>

<style>
.square-button {
    width:30px;height:30px;background:rgb(227, 227, 227);border:1px solid rgb(190, 190, 190);float:left;text-align:center;padding:5px;border-radius:5px;cursor:pointer;
}

.btn-active {background-color: rgb(232, 232, 232);}

.mobile-menu {display:none;}
.desktop-menu {float:left;width:100%;max-width:200px;padding-right:40px;}
.content-hub {float:right;width:100%;max-width:760px;}
.height100 { height: 100%; }

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