<script setup>
import SocialLinks from '../components/SocialLinks.vue';
import ModalFeedback from '../components/ModalFeedback.vue';

import { useRoute } from 'vue-router';

import { ref, watch } from 'vue';

let forceFullFooter = ref(false);
let modalFeedback = ref(false);

function openFooter() {
    forceFullFooter.value = true;
    setTimeout(() => { window.scrollTo(0, document.body.scrollHeight); }, 100);
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

const route = useRoute();

watch(route, () => {
    forceFullFooter.value = false;
});
</script>

<template>
    <div>
        <div v-if="$route.name == 'Home'" >
            <social-links />

            <div style="height:10px;"></div>
            
            <div style="text-align:center;">
                <router-link to="/about">About</router-link>
                &nbsp;
                <router-link to="/terms">Terms</router-link>
                &nbsp;
                <router-link to="/privacy">Privacy</router-link>
                &nbsp;
                <router-link to="/contacts">Contacts</router-link>
            </div>

            <div style="height:8px;"></div>

            <div style="text-align:center;">
                Coliving App © 2025
                <br>
                v0.1-beta
                <br>
                <br>
                <br>
                <div style="max-width:800px;margin:0 auto;padding:20px;">
                    <div style="background-color: rgb(246, 246, 246);border: 1px solid rgb(204, 204, 204);padding:0 20px 15px 20px;">
                        <h1><i>Beta Version</i></h1>

                        <i>This is the first public beta release of the Coliving App. Thank you for being an early adopter! We apologize for any bugs and appreciate your <span style="font-weight:bold;color:rgb(0, 134, 218)!important;"  class="link-footer" @click="modalFeedback = true">feedback</span>.</i>
                        <div style="height:30px;"></div>
                    </div>
                </div>
                
                <!-- <br>
                <br>
                <span style="font-weight:bold;color:#999!important;"  class="link-footer" @click="modalFeedback = true">Feedback</span> -->
            </div>
            
            <div style="height:40px;"></div>
            
            <div class="bottom-arrow-div-2" @click="scrollToTop" style="text-align: center;">
                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"  xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                    viewBox="0 0 78.6 45.6" style="enable-background:new 0 0 78.6 45.6;width:22px;fill:#7e7e7e;transform: rotate(180deg);" xml:space="preserve">
                    <g>
                        <path style="fill:#888888;" d="M76.8,1.8L76.8,1.8c1.2,1.2,1.8,2.9,1.8,4.5c0,1.6-0.6,3.2-1.8,4.5l-33,33l0,0c-0.6,0.6-1.3,1-2,1.4
                        l0,0c-0.8,0.3-1.6,0.5-2.4,0.5c-0.8,0-1.6-0.2-2.4-0.5h0c-0.7-0.3-1.4-0.8-2-1.4l0,0l-33-33C0.6,9.5,0,7.9,0,6.3
                        c0-1.6,0.6-3.2,1.9-4.4l0,0l0,0C3.1,0.6,4.7,0,6.3,0c1.6,0,3.2,0.6,4.4,1.8h0l28.5,28.5L67.9,1.8l0,0C69.1,0.6,70.7,0,72.3,0
                        C73.9,0,75.5,0.6,76.8,1.8L76.8,1.8L76.8,1.8z"/>
                    </g>
                </svg>
            </div>
        </div>

        <!-- <div v-else-if="($route.name == 'HubChat' || $route.name == 'HubInfo' || $route.name == 'HubMates' || $route.name == 'HubSettings' || $route.name == 'ConversationMateSpace' || $route.name == 'ConversationMateMate' || $route.name == 'ConversationSpacePOV') && !forceFullFooter"> -->
        <div v-else-if="($route.name == 'ConversationMateSpace' || $route.name == 'ConversationMateMate' || $route.name == 'ConversationSpacePOV') && !forceFullFooter">    
            <!-- <div style="height:50px;"></div> -->
            <div class="footer site-footer">
                <div class="footer-content">
                    <div style="height:35px;font-size:26px;padding-top:5px;cursor:pointer;" @click="openFooter">⏏</div>
                </div>
            </div>
        </div>

        <div v-else>
            <div style="height:50px;clear:both;"></div>

            <div class="footer site-footer">

                <div class="footer-content">
                    <div class="footer-column" style="grid-area: col1;">
                        <!-- <span style="font-size: 19px; color: rgb(111, 111, 111);">Menu</span> -->

                        <router-link to="/spaces">Spaces</router-link>
                        <br>
                        <router-link to="/mates">Mates</router-link>
                        <br>
                        <router-link to="/inbox">Inbox</router-link>
                        <br>
                        <router-link to="/account">Account</router-link>
                        
                        <br>
                        <br>
                        <router-link to="/space/new">Add New Space</router-link>
                        <br>
                        <router-link to="/my-spaces">My Spaces</router-link>
                        <!-- <span class="copyright-desktop">
                            <br>
                            <br>
                            <span style="font-size:13px;color:#bbbbbb;">Coliving App © 2025</span>
                        </span> -->
                        
                        <br>
                        <br>
                        <router-link to="/">Home</router-link>
                    </div>
                    <div class="footer-column" style="grid-area: col2;">
                        <!-- <span style="font-size: 19px; color: rgb(111, 111, 111);">Menu</span> -->

                        <router-link to="/about">About</router-link>
                        <br>
                        <router-link to="/terms">Terms</router-link>
                        <br>
                        <router-link to="/privacy">Privacy</router-link>
                        <br>
                        <router-link to="/contacts">Contacts</router-link>
                        <br>
                        <br>
                        <span style="font-weight:bold;"  class="link-footer" @click="modalFeedback = true">Send Feedback</span>
                    </div>
                    <div class="footer-column" style="grid-area: col3;">
                        
                        <social-links style="text-align:left!important;" />

                        <div style="height:10px;"></div>

                        <span style="font-size:12px;color:#bbbbbb;">
                            Coliving App © 2025
                            <br>
                            v0.1-beta
                        </span>

                        <div class="footer-bottom-spacer"></div>
                    </div>
                    <div style="clear: both; height: 40px;"></div>
                </div>
            </div>
        </div>

        <div v-if="modalFeedback">
            <modal-feedback @close="modalFeedback = false" />
        </div>
    </div>
</template>

<style scoped>
#svg-twitter {
    fill: #848688;
    fill-rule: evenodd;
}

#svg-twitter:hover {
    fill: #adaeaf;
    fill-rule: evenodd;
}

.footer {
    background-color: #7b7b7b;
    left: 0;
    bottom: 0;
    width: 100%;
    overflow: hidden;
}

.copyright-mobile {
    display:none;
}

.spacer-footer {
  height:20px;
}

@media only screen and (max-width:480px) {
    .spacer-footer {
        height:10px;
    }
    .footer-content {
        padding-left: 10px;
    }
    .copyright-mobile {
        display:block;
    }
    .copyright-desktop {
        display:none;
    }
}

.footer-content {
    max-width: 1000px;
    padding: 0 10px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    grid-template-rows: auto;
    grid-column-gap: 40px;        
    grid-template-areas: "col1 col2 col3";
}

.footer-bottom-spacer {
    height:40px;
}

@media screen and (max-width:560px) {
    .footer-content {
        grid-template-columns: 1fr 1fr;
        grid-template-areas:
            "col1 col2"
            "col3 col3";
    }
    .footer-bottom-spacer {
        height:10px;
    }
}

.footer-column {
    margin-top: 45px;
    /* width: 212px;
    float: left; */
    line-height: 180%;
}

.footer {
    font-size: 14px;
    color: #e2e2e2;
    padding: 10px;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    padding-top: 0;
}

.footer a {
    font-size: 14px;
    color: #e2e2e2 !important;
    line-height: 22px;
    border-bottom: 0 !important;
}

.footer a:hover {
    text-decoration: underline !important;
}

.link-footer {
    font-size: 14px;
    color: #e2e2e2 !important;
    line-height: 22px;
    border-bottom: 0 !important;
    cursor: pointer;
}

.link-footer:hover {
    text-decoration: underline !important;
}

::placeholder {
    color: #cacaca;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 13px;
}
</style>