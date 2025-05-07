<template>
  <div class="content">
    <h1>My Profile</h1>
    
    <form @submit.prevent="submitForm">

      <div class="form-section">
        <h2>About</h2>
        <div class="form-group">
          <label for="name">Name</label>
          <input type="text" id="name" v-model="name" class="input form-control">
        </div>
        <div class="form-group">
          <label for="name">Username</label>
          <input type="text" id="username" v-model="username" class="input form-control">
        </div>

        <div class="form-group">
          <label for="hobbies_interests">About me</label>
          <textarea id="hobbies_interests" v-model="hobbies_interests" class="textarea"></textarea>
        </div>
      </div>
      

      <div class="form-section">
        <h2>Profile picture</h2>
        <label for="avatar"><input v-model="profilePic" value="avatar" type="radio" id="avatar" name="profile-picture" style="width:15px;"> Avatar</label>
        <label for="photo" style="margin-left:10px;"><input v-model="profilePic" value="photo" type="radio" id="photo" name="profile-picture" style="width:15px;"> Photo</label>
        <br>
        <br>
        <div v-if="profilePic == 'avatar'">
          <!-- Avatar SVG generated via multiavatar — safe from XSS -->
          <div v-html="avatarSvg" style="height:100px;width:100px;padding:5px;border:2px solid grey;border-radius:50%;"></div>
          <div style="height:10px;"></div>
          Avatar name:
          <br>
          <input type="text" id="avatar" v-model="avatar" class="input" style="max-width:280px;">
          <br>
          Avatar gender:
          <br>
          <label for="gender-any"><input v-model="avatarGender" value="any" id="gender-any" type="radio" style="width:15px;"> Any</label>
          <label for="gender-male" style="margin-left:10px;"><input v-model="avatarGender" value="male" id="gender-male" type="radio" style="width:15px;"> Male</label>
          <label for="gender-female" style="margin-left:10px;"><input v-model="avatarGender" value="female" type="radio" id="gender-female" style="width:15px;"> Female</label>
          <br>
          <br>
          <span style="font-size:13px;">Powered by <a href="https://multiavatar.com" target="_blank">Multiavatar</a></span>
        </div>
        <div  v-if="profilePic == 'photo'" style="min-height:150px;">
          <div class="thumbnails" style="height:100px;width:100px;padding:5px;border:2px solid grey;border-radius:50%;">
            <img :src="photo"  style="height:86px;width:86px;overflow:hidden;border-radius:50%;" >
          </div>
          <div style="height:10px;"></div>
          <div>
            <input type="file" id="photos" accept="image/*" v-on:change="onFileSelected">
          </div>
        </div>
      </div>


      <!-- <h2>Contacts</h2>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="text" id="email" v-model="email" class="input">
      </div>
      <div class="form-group">
        <label for="website">Website</label>
        <input type="text" id="website" v-model="website" class="input">
      </div>
      <div class="form-group">
        <label for="phone">Phone</label>
        <input type="text" id="phone" v-model="phone" class="input">
      </div> -->

      
      <div class="form-section">
        <h2>CoMatch Score</h2>

        CoMatch Score helps you find coliving spaces and roommates that match your lifestyle. 
        <div style="height:10px;"></div>
        CoMatch Score is in beta version. Let us know your <span style="font-weight:bold;"  class="link" @click="modalFeedback = true">feedback</span>.
        
        <div style="height:30px;"></div>

        <div class="form-group" v-for="(fit, index) in coliveFits" :key="index">
          <div style="display:grid;grid-template-columns: 1fr 0.2fr 1fr;">
            <span :for="fit.name">{{ fit.labels.left }}</span>
            <span style="display: block; text-align: center;">&nbsp;|</span>
            <!-- {{ fit.value }} -->
            <span style="text-align: right;">{{ fit.labels.right }}</span>
          </div>
          <input type="range" :id="fit.name" v-model="fit.value" min="0" max="10" class="form-control-range">
          <div style="height:10px;"></div>
        </div>
      </div>

      
      <div class="form-section">
        <h2>Social networks</h2>

        <div class="form-group">
          <label for="twitter">Twitter</label>
          <input type="text" id="twitter" v-model="twitter" class="input">
        </div>
        <div class="form-group">
          <label for="facebook">Facebook</label>
          <input type="text" id="facebook" v-model="facebook" class="input">
        </div>
        <div class="form-group">
          <label for="instagram">Instagram</label>
          <input type="text" id="instagram" v-model="instagram" class="input">
        </div>
        <div class="form-group">
          <label for="YouTube">YouTube</label>
          <input type="text" id="youtube" v-model="youtube" class="input">
        </div>
        <div class="form-group">
          <label for="tiktok">TikTok</label>
          <input type="text" id="tiktok" v-model="tiktok" class="input">
        </div>
        <div class="form-group">
          <label for="pinterest">Pinterest</label>
          <input type="text" id="pinterest" v-model="pinterest" class="input">
        </div>
        <div class="form-group">
          <label for="linkedin">LinkedIn</label>
          <input type="text" id="linkedin" v-model="linkedin" class="input">
        </div>
        <!-- <div class="form-group">
          <label for="linktree">Linktree</label>
          <input type="text" id="linktree" v-model="linktree" class="input">
        </div>
        <div class="form-group">
          <label for="telegram">Telegram</label>
          <input type="text" id="telegram" v-model="telegram" class="input">
        </div> -->
        <div class="form-group">
          <label for="github">GitHub</label>
          <input type="text" id="github" v-model="github" class="input">
        </div>
        <div class="form-group">
          <label for="substack">Substack</label>
          <input type="text" id="substack" v-model="substack" class="input">
        </div>
        <div class="form-group">
          <label for="medium">Medium</label>
          <input type="text" id="medium" v-model="medium" class="input">
        </div>
      </div>


      <div class="form-section">
        <h2>Visited Countries</h2>
        <VisitedCountries :visitedCountries="visitedCountries" :type="'form'" @selected-countries-changed = "handleSelectedCountriesChanged" />
      </div>
      

      <div class="form-section">
        <h2>Current location</h2>
        
        Select your current location to be visible on the coliving mates map (coming soon).
        <div style="height:15px;"></div>
        <!-- <div class="auth-label">Select country</div> -->
        <select id="country" class="auth-input" v-model="country">
            <option value="none">Choose country</option>
            <option v-for="country in countries" :key="country.code" :value="country.code">
                {{ country.name }}
            </option>
        </select> 
        <!-- <div style="height:5px;"></div>
        Precise location (optional)
        <br>
        <button>Select...</button> -->
      </div>


      <div class="form-section">
        <h2>Profile visibility</h2>
        <div class="form-group" style="text-align:left;">
          <input type="radio" id="public" v-model="private" value="0" style="width:13px;">
          <label for="public">Public</label>
          <br>
          <input type="radio" id="private" v-model="private" value="1" style="width:13px;">
          <label for="private">Private</label>
        </div>
        
        <div style="height:10px;"></div>
        <a v-if="!username" :href="'/mate/'+user_uid" target="_blank">View profile</a>
        <a v-else :href="'/mate/'+username" target="_blank">View profile</a>
      </div>
      
      <div style="height:20px"></div>
      <button type="submit" class="action-button button-post" :disabled="isUpdating">Save</button>
      <span v-if="profileUpdated">&nbsp;&nbsp;Updated!</span>
    </form>

    <div v-if="modalFeedback">
      <modal-feedback @close="modalFeedback = false" />
    </div>
  </div>
