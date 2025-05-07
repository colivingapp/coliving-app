<template >
    <div  class="header">
        <div class="header-inner">

            <div class="container-header relative" >
                
                <div  class="grid-item">
                    <!-- LEFT SIDE -->
                    <router-link to="/" class="logo-grid" @click="resetMap()">
                        <div>
                            <img src="/img/coliving-app-logo-symbol.png" width="38">
                        </div>
                        <div class="logo-text" :class="{'logo-text-mobile':$route.name != 'Home'}" >
                            <!-- <span v-if="$route.name == 'Home'"> -->
                                Coliving App
                            <!-- </span> -->
                        </div>
                        <div style="position:absolute;color:#939393;font-size:11px;padding-left:133px;" :class="{'logo-text-mobile':$route.name != 'Home'}">
                        <!-- <div style="position:absolute;color:red;font-size:11px;padding-left:133px;" v-if="$route.name == 'Home'"> -->
                          Beta
                        </div>
                    </router-link>
                </div>

                <div  class="grid-item">
                    <div v-if="$route.name !== 'Home'">
                        <div class="search-input" style="margin-top:2px;text-align:center;">
                            <input v-model="searchQuery" placeholder="Search..." 
                                @input="showResults = searchQuery !== ''" 
                                @blur="onBlur" 
                                @focus="fetchSpaces"
                                class="input-search" />
                            <div v-if="showResults && spacesFiltered.length > 0" class="dropdown">
                              <router-link v-for="space in spacesFiltered.slice(0, 3)" :to="'/space/'+space.uid">
                                  <div class="dropdown-item">
                                  {{ space.name }}, {{ space.country }}
                                  </div>
                              </router-link>
                              <div v-if="spacesFiltered.length > 0" class="dropdown-item link" style="text-align:center;" @click="showAll">All...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE -->
                <div style="text-align:right;" class="prevent-select grid-item ">

                  <jet-dropdown style="width:38px;float:right;font-size:16px!important;" >
                      <template #trigger >
                          <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out acc-btn" style="padding:0px;margin-top:-1px;">
                              <profile-pic v-if="mate" :profile_pic="mate.profile_pic" :avatar="mate.avatar" :photo="mate.photo" style="width:35px;height:35px;overflow:hidden;"/>
                              <div v-else class="rounded-full object-cover buttons-hover"  style="width:35px;height:35px;">
                                  <svg version="1.1" viewBox="0 0 25.314 25.248" xmlns="http://www.w3.org/2000/svg">
                                      <path class="svg-button" style="fill:#a3a3a3;" d="m3.7034 3.6939c2.3722-2.3661 5.5952-3.6939 8.9483-3.6939 6.9965 0 12.662 5.6507 12.662 12.619 0 4.3728-2.2321 8.2264-5.6152 10.493h-0.01001c-1.2412-0.88853-2.6725-1.4576-4.1539-1.797-0.57053-0.12979-1.1511-0.22962-1.7316-0.27954v-1.5974c3.3131-0.54909 5.8354-3.4144 5.8354-6.8687 0-3.8536-3.1229-6.9685-6.9865-6.9685-3.8536 0-6.9765 3.1149-6.9765 6.9685 0 3.4543 2.5223 6.3196 5.8354 6.8687v1.5974c-0.58054 0.04992-1.1611 0.13977-1.7316 0.26956-1.4914 0.34942-2.9027 0.95842-4.1639 1.807-0.68063-0.45924-1.3212-0.97838-1.9118-1.5674-2.3722-2.3661-3.7034-5.5708-3.7034-8.9253 0-3.3445 1.3312-6.5592 3.7034-8.9153zm9.0784 21.554h-0.13012-0.16015 0.17016 0.12011z" />
                                  </svg>
                                  <!-- <img src="/img/logo-animated.gif?v=003" title="Account"> -->
                              </div>
                          </button>
                      </template>

                      <template #content>
                          <!-- Account Management -->
                          <div class="block px-4 py-2 text-xs text-gray-400">
                              Account
                          </div>

                          <span v-if="userLoggedIn">

                              <router-link to="/mate/update" class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">Profile</router-link>

                              <router-link to="/bookmarks" class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">Bookmarks</router-link>

                              <router-link to="/my-spaces" class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">My Spaces</router-link>

                              <router-link to="/account/settings" class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">Settings</router-link>
                                                          
                              <br>
                              <!-- Authentication -->
                              <router-link class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out" to="#" @click.prevent="logOut">
                                    Log Out
                              </router-link>
                          </span>

                          <span v-else>
                              <router-link to="/register" class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">Register</router-link>

                              <router-link to="/login" class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">Login</router-link>
                          </span>
                      </template>
                  </jet-dropdown>


                  <div class="menu" >
                      <router-link to="/spaces">Spaces</router-link>
                      &nbsp;
                      <router-link to="/mates">Mates</router-link>
                      &nbsp;
                      <!-- <router-link to="/inbox">Inbox</router-link> -->
                      <router-link to="/inbox">
                        <!-- <span v-if="mate && (mate.hasUnreadPersonal || mate.hasUnreadSpaces)" style="color:red;">●</span> Inbox -->
                        <span v-if="inboxStatus.hasUnreadPersonal || inboxStatus.hasUnreadSpaces" style="color:red;">●</span> Inbox
                      </router-link>
                    </div>


                  <JetDropdown class="mobile-only"  @menu-toggle="isMenuOpen = $event" style="font-size:16px!important;">
                    
                    <template #trigger >
                      <!-- <div style="width:50px;height:50px;float:left;"> -->
                      <div style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; margin-top:-4px;">
                        <svg xmlns="http://www.w3.org/2000/svg" v-if="!isMenuOpen" xml:space="preserve" style="height:29px;width:29px;display:inline-block;cursor:pointer;pointer-events:none;"  width="100%" height="100%" version="1.1" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd"
                        viewBox="0 0 3.067 2.4718">
                            <metadata id="CorelCorpID_0Corel-Layer"/>
                            <rect fill="#777" x="-0" y="-0" width="3.067" height="0.4264" rx="0.2665" ry="0.2132"/>
                            <rect fill="#777" x="-0" y="1.0227" width="3.067" height="0.4264" rx="0.2665" ry="0.2132"/>
                            <rect fill="#777" x="-0" y="2.0454" width="3.067" height="0.4264" rx="0.2665" ry="0.2132"/>
                        </svg>

                        <svg xmlns="http://www.w3.org/2000/svg"  v-else style="height:27px;width:27px;display:inline-block;cursor:pointer;pointer-events:none;" xml:space="preserve" width="100%" height="100%" version="1.1" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd"
                        viewBox="0 0 63.3813 63.3812"
                        xmlns:xlink="http://www.w3.org/1999/xlink"
                        xmlns:xodm="http://www.corel.com/coreldraw/odm/2003">
                        <g id="Layer_x0020_1">
                          <metadata id="CorelCorpID_0Corel-Layer"/>
                          <g id="_3211222540640">
                          <path fill="#777777" d="M10.5693 2.4415l50.3705 50.3705c2.8051,2.8051 3.26,6.8998 1.016,9.1438l0 0c-2.2441,2.2441 -6.3388,1.7891 -9.1438,-1.016l-50.3705 -50.3705c-2.805,-2.805 -3.26,-6.8997 -1.0159,-9.1438l-0 -0c2.2441,-2.244 6.3388,-1.7891 9.1438,1.016z"/>
                          <path fill="#777777" d="M52.812 2.4415l-50.3705 50.3705c-2.8051,2.8051 -3.26,6.8998 -1.016,9.1438l0 0c2.2441,2.2441 6.3388,1.7891 9.1438,-1.016l50.3705 -50.3705c2.805,-2.805 3.26,-6.8997 1.0159,-9.1438l0 -0c-2.2441,-2.244 -6.3388,-1.7891 -9.1438,1.016z"/>
                          </g>
                        </g>
                        </svg>

                        <!-- <input id="menu-toggle" type="checkbox" v-model="isMenuOpen" />
                        <label class='menu-button-container' for="menu-toggle">
                          <div class='menu-button'></div>
                        </label> -->
                      </div>
                    </template>
                    
                    <template #content >
                      <div class="block px-4 py-2 text-xs text-gray-400" >
                          Menu
                        </div>

                        <router-link to="/spaces" class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">Spaces</router-link>
                        <router-link to="/mates" class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">Mates</router-link>
                        <router-link to="/inbox" class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">Inbox <span v-if="mate && (mate.hasUnreadPersonal || mate.hasUnreadSpaces)" style="color:red;">●</span>
                        </router-link>
      
                    </template>

                  </JetDropdown>

                </div>
            
            </div>

        </div>
    </div>
