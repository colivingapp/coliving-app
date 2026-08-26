<template>
  <div class="content">
    <div class="page-form-header">
      <h1>My Profile</h1>
      <p class="page-form-intro">Update how you appear to spaces and other mates.</p>
    </div>
    
    <form @submit.prevent="submitForm">

      <div class="form-section">
        <h2>About</h2>
        <div class="form-group">
          <label class="form-label" for="name">Name</label>
          <input type="text" id="name" v-model="name" class="input form-control">
        </div>
        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <input type="text" id="username" v-model="username" class="input form-control">
        </div>

        <div class="form-group">
          <label class="form-label" for="hobbies_interests">About me</label>
          <textarea id="hobbies_interests" v-model="hobbies_interests" class="textarea"></textarea>
        </div>
      </div>
      

      <div class="form-section profile-pic-section">
        <h2>Profile picture</h2>
        <div class="form-choices">
          <label class="form-choice" :class="{ 'is-active': profilePic == 'avatar' }" for="avatar">
            <input v-model="profilePic" value="avatar" type="radio" id="avatar" name="profile-picture">
            Avatar
          </label>
          <label class="form-choice" :class="{ 'is-active': profilePic == 'photo' }" for="photo">
            <input v-model="profilePic" value="photo" type="radio" id="photo" name="profile-picture">
            Photo
          </label>
        </div>
        <div v-if="profilePic == 'avatar'" class="avatar-widget">
          <!-- Avatar SVG generated via multiavatar — safe from XSS -->
          <div class="form-avatar" v-html="avatarSvg"></div>
          <label class="form-label" for="avatar-name">Avatar name</label>
          <div class="avatar-name-row">
            <input type="text" id="avatar-name" v-model="avatar" class="input" :disabled="isSpinning">
            <button
              type="button"
              class="avatar-spin-btn"
              :class="{ 'is-spinning': isSpinning }"
              :disabled="isSpinning"
              title="New avatar"
              aria-label="Generate a new avatar"
              @click="spinAvatar"
            >
              <svg class="avatar-spin-icon" version="1.1" viewBox="0 0 9.8142 9.8427" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="m8.3822 8.39a4.9107 4.9107 0 0 0-2.9686-8.3502 0.38365 0.38365 0 0 0-0.29633 0.0979 0.36777 0.36777 0 0 0-0.12436 0.28575v0.045a0.381 0.381 0 0 0 0.34132 0.37835 4.101 4.101 0 0 1 2.4897 6.985l-0.0926 0.0926a0.15875 0.15875 0 0 1-0.2249 0l-0.8546-0.8546a0.15875 0.15875 0 0 0-0.26458 0.11113v2.503a0.15875 0.15875 0 0 0 0.15875 0.15875h2.5056a0.15346 0.15346 0 0 0 0.14552-0.0979 0.15081 0.15081 0 0 0-0.0344-0.17197l-0.87312-0.86783a0.15875 0.15875 0 0 1 0-0.22225zm-7.5671-3.4687a4.0852 4.0852 0 0 1 1.188-2.9104l0.0926-0.09a0.15346 0.15346 0 0 1 0.22225 0l0.85725 0.85461a0.15875 0.15875 0 0 0 0.26459-0.11113v-2.5056a0.15875 0.15875 0 0 0-0.15875-0.15875h-2.4897a0.15346 0.15346 0 0 0-0.14552 0.0979 0.15081 0.15081 0 0 0 0.0344 0.17198l0.85725 0.85725a0.15875 0.15875 0 0 1 0 0.22225l-0.0926 0.0926a4.908 4.908 0 0 0 2.9686 8.3608 0.38629 0.38629 0 0 0 0.42069-0.38365v-0.0423a0.381 0.381 0 0 0-0.34131-0.381 4.1037 4.1037 0 0 1-3.6777-4.0746z"/>
              </svg>
            </button>
          </div>
        </div>
        <div v-if="profilePic == 'photo'" class="avatar-widget">
          <div class="form-avatar">
            <img :src="photo" alt="">
          </div>
          <label class="form-label" for="photos">Upload photo</label>
          <input type="file" id="photos" accept="image/*" v-on:change="onFileSelected">
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
        <h2>Lifestyle Match</h2>
        <p class="form-hint">
          Lifestyle Match helps you find coliving spaces and roommates that match your lifestyle.
        </p>

        <div class="form-group" v-for="(fit, index) in coliveFits" :key="index">
          <div class="form-range-labels">
            <span>{{ fit.labels.left }}</span>
            <span>{{ fit.labels.right }}</span>
          </div>
          <input type="range" :id="fit.name" v-model="fit.value" min="0" max="10" class="form-control-range">
        </div>
      </div>

      
      <div class="form-section">
        <h2>Social networks</h2>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label social-field-label" for="twitter">
              <span class="social-field-icon" aria-hidden="true"><social-field-icon name="twitter" /></span>
              Twitter
            </label>
            <input type="text" id="twitter" v-model="twitter" class="input">
          </div>
          <div class="form-group">
            <label class="form-label social-field-label" for="facebook">
              <span class="social-field-icon" aria-hidden="true"><social-field-icon name="facebook" /></span>
              Facebook
            </label>
            <input type="text" id="facebook" v-model="facebook" class="input">
          </div>
          <div class="form-group">
            <label class="form-label social-field-label" for="instagram">
              <span class="social-field-icon" aria-hidden="true"><social-field-icon name="instagram" /></span>
              Instagram
            </label>
            <input type="text" id="instagram" v-model="instagram" class="input">
          </div>
          <div class="form-group">
            <label class="form-label social-field-label" for="youtube">
              <span class="social-field-icon" aria-hidden="true"><social-field-icon name="youtube" /></span>
              YouTube
            </label>
            <input type="text" id="youtube" v-model="youtube" class="input">
          </div>
          <div class="form-group">
            <label class="form-label social-field-label" for="tiktok">
              <span class="social-field-icon" aria-hidden="true"><social-field-icon name="tiktok" /></span>
              TikTok
            </label>
            <input type="text" id="tiktok" v-model="tiktok" class="input">
          </div>
          <div class="form-group">
            <label class="form-label social-field-label" for="pinterest">
              <span class="social-field-icon" aria-hidden="true"><social-field-icon name="pinterest" /></span>
              Pinterest
            </label>
            <input type="text" id="pinterest" v-model="pinterest" class="input">
          </div>
          <div class="form-group">
            <label class="form-label social-field-label" for="linkedin">
              <span class="social-field-icon" aria-hidden="true"><social-field-icon name="linkedin" /></span>
              LinkedIn
            </label>
            <input type="text" id="linkedin" v-model="linkedin" class="input">
          </div>
          <div class="form-group">
            <label class="form-label social-field-label" for="github">
              <span class="social-field-icon" aria-hidden="true"><social-field-icon name="github" /></span>
              GitHub
            </label>
            <input type="text" id="github" v-model="github" class="input">
          </div>
          <div class="form-group">
            <label class="form-label social-field-label" for="substack">
              <span class="social-field-icon" aria-hidden="true"><social-field-icon name="substack" /></span>
              Substack
            </label>
            <input type="text" id="substack" v-model="substack" class="input">
          </div>
          <div class="form-group">
            <label class="form-label social-field-label" for="medium">
              <span class="social-field-icon" aria-hidden="true"><social-field-icon name="medium" /></span>
              Medium
            </label>
            <input type="text" id="medium" v-model="medium" class="input">
          </div>
        </div>
      </div>
      

      <div class="form-section">
        <h2>Visited Countries</h2>
        <VisitedCountries :visitedCountries="visitedCountries" :type="'form'" @selected-countries-changed = "handleSelectedCountriesChanged" />
      </div>
      

      <div class="form-section">
        <h2>Current location</h2>
        <p class="form-hint">Select your current location to be visible on the coliving mates map (coming soon).</p>
        <label class="form-label" for="country">Country</label>
        <div class="country-select-wrap">
          <select id="country" class="input" v-model="country">
              <option value="none">Choose country</option>
              <option v-for="country in countries" :key="country.code" :value="country.code">
                  {{ country.name }}
              </option>
          </select>
          <span v-if="country && country !== 'none'" class="country-select-flag" aria-hidden="true">
            <span :class="'fi fi-' + country"></span>
          </span>
        </div>
      </div>


      <div class="form-section">
        <h2>Profile visibility</h2>
        <div class="form-choices">
          <label class="form-choice" :class="{ 'is-active': private == 0 }" for="public">
            <input type="radio" id="public" v-model="private" value="0">
            Public
          </label>
          <label class="form-choice" :class="{ 'is-active': private == 1 }" for="private">
            <input type="radio" id="private" v-model="private" value="1">
            Private
          </label>
        </div>
        
        <p class="form-hint" style="margin-top:16px;margin-bottom:0;">
          <a v-if="!username" :href="'/mate/'+user_uid" target="_blank">View profile</a>
          <a v-else :href="'/mate/'+username" target="_blank">View profile</a>
        </p>
      </div>
      
      <div class="form-actions">
        <button type="submit" class="action-button button-post" :disabled="isUpdating">{{ isUpdating ? 'Please wait...' : 'Save' }}</button>
        <span v-if="profileUpdated" class="form-success" :class="{ 'is-fading': profileSavedFading }">Saved!</span>
      </div>
    </form>

  </div>