</template>
  
<script>
// import multiavatar from '@multiavatar/multiavatar/esm'
import ModalFeedback from '../components/ModalFeedback.vue';
import VisitedCountries from './VisitedCountries.vue';

export default {
  components: {
    VisitedCountries,
    ModalFeedback
  },
  data() {
    return {
      modalFeedback: false,
      isUpdating: false, 
      profileUpdated: false,

      username: '',
      name: '',

      multiavatar: window.multiavatar,

      avatar: '',
      avatarGender: 'any',
      photo: '',
      profilePic: 'avatar',

      avatarSvg: '',

      imageUrl: '',

      hobbies_interests: '',

      user_uid: '',

      twitter: '',
      facebook: '',
      instagram: '',
      youtube: '',
      tiktok: '',
      pinterest: '',
      linkedin: '',
      github: '',
      substack: '',
      medium: '',
      
      private: 0,
      
      coliveFits: {
        orgSpont: { name: 'organizedSpontaneous', value: '5', labels: { left: 'Organized', right: 'Spontaneous' } },
        quietLoud: { name: 'quietLoud', value: '5', labels: { left: 'Quiet', right: 'Loud' } },
        earlyNight: { name: 'earlyBirdNightOwl', value: '5', labels: { left: 'Early Bird', right: 'Night Owl' } },
        workFun: { name: 'workFun', value: '5', labels: { left: 'Work', right: 'Fun' } },
        expTrad: { name: 'experimentalTraditional', value: '5', labels: { left: 'Experimental', right: 'Traditional' } },
        multiLocal: { name: 'globalLocal', value: '5', labels: { left: 'Global', right: 'Local' } },
      },

      country: '',
      visitedCountries: [],
      
      countries: [
        { "code": "af", "name": "Afghanistan" },
        { "code": "ax", "name": "Aland Islands" },
        { "code": "al", "name": "Albania" },
        { "code": "dz", "name": "Algeria" },
        { "code": "as", "name": "American Samoa" },
        { "code": "ad", "name": "Andorra" },
        { "code": "ao", "name": "Angola" },
        { "code": "ai", "name": "Anguilla" },
        // { "code": "aq", "name": "Antarctica" },
        { "code": "ag", "name": "Antigua and Barbuda" },
        { "code": "ar", "name": "Argentina" },
        { "code": "am", "name": "Armenia" },
        { "code": "aw", "name": "Aruba" },
        { "code": "au", "name": "Australia" },
        { "code": "at", "name": "Austria" },
        { "code": "az", "name": "Azerbaijan" },
        { "code": "bs", "name": "Bahamas" },
        { "code": "bh", "name": "Bahrain" },
        { "code": "bd", "name": "Bangladesh" },
        { "code": "bb", "name": "Barbados" },
        { "code": "by", "name": "Belarus" },
        { "code": "be", "name": "Belgium" },
        { "code": "bz", "name": "Belize" },
        { "code": "bj", "name": "Benin" },
        { "code": "bm", "name": "Bermuda" },
        { "code": "bt", "name": "Bhutan" },
        { "code": "bo", "name": "Bolivia" },
        { "code": "bq", "name": "Bonaire, Sint Eustatius and Saba" },
        { "code": "ba", "name": "Bosnia and Herzegovina" },
        { "code": "bw", "name": "Botswana" },
        { "code": "bv", "name": "Bouvet Island" },
        { "code": "br", "name": "Brazil" },
        { "code": "io", "name": "British Indian Ocean Territory" },
        { "code": "bn", "name": "Brunei Darussalam" },
        { "code": "bg", "name": "Bulgaria" },
        { "code": "bf", "name": "Burkina Faso" },
        { "code": "bi", "name": "Burundi" },
        { "code": "kh", "name": "Cambodia" },
        { "code": "cm", "name": "Cameroon" },
        { "code": "ca", "name": "Canada" },
        { "code": "cv", "name": "Cape Verde" },
        { "code": "ky", "name": "Cayman Islands" },
        { "code": "cf", "name": "Central African Republic" },
        { "code": "td", "name": "Chad" },
        { "code": "cl", "name": "Chile" },
        { "code": "cn", "name": "China" },
        { "code": "cx", "name": "Christmas Island" },
        { "code": "cc", "name": "Cocos (Keeling) Islands" },
        { "code": "co", "name": "Colombia" },
        { "code": "km", "name": "Comoros" },
        { "code": "cg", "name": "Congo" },
        { "code": "cd", "name": "Congo, Democratic Republic of the Congo" },
        { "code": "ck", "name": "Cook Islands" },
        { "code": "cr", "name": "Costa Rica" },
        { "code": "ci", "name": "Cote D'Ivoire" },
        { "code": "hr", "name": "Croatia" },
        { "code": "cu", "name": "Cuba" },
        { "code": "cw", "name": "Curacao" },
        { "code": "cy", "name": "Cyprus" },
        { "code": "cz", "name": "Czech Republic" },
        { "code": "dk", "name": "Denmark" },
        { "code": "dj", "name": "Djibouti" },
        { "code": "dm", "name": "Dominica" },
        { "code": "do", "name": "Dominican Republic" },
        { "code": "ec", "name": "Ecuador" },
        { "code": "eg", "name": "Egypt" },
        { "code": "sv", "name": "El Salvador" },
        { "code": "gq", "name": "Equatorial Guinea" },
        { "code": "er", "name": "Eritrea" },
        { "code": "ee", "name": "Estonia" },
        { "code": "et", "name": "Ethiopia" },
        { "code": "fk", "name": "Falkland Islands (Malvinas)" },
        { "code": "fo", "name": "Faroe Islands" },
        { "code": "fj", "name": "Fiji" },
        { "code": "fi", "name": "Finland" },
        { "code": "fr", "name": "France" },
        { "code": "gf", "name": "French Guiana" },
        { "code": "pf", "name": "French Polynesia" },
        { "code": "tf", "name": "French Southern Territories" },
        { "code": "ga", "name": "Gabon" },
        { "code": "gm", "name": "Gambia" },
        { "code": "ge", "name": "Georgia" },
        { "code": "de", "name": "Germany" },
        { "code": "gh", "name": "Ghana" },
        { "code": "gi", "name": "Gibraltar" },
        { "code": "gr", "name": "Greece" },
        { "code": "gl", "name": "Greenland" },
        { "code": "gd", "name": "Grenada" },
        { "code": "gp", "name": "Guadeloupe" },
        { "code": "gu", "name": "Guam" },
        { "code": "gt", "name": "Guatemala" },
        { "code": "gg", "name": "Guernsey" },
        { "code": "gn", "name": "Guinea" },
        { "code": "gw", "name": "Guinea-Bissau" },
        { "code": "gy", "name": "Guyana" },
        { "code": "ht", "name": "Haiti" },
        { "code": "hm", "name": "Heard Island and McDonald Islands" },
        { "code": "va", "name": "Holy See (Vatican City State)" },
        { "code": "hn", "name": "Honduras" },
        { "code": "hk", "name": "Hong Kong" },
        { "code": "hu", "name": "Hungary" },
        { "code": "is", "name": "Iceland" },
        { "code": "in", "name": "India" },
        { "code": "id", "name": "Indonesia" },
        { "code": "ir", "name": "Iran, Islamic Republic of" },
        { "code": "iq", "name": "Iraq" },
        { "code": "ie", "name": "Ireland" },
        { "code": "im", "name": "Isle of Man" },
        { "code": "il", "name": "Israel" },
        { "code": "it", "name": "Italy" },
        { "code": "jm", "name": "Jamaica" },
        { "code": "jp", "name": "Japan" },
        { "code": "je", "name": "Jersey" },
        { "code": "jo", "name": "Jordan" },
        { "code": "kz", "name": "Kazakhstan" },
        { "code": "ke", "name": "Kenya" },
        { "code": "ki", "name": "Kiribati" },
        { "code": "kp", "name": "Korea, Democratic People's Republic of" },
        { "code": "kr", "name": "Korea, Republic of" },
        { "code": "xk", "name": "Kosovo" },
        { "code": "kw", "name": "Kuwait" },
        { "code": "kg", "name": "Kyrgyzstan" },
        { "code": "la", "name": "Lao People's Democratic Republic" },
        { "code": "lv", "name": "Latvia" },
        { "code": "lb", "name": "Lebanon" },
        { "code": "ls", "name": "Lesotho" },
        { "code": "lr", "name": "Liberia" },
        { "code": "ly", "name": "Libya" },
        { "code": "li", "name": "Liechtenstein" },
        { "code": "lt", "name": "Lithuania" },
        { "code": "lu", "name": "Luxembourg" },
        { "code": "mo", "name": "Macao" },
        { "code": "mk", "name": "North Macedonia" },
        { "code": "mg", "name": "Madagascar" },
        { "code": "mw", "name": "Malawi" },
        { "code": "my", "name": "Malaysia" },
        { "code": "mv", "name": "Maldives" },
        { "code": "ml", "name": "Mali" },
        { "code": "mt", "name": "Malta" },
        { "code": "mh", "name": "Marshall Islands" },
        { "code": "mq", "name": "Martinique" },
        { "code": "mr", "name": "Mauritania" },
        { "code": "mu", "name": "Mauritius" },
        { "code": "yt", "name": "Mayotte" },
        { "code": "mx", "name": "Mexico" },
        { "code": "fm", "name": "Micronesia, Federated States of" },
        { "code": "md", "name": "Moldova, Republic of" },
        { "code": "mc", "name": "Monaco" },
        { "code": "mn", "name": "Mongolia" },
        { "code": "me", "name": "Montenegro" },
        { "code": "ms", "name": "Montserrat" },
        { "code": "ma", "name": "Morocco" },
        { "code": "mz", "name": "Mozambique" },
        { "code": "mm", "name": "Myanmar" },
        { "code": "na", "name": "Namibia" },
        { "code": "nr", "name": "Nauru" },
        { "code": "np", "name": "Nepal" },
        { "code": "nl", "name": "Netherlands" },
        { "code": "nc", "name": "New Caledonia" },
        { "code": "nz", "name": "New Zealand" },
        { "code": "ni", "name": "Nicaragua" },
        { "code": "ne", "name": "Niger" },
        { "code": "ng", "name": "Nigeria" },
        { "code": "nu", "name": "Niue" },
        { "code": "nf", "name": "Norfolk Island" },
        { "code": "mp", "name": "Northern Mariana Islands" },
        { "code": "no", "name": "Norway" },
        { "code": "om", "name": "Oman" },
        { "code": "pk", "name": "Pakistan" },
        { "code": "pw", "name": "Palau" },
        { "code": "ps", "name": "Palestinian Territory, Occupied" },
        { "code": "pa", "name": "Panama" },
        { "code": "pg", "name": "Papua New Guinea" },
        { "code": "py", "name": "Paraguay" },
        { "code": "pe", "name": "Peru" },
        { "code": "ph", "name": "Philippines" },
        { "code": "pn", "name": "Pitcairn" },
        { "code": "pl", "name": "Poland" },
        { "code": "pt", "name": "Portugal" },
        { "code": "pr", "name": "Puerto Rico" },
        { "code": "qa", "name": "Qatar" },
        { "code": "re", "name": "Reunion" },
        { "code": "ro", "name": "Romania" },
        { "code": "ru", "name": "Russian Federation" },
        { "code": "rw", "name": "Rwanda" },
        { "code": "bl", "name": "Saint Barthelemy" },
        { "code": "sh", "name": "Saint Helena" },
        { "code": "kn", "name": "Saint Kitts and Nevis" },
        { "code": "lc", "name": "Saint Lucia" },
        { "code": "mf", "name": "Saint Martin (French part)" },
        { "code": "pm", "name": "Saint Pierre and Miquelon" },
        { "code": "vc", "name": "Saint Vincent and the Grenadines" },
        { "code": "ws", "name": "Samoa" },
        { "code": "sm", "name": "San Marino" },
        { "code": "st", "name": "Sao Tome and Principe" },
        { "code": "sa", "name": "Saudi Arabia" },
        { "code": "sn", "name": "Senegal" },
        { "code": "rs", "name": "Serbia" },
        { "code": "sc", "name": "Seychelles" },
        { "code": "sl", "name": "Sierra Leone" },
        { "code": "sg", "name": "Singapore" },
        { "code": "sx", "name": "Sint Maarten (Dutch part)" },
        { "code": "sk", "name": "Slovakia" },
        { "code": "si", "name": "Slovenia" },
        { "code": "sb", "name": "Solomon Islands" },
        { "code": "so", "name": "Somalia" },
        { "code": "za", "name": "South Africa" },
        { "code": "gs", "name": "South Georgia and the South Sandwich Islands" },
        { "code": "ss", "name": "South Sudan" },
        { "code": "es", "name": "Spain" },
        { "code": "lk", "name": "Sri Lanka" },
        { "code": "sd", "name": "Sudan" },
        { "code": "sr", "name": "Suriname" },
        { "code": "sj", "name": "Svalbard and Jan Mayen" },
        { "code": "sz", "name": "Swaziland" },
        { "code": "se", "name": "Sweden" },
        { "code": "ch", "name": "Switzerland" },
        { "code": "sy", "name": "Syrian Arab Republic" },
        { "code": "tw", "name": "Taiwan" },
        { "code": "tj", "name": "Tajikistan" },
        { "code": "tz", "name": "Tanzania, United Republic of" },
        { "code": "th", "name": "Thailand" },
        { "code": "tl", "name": "Timor-Leste" },
        { "code": "tg", "name": "Togo" },
        { "code": "tk", "name": "Tokelau" },
        { "code": "to", "name": "Tonga" },
        { "code": "tt", "name": "Trinidad and Tobago" },
        { "code": "tn", "name": "Tunisia" },
        { "code": "tr", "name": "Turkey" },
        { "code": "tm", "name": "Turkmenistan" },
        { "code": "tc", "name": "Turks and Caicos Islands" },
        { "code": "tv", "name": "Tuvalu" },
        { "code": "ug", "name": "Uganda" },
        { "code": "ua", "name": "Ukraine" },
        { "code": "ae", "name": "United Arab Emirates" },
        { "code": "gb", "name": "United Kingdom" },
        { "code": "us", "name": "United States" },
        { "code": "um", "name": "United States Minor Outlying Islands" },
        { "code": "uy", "name": "Uruguay" },
        { "code": "uz", "name": "Uzbekistan" },
        { "code": "vu", "name": "Vanuatu" },
        { "code": "ve", "name": "Venezuela, Bolivarian Republic of" },
        { "code": "vn", "name": "Viet Nam" },
        { "code": "vg", "name": "Virgin Islands, British" },
        { "code": "vi", "name": "Virgin Islands, U.S." },
        { "code": "wf", "name": "Wallis and Futuna" },
        { "code": "eh", "name": "Western Sahara" },
        { "code": "ye", "name": "Yemen" },
        { "code": "zm", "name": "Zambia" },
        { "code": "zw", "name": "Zimbabwe" }

      ],
    }
  },

  watch: {
    avatar: function(newVal, oldVal) {
      // 'avatar' property has changed. Regenerate the avatar.
      this.generateAvatar()
    },
    avatarGender: function() {
      this.generateAvatar()
    }
  },

  mounted() {
    axios.get("/api/mate").then((r) => {
      let m = r.data;
      window.mate = m;

      this.user_uid = window.mate.uid;
      this.username = m.username;
      this.name = m.name; 

      this.avatar = m.avatar;
      this.photo = m.photo;
      this.profilePic = m.profile_pic;

      this.hobbies_interests = m.hobbies_interests;

      this.twitter = m.twitter;
      this.facebook = m.facebook;
      this.instagram = m.instagram;
      this.youtube = m.youtube;
      this.tiktok  = m.tiktok;
      this.pinterest  = m.pinterest;
      this.linkedin  = m.linkedin;
      this.github = m.github;
      this.substack = m.substack;
      this.medium = m.medium;

      this.visitedCountries = JSON.parse(m.visited_countries);

      this.coliveFits['orgSpont'].value = m.orgSpont;
      this.coliveFits['quietLoud'].value = m.quietLoud;
      this.coliveFits['earlyNight'].value = m.earlyNight;
      this.coliveFits['workFun'].value = m.workFun;
      this.coliveFits['expTrad'].value = m.expTrad;
      this.coliveFits['multiLocal'].value = m.multiLocal;

      this.private = m.private;
      this.country = m.location_country;
    })

    this.generateAvatar()
  },

  methods: {
    handleSelectedCountriesChanged(countries) {
      // console.log(countries);
      this.visitedCountries = countries;
    },
    generateAvatar() {
      if (this.avatarGender == 'male') {
        if (this.avatar.substring(0,3) == '[f]') {
          this.avatar = this.avatar.substring(3);
        }
        this.avatarSvg = multiavatar(this.avatar, undefined, undefined, 'male')
      }
      else if (this.avatarGender == 'female') {
        if (this.avatar.substring(0,3) == '[f]') {
          this.avatar = this.avatar.substring(3);
        }
        this.avatarSvg = multiavatar(this.avatar, undefined, undefined, 'female')
      }
      else {
        this.avatarSvg = multiavatar(this.avatar)
      }
    },
    validateFiles(files) {
      const invalidFiles = [];

      // Check file format and size
      files.forEach(file => {
          if (!['image/jpeg', 'image/png'].includes(file.type)) {
              invalidFiles.push({ name: file.name, format: true });
          } else if (file.size > 2 * 1024 * 1024) {
              invalidFiles.push({ name: file.name, size: true });
          }
      });

      return invalidFiles;
    },
    onFileSelected(event) {
      const file = event.target.files[0]; // Get the first selected file

      // Check file format and size before updating files
      const invalidFiles = this.validateFiles([ file ]);

      if (invalidFiles.length > 0) {
          // Display alerts for invalid files
          invalidFiles.forEach(invalidFile => {
              if (invalidFile.format) {
                  alert(`Invalid file format: ${invalidFile.name}`);
              } else if (invalidFile.size) {
                  alert(`File size exceeds 2MB: ${invalidFile.name}`);
              } else if (invalidFile.count) {
                  alert(`Maximum 20 files allowed. You selected ${invalidFile.count} files.`);
              }
          });
          return;
      }

      if (file) {
        const reader = new FileReader();
        reader.onload = () => {
          this.imageUrl = reader.result;
          this.photo = reader.result;
        };
        reader.readAsDataURL(file);

        // Update the photo field in the data
        this.photoFile = file;
      }
    },
    submitForm() {
      this.isUpdating = true;

      if (this.avatarGender == 'male') {
        this.avatar = '[m]' + this.avatar;
      }
      else if (this.avatarGender == 'female') {
        this.avatar = '[f]' + this.avatar;
      }

      // Create FormData object to append form data
      const formData = new FormData();
      formData.append('username', this.username);
      formData.append('name', this.name);
      formData.append('avatar', this.avatar);
      formData.append('profile_pic', this.profilePic);
      formData.append('hobbies_interests', this.hobbies_interests);
      formData.append('twitter', this.twitter);
      formData.append('facebook', this.facebook);
      formData.append('instagram', this.instagram);
      formData.append('youtube', this.youtube);
      formData.append('tiktok', this.tiktok);
      formData.append('pinterest', this.pinterest);
      formData.append('linkedin', this.linkedin);
      formData.append('github', this.github);
      formData.append('substack', this.substack);
      formData.append('medium', this.medium);
      formData.append('visitedCountries', this.visitedCountries);
      formData.append('location_country', this.country);
      formData.append('private', this.private);

      // for (let i = 0; i < this.coliveFits.length; i++) {
      //   formData.append(this.coliveFits[i].name, this.coliveFits[i].value);
      // }

      for (let key in this.coliveFits) {
        formData.append(key, this.coliveFits[key].value);
      }

      // Check if an image file is selected
      if (this.profilePic === 'photo' && this.photoFile) {
        // Append the selected file to the FormData object with the correct content type
        formData.append('photo', this.photoFile, this.photoFile.name);
      }

      // Perform the axios request with the FormData object
      axios.post('/api/mate', formData)
      .then(response => {
        // console.log(response.data);
        window.mate = response.data;

        this.isUpdating = false;
        this.profileUpdated = true;
        window.location = '/mate/'+this.username;
        // do something with the response, e.g., show a success message
      })
      .catch(error => {
        console.log(error.response.data);
        // show an error message
      });
    },
  }
}
</script>

<style>
  .form-section {
    padding:20px;
    border:1px solid rgb(225, 225, 225);
    border-radius:5px;
    margin-bottom:30px;
    background: #f8fbff;
  }

  .form-section h2 {
    margin-top: 0px;
  }

  .form-group {
    margin-top: 5px;
  }
</style>