</template>

<script setup>

import ProfilePic from '../components/ProfilePic.vue';

import JetDropdown from './Dropdown.vue'

import { ref, computed, watch, inject } from 'vue';

const inboxStatus = inject('inboxStatus')

import { useRouter } from 'vue-router';
const router = useRouter();

const caUser = window.caUser;
let userLoggedIn = window.userLoggedIn;

let isMenuOpen = ref(false);

let isAccountDropdownOpen = ref(false);
let isMenuDropdownOpen = ref(false);


const searchQuery = ref('');
const showResults = ref(false);

const spaces = ref(window.spaces || []);

// if (window.mate) {
  const mate = window.mate;

  const showInboxDot = computed(() => {
    return mate?.hasUnreadPersonal || mate?.hasUnreadSpaces;
  });
// }
// let mate = ref(null);
// setTimeout(() => {
//   console.log([window.mate])
//   mate.value = window.mate;
// }, 2000)


// if (!window.spaces) {
//   async function fetchSpaces() {
//     try {
//       const response = await axios.get('/api');
//       // console.log(response.data.spaces)
//       // spaces.value = response.data[0] || [];
//       // console.log(spaces.value)
//       spaces.value = response.data.spaces;
//     } catch (error) {
//       console.error(error);
//     }
//   }
//   fetchSpaces();
// }

  async function fetchSpaces() {
    try {
      const response = await axios.get('/api');
      // console.log(response.data.spaces)
      // spaces.value = response.data[0] || [];
      // console.log(spaces.value)
      spaces.value = response.data.spaces;
    } catch (error) {
      console.error(error);
    }
  }
  // fetchSpaces();