</template>
  
<script>
// import multiavatar from '@multiavatar/multiavatar/esm'
import "/node_modules/flag-icons/css/flag-icons.min.css";
import SocialFieldIcon from '../components/SocialFieldIcon.vue';
import VisitedCountries from './VisitedCountries.vue';

export default {
  components: {
    VisitedCountries,
    SocialFieldIcon,
  },
  data() {
    return {
      isUpdating: false, 
      profileUpdated: false,
      profileSavedFading: false,
      savedFadeTimer: null,

      username: '',
      name: '',

      multiavatar: window.multiavatar,

      avatar: '',
      avatarGender: 'any',
      isSpinning: false,
      spinTimer: null,
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

  beforeUnmount() {
    if (this.spinTimer) {
      clearTimeout(this.spinTimer);
    }
    if (this.savedFadeTimer) {
      clearTimeout(this.savedFadeTimer);
    }
  },

  methods: {
    showSavedMessage() {
      this.profileUpdated = true;
      this.profileSavedFading = false;
      if (this.savedFadeTimer) {
        clearTimeout(this.savedFadeTimer);
      }
      this.savedFadeTimer = setTimeout(() => {
        this.profileSavedFading = true;
        this.savedFadeTimer = setTimeout(() => {
          this.profileUpdated = false;
          this.profileSavedFading = false;
        }, 500);
      }, 2200);
    },
    spinAvatar() {
      if (this.isSpinning) return;

      this.isSpinning = true;
      const timeClicked = Date.now();
      const hex = '0123456789abcdef';
      let randomHash = '';
      for (let i = 0; i < 8; i++) {
        randomHash += hex[Math.floor(Math.random() * hex.length)];
      }
      let constructed = '';

      const runIt = () => {
        this.spinTimer = setTimeout(() => {
          if (constructed.length < 8) {
            const lastChar = randomHash.substring(randomHash.length - 1);
            randomHash = randomHash.slice(0, -1);
            constructed += lastChar;
            this.avatar = constructed;
            runIt();
          } else {
            const timeDiff = Date.now() - timeClicked;
            if (timeDiff > 100) {
              this.isSpinning = false;
            } else {
              this.spinTimer = setTimeout(() => {
                this.isSpinning = false;
              }, 100 - timeDiff);
            }
          }
        }, 6);
      };

      runIt();
    },
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
      this.profileUpdated = false;

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
        window.mate = response.data;
        this.isUpdating = false;
        this.showSavedMessage();
      })
      .catch(error => {
        this.isUpdating = false;
        console.log(error.response?.data);
      });
    },
  }
}
</script>

<style scoped>
.form-actions {
  margin-top: 28px;
}

.form-actions .button-post,
.form-actions .button-post:disabled {
  width: 260px;
}

.form-actions .action-button.button-post:disabled {
  background-color: #8ecf9a;
  border-color: #8ecf9a;
  color: #fff;
}

.form-success {
  transition: opacity 0.5s ease;
}

.form-success.is-fading {
  opacity: 0;
}

.form-range-labels {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  color: rgb(89, 89, 89);
}

.country-select-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}

.country-select-wrap .input {
  width: 100%;
  max-width: 280px;
  margin-bottom: 0;
}

.country-select-flag {
  display: inline-flex;
  align-items: center;
  flex-shrink: 0;
}

.country-select-flag .fi {
  width: 2.2em;
  line-height: 1.65em;
  border-radius: 3px;
}

.avatar-widget {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  width: 100%;
  max-width: 260px;
  margin: 20px 0 0;
  padding: 22px 18px 18px;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
}

.avatar-widget .form-label {
  margin-top: 14px;
}

.avatar-widget input[type="file"] {
  width: 100%;
  max-width: 200px;
}

.avatar-name-row {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  max-width: 200px;
}

.avatar-name-row .input {
  flex: 1;
  min-width: 0;
  margin-bottom: 0;
}

.avatar-spin-btn {
  flex-shrink: 0;
  width: 38px;
  height: 38px;
  padding: 8px;
  border: 1px solid #e4e4e4;
  border-radius: 50%;
  background: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-spin-btn:hover:not(:disabled) {
  background: #f4faff;
  border-color: #87c9eb;
}

.avatar-spin-btn:disabled {
  cursor: default;
}

.avatar-spin-icon {
  width: 18px;
  height: 18px;
  fill: #555;
}

.avatar-spin-btn.is-spinning .avatar-spin-icon {
  animation: avatar-spin 200ms linear infinite;
}

@keyframes avatar-spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(180deg); }
}
</style>