function logOut() {
  axios.post('/logout')
  .then(response => {
      window.location = '/';
  })
}

const spacesFiltered = computed(() => {
  if (searchQuery.value && spaces.value) {
    return spaces.value.filter(space => {
      const { country, name, formatted_address } = space;
      const searchValue = searchQuery.value.toLowerCase();

      return (
        country.toLowerCase().includes(searchValue) ||
        name.toLowerCase().includes(searchValue) || 
        formatted_address.toLowerCase().includes(searchValue)
      );
    });
  } else {
    return spaces.value || [];
  }
});


watch(spacesFiltered);


function slugify(text) {
    // replace non letter or digits by -
    text = text.replace(/[^a-zA-Z0-9]+/g, '-');

    // transliterate
    text = text
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/([^\u0000-\u007F]+)/g, '')
        .replace(/[^\w-]+/g, '');

    // trim
    text = text.trim('-');

    // remove duplicated - symbols
    text = text.replace(/-+/g, '-');

    // lowercase
    text = text.toLowerCase();

    if (text === '') {
        return 'n-a';
    }

    return text;
  }


function onBlur() {
  setTimeout(() => { showResults.value = false; }, 150);
}

function resetMap() {
  if(vueVM) {
    vueVM.resetMap();
  }
}

function showAll() {
  router.push('/search/'+searchQuery.value);
}
</script>

<style scoped>
.logo-text {
  padding-left:8px;padding-top:8px;color:rgb(97 97 97);font-weight:bold;font-size:15px;
}

.logo-grid {
  color:black;text-decoration:none;z-index:99;display:grid;grid-template-columns:38px 98px;width:136px;
}

/* .header {
    position:sticky;
    z-index: 10;
    width: 100%;
    height:60px;
    top:0px;
    left: 0px;
    padding-top: 10px;
    background: white;
} */

.account {
    background-color:#F3F4F6;
    box-shadow: 0 1px 6px 0 rgb(0 0 0 / 7%), 0 1px 1px 0 rgb(0 0 0 / 5%);
}

.absolute {
    position: absolute;
}

/* .header-inner {
    max-width:1000px;
    margin:0 auto;
    padding:0 10px 0 10px;
}

@media only screen and (max-width: 600px) {
    .header-inner {
        padding:0 5px 0 5px;
    }
    .home {
        margin-top:0px;
    }
}

.container-header {
    max-width: 1000px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    pointer-events: auto;
} */

.search-input {
    margin: auto; 
    max-width: 280px;
    padding-top:3px;
}

.header {
  position: sticky;
  z-index: 10;
  width: 100%;
  height: 60px;
  top: 0px;
  left: 0px;
  padding-top: 10px;
  background: white;
}

.header-inner {
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 10px 0 10px;
}

.container-header {
  max-width: 1000px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  pointer-events: auto;
  /* grid-template-areas: 
    "item1 item2 item3"
    "item1 item2 item3"; */
}

.grid-item {
  /* display: flex; */
  justify-content: center;
}

/* .grid-item:nth-child(1) {
  grid-area: item1;
}

.grid-item:nth-child(2) {
  grid-area: item2;
}

.grid-item:nth-child(3) {
  grid-area: item3;
} */

@media only screen and (max-width: 700px) {
    
  /* .grid-item {
    display: flex;
  } */
    .header-inner {
    padding: 0 10px 0 10px;
  }

  .container-header {
      grid-template-columns: 48px 1fr 100px;
  }

  /* .container-header {
    grid-template-columns: auto 1fr;
    grid-template-areas: 
      "item1 item3"
      "item2 item2";
  } */

  .search-input {
    padding-top: 3px;
  }

  .logo-text-mobile {
    display: none;
  }

  .logo-text {
    position:absolute;
    padding-left: 45px;
  }

  .logo-grid {
    grid-template-columns:38px;
  }
}


input {
  border: 1px solid #87c9eb;
  border-radius: 50px;
  padding: 5px 10px;
  font-size: 14px;
  box-sizing: border-box;
}

.dropdown {
  border: 1px solid #ddd;
  max-height: 200px;
  overflow-y: auto;
  position: absolute;
  width: 340px;
  background: white;
  z-index: 1000;
  margin-top: 5px;
  border-radius: 10px;
  font-size: 14px;
  padding: 5px;
}

.dropdown a {
  text-decoration: none;
}

.dropdown-item {
  padding: 5px;
  cursor: pointer;
  text-align: left;
  font-size: 18px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-item:hover {
  background-color: #87c9eb;
  color: white;
}

.more-results {
  text-align: center;
  padding: 5px;
  height: 41px;
  font-size: 22px;
  margin-top: -11px;
}


/* .sticky-header-account {
    background-color:#f1f1f1;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    height: 63px;
} */


/* .header-logo {
    display: grid;
    grid-template-columns: 50px 150px;
} */

 
/* CSS HAMBURGER */
/* https://codepen.io/alvarotrigo/pen/MWEJEWG */

.top-nav {
  display: flex;
  flex-direction: row;
  align-items: center;

  /* justify-content: space-between; */

  /* background-color: #00BAF0; */
  /* background: linear-gradient(to left, #f46b45, #eea849); */
  /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
  color: #FFF;
  /* height: 50px; */
  /* padding: 1em; */
}

.menu {
  text-align: right;
  max-width:165px;float:right;margin-right:15px;
  margin-top:10px;
}

.menu-button-container {
  display: none;
  /* height: 40px;
  width: 40px; */
  cursor: pointer;
  flex-direction: column;
  /* justify-content: center; */
  /* align-items: center; */
  float:right;
  /* margin-top: 18px;
  margin-right:10px; */
  margin-top: 17px;
  margin-right:30px;
}

#menu-toggle {
  display: none;
}

.menu-button,
.menu-button::before,
.menu-button::after {
  display: block;
  background-color: #5e5e5e;
  position: absolute;
  height: 4px;
  width: 30px;
  transition: transform 400ms cubic-bezier(0.23, 1, 0.32, 1);
  border-radius: 2px;
}

.menu-button::before {
  content: '';
  margin-top: -8px;
}

.menu-button::after {
  content: '';
  margin-top: 8px;
}

#menu-toggle:checked + .menu-button-container .menu-button::before {
  margin-top: 0px;
  transform: rotate(405deg);
}

#menu-toggle:checked + .menu-button-container .menu-button {
  background: rgba(255, 255, 255, 0);
}


#menu-toggle:checked + .menu-button-container .menu-button::after {
  margin-top: 0px;
  transform: rotate(-405deg);
}

.mobile-only {
  display:none;
  width:36px;
  margin-right:16px;
  margin-top:6px;
}

@media (max-width: 700px) {
  .menu {
    display:none;
  }
  .mobile-only {
    display:inline-block;
  }
  .menu-button-container {
    display: inline-block;
  }
}
</style>