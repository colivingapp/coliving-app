<template>
  <div v-if="created" class="content">
    <h1>Your Space Is Pending Approval</h1>
    <br>
    Your new space has been successfully created and is currently under review. Approval typically takes up to 24 hours.
    <br><br>
    <b>Once approved, you will receive an email, and your space profile will go live!</b>
  </div>
  <div v-else>
    <div v-if="!userLoggedIn" class="content">
      <h1>New Coliving Space</h1>
      Add a new coliving space to Coliving App to enhance your space's visibility, connect directly with potential guests, and manage coliving experiences through our platform.

      <div style="height:30px;"></div>

      Log in to continue
      <div style="height:10px;"></div>
      <router-link to="/login"><button type="button" class="btn btn-md btn-primary">Login</button></router-link>
      
      <div style="height:30px;"></div>

      Create a free account
      <div style="height:10px;"></div>
      <router-link to="/register"><button type="button" class="btn btn-md btn-primary">Sign Up</button></router-link> 
    </div>
    <div v-else class="content">
      <h1 v-if="pageType == 'create'">New Coliving Space</h1>
      <h1 v-else>Edit Coliving Space</h1>
      
      <div style="height:10px;"></div>

      <form @submit.prevent="submitForm">

        <div v-if="pageType == 'create'" class="space-create-progress">
          <div
            v-for="(step, index) in createSteps"
            :key="step.key"
            class="space-create-step"
            :class="{ active: activeCreateStep === step.key, complete: index < activeCreateStepIndex }"
          >
            <span class="space-create-step-number">{{ index + 1 }}</span>
            <span>{{ step.label }}</span>
          </div>
        </div>

        <div v-if="errors" class="form-error-summary">
          <span v-if="error1">* The name is required. Please enter your space name to proceed.<br></span>
          <span v-if="error6">* Category is required. Please select a category for your space.<br></span>
          <span v-if="error2">* Email address is required for communication. Please enter your space email.<br></span>
          <span v-if="error3">* Country is required. Please select the country where your space is located.<br></span>
          <span v-if="error4">* Address is required. Please enter the address of your space.</span>
          <span v-if="error5">* Unknown error. Please contact site admin.</span>
          <span v-if="errorPhoto">* {{ errorPhoto }}</span>
        </div>

        <div class="form-section" v-if="isSectionVisible('general')">
          <span v-if="userId == 1 && pageType == 'create' && true == false">
            <textarea v-model="json"></textarea>
          </span>

          <h2>General info</h2>

          <div class="form-group">
            <label for="name">Name <span style="color:red;">*</span></label>
            <input type="text" id="name" v-model="name" class="input">
          </div>

          <div class="form-group">
            <label for="category">What type of space is this? <span style="color:red;">*</span></label>
            <br>
            <select id="category" v-model="category" class="input" @change="onCategoryChange" required style="min-width:240px;">
              <option value="">Select a category</option>
              <option v-for="option in categoryOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
            <div v-if="categoryDescription" style="color:#666;font-size:13px;margin-top:-5px;margin-bottom:10px;max-width:650px;">
              {{ categoryDescription }}
            </div>
            <div v-if="isSelfManagedCategory" class="self-managed-note">
              <b>How self-managed spaces work:</b> members decide together who joins and who leaves. There is no single admin controlling membership; applications and removals are handled by majority vote.
            </div>
          </div>

          <div class="form-group" v-if="category">
            <label for="subcategory">Subtype (optional)</label>
            <br>
            <select id="subcategory" v-model="subcategory" class="input" style="min-width:240px;">
              <option value="">None</option>
              <option v-for="(label, value) in availableSubcategories" :key="value" :value="value">{{ label }}</option>
            </select>
            <div v-if="subcategoryDescription" style="color:#666;font-size:13px;margin-top:-5px;margin-bottom:15px;max-width:650px;">
              {{ subcategoryDescription }}
            </div>
          </div>

          <div class="form-group">
            <label for="email">Username</label>
            <input type="text" id="username" v-model="username" class="input">
          </div>
          <!-- <div>
            <label for="amenities">Amenities</label>
            <input type="text" id="amenities" v-model="amenities" class="input">
          </div> -->
          <div>
            <label for="description">Description</label>
            <textarea id="description" v-model="description" class="textarea" placeholder="Availability, amenities, pricing, rules, etc." style="height:200px;"></textarea>
          </div>
        </div>

        <div class="form-section" v-if="isSectionVisible('contact')">
          <h2>Contacts</h2>

          <div class="form-group">
            <label for="email">Email <span style="color:red;">*</span></label>
            <input type="text" id="email" v-model="email" class="input">
          </div>
          <div class="form-group">
            <label for="website">Website</label>
            <input type="text" id="website" v-model="website" class="input">
          </div>
          <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" v-model="phone" class="input">
          </div>
          <div class="form-group">
            <label for="whatsapp">WhatsApp</label>
            <input type="text" id="whatsapp" v-model="whatsapp" class="input">
          </div>

          <div v-if="pageType == 'create'" class="space-create-expand">
            <button type="button" class="btn btn-sm btn-quiet" @click="showCreateSocials = !showCreateSocials">
              {{ showCreateSocials ? 'Hide social networks' : 'Add social networks' }}
            </button>
          </div>

          <div v-if="pageType == 'create' && showCreateSocials" class="form-grid space-social-grid">
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
              <label class="form-label social-field-label" for="linktree">
                <span class="social-field-icon" aria-hidden="true"><social-field-icon name="linktree" /></span>
                Linktree
              </label>
              <input type="text" id="linktree" v-model="linktree" class="input">
            </div>
            <div class="form-group">
              <label class="form-label social-field-label" for="telegram">
                <span class="social-field-icon" aria-hidden="true"><social-field-icon name="telegram" /></span>
                Telegram
              </label>
              <input type="text" id="telegram" v-model="telegram" class="input">
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


        <div class="form-section" v-if="isSectionVisible('location')">
          <h2>Location</h2>
          
          <label>Location <span style="color:red;">*</span></label>
          <FormGoogleAutoComplete :address="address" @location-updated="handleLocationUpdated" />
          <div style="color:#666;font-size:13px;margin-top:-5px;margin-bottom:10px;max-width:650px;">
            Start typing your space name or address, then select the exact location from the list.
          </div>
            
          <div style="height:10px;"></div>
          <MapSpacesSearchable :location="{ 'latitude': latitude, 'longitude': longitude }"   />

          <div style="height:20px;"></div>
          <label for="google_url">Google Maps URL (Optional)</label>
          <input type="text" id="google_url" v-model="google_url" class="input">
        </div>


        <div class="form-section" v-if="isSectionVisible('photos')">
          <h2>Photos</h2>
          <SpacePhotoManager v-model="photos" />
        </div>
        
        <div class="form-section" v-if="pageType == 'edit'">
          <h2>Social networks</h2>

          <div class="form-grid space-social-grid">
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
              <label class="form-label social-field-label" for="linktree">
                <span class="social-field-icon" aria-hidden="true"><social-field-icon name="linktree" /></span>
                Linktree
              </label>
              <input type="text" id="linktree" v-model="linktree" class="input">
            </div>
            <div class="form-group">
              <label class="form-label social-field-label" for="telegram">
                <span class="social-field-icon" aria-hidden="true"><social-field-icon name="telegram" /></span>
                Telegram
              </label>
              <input type="text" id="telegram" v-model="telegram" class="input">
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

        
        <div class="form-section" v-if="true == false">
          <h2>Other platforms</h2>

          <div class="form-group">
            <label for="airbnb_com">AirBnB</label>
            <input type="text" id="airbnb_com" v-model="airbnb_com" class="input">
          </div>
          <div class="form-group">
            <label for="coliving_com">Coliving.com</label>
            <input type="text" id="coliving_com" v-model="coliving_com" class="input">
          </div>
          <div class="form-group">
            <label for="booking_com">Booking.com</label>
            <input type="text" id="booking_com" v-model="booking_com" class="input">
          </div>
        </div>


        <div class="form-section" v-if="pageType == 'edit'">
          <h2>Lifestyle Match</h2>

          Lifestyle Match helps you find coliving spaces and roommates that match your lifestyle.
          <br><br>
          
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

        
        <div class="form-section" v-if="pageType == 'edit'">
          <h2>Profile visibility</h2>
          <div class="form-group" style="text-align:left;">
            <input type="radio" id="public" v-model="private" value="0" style="width:13px;">
            <label for="public">Public</label>
            <br>
            <input type="radio" id="private" v-model="private" value="1" style="width:13px;">
            <label for="private">Private</label>
          </div>
          
          <div style="height:10px;"></div>
          <a v-if="!username && pageType != 'create'" :href="'/space/'+uid" target="_blank">View profile</a>
          <a v-else-if="pageType != 'create'" :href="'/space/'+username" target="_blank">View profile</a>
        </div>

        <div style="height:20px"></div>

        <div v-if="pageType == 'create'" class="space-create-actions">
          <button v-if="activeCreateStepIndex > 0" type="button" class="btn btn-md btn-ghost space-create-back" :disabled="isUpdating" @click="previousCreateStep">Back</button>
          <button v-if="!isLastCreateStep" type="button" class="btn btn-md btn-join" @click="nextCreateStep">Continue</button>
          <button v-else type="submit" :disabled="isUpdating" class="btn btn-md btn-join">Submit space</button>
        </div>
        <div v-else>
          <button :disabled="isUpdating" type="submit" class="btn btn-md btn-join">Update</button>
          &nbsp;&nbsp;
          <button type="button" @click="deleteSpace" class="btn btn-sm btn-danger">Delete</button>
        </div>
        
      </form>

      <div style="height:20px;"></div>
    </div>
  </div>
</template>

<script>

import MapSpacesSearchable from '../map/MapSpacesSearchable.vue';
import FormCountriesDropDown from './FormCountriesDropDown.vue';
import FormGoogleAutoComplete from './FormGoogleAutoComplete.vue';
import SpacePhotoManager from './SpacePhotoManager.vue';
import { CATEGORY_ORDER, CATEGORY_META, SUBCATEGORY_META } from '../spaceTaxonomy.js';
import { spacePhotoUrl } from '../mediaUrls.js';
import SocialFieldIcon from '../components/SocialFieldIcon.vue';

export default {
  components: {
    MapSpacesSearchable,
    FormCountriesDropDown,
    FormGoogleAutoComplete,
    SpacePhotoManager,
    SocialFieldIcon,
  },
  data() {
    return {
      isUpdating: false,
      json: '',
      activeCreateStep: 'general',
      showCreateSocials: false,

      uid: '',
      name: '',
      username: '',
      description: '',
      governance_type: 'hosted',
      category: '',
      subcategory: '',
      country: '',
      country_code: '',
      city: '',
      email: '',
      website: '',
      phone: '',
      whatsapp: '',
      social: '5',
      noise_level: '5',
      cleanliness: '5',
      privacy: '5',
      outdoor_activities: '5',
      pets: '5',
      work_life_balance: '5',
      cooking: '5',
      physical_activity: '5',
      amenities: '',
      photos: [],
      
      google_url: '',
      longitude: '',
      latitude: '',

      twitter: '',
      facebook: '',
      instagram: '',
      youtube: '',
      tiktok: '',
      pinterest: '',
      linkedin: '',
      linktree: '',
      telegram: '',
      github: '',
      substack: '',
      medium: '',
      
      booking_com: '',
      coliving_com: '',
      airbnb_com: '',

      address: '',
      formatted_address: '',

      userLoggedIn: false,

      coliveFits: {
        orgSpont: { name: 'organizedSpontaneous', value: '5', labels: { left: 'Organized', right: 'Spontaneous' } },
        quietLoud: { name: 'quietLoud', value: '5', labels: { left: 'Quiet', right: 'Loud' } },
        earlyNight: { name: 'earlyBirdNightOwl', value: '5', labels: { left: 'Early Bird', right: 'Night Owl' } },
        workFun: { name: 'workFun', value: '5', labels: { left: 'Work', right: 'Fun' } },
        expTrad: { name: 'experimentalTraditional', value: '5', labels: { left: 'Experimental', right: 'Traditional' } },
        multiLocal: { name: 'multiculturalLocal', value: '5', labels: { left: 'Multicultural', right: 'Local' } },
      },
      
      private: 0,

      errors: false,
      error1: false,
      error2: false,
      error3: false,
      error4: false,
      error5: false,
      error6: false,
      errorPhoto: '',

      userId: null,
      created: false,
    }
  },
  
  computed: {
    pageType() {
      if (this.$route.name === 'SpaceEdit') {
        return 'edit';
      } else {
        return 'create';
      }
    },
    createSteps() {
      return [
        { key: 'general', label: 'General info' },
        { key: 'contact', label: 'Contact' },
        { key: 'location', label: 'Location' },
        { key: 'photos', label: 'Photos' },
      ];
    },
    activeCreateStepIndex() {
      return this.createSteps.findIndex(step => step.key === this.activeCreateStep);
    },
    isLastCreateStep() {
      return this.activeCreateStepIndex === this.createSteps.length - 1;
    },
    categoryOptions() {
      return CATEGORY_ORDER
        .filter(slug => CATEGORY_META[slug])
        .map(slug => ({
          value: slug,
          label: CATEGORY_META[slug].label,
        }));
    },
    isSelfManagedCategory() {
      return this.category === 'self-managed';
    },
    categoryDescription() {
      if (!this.category) return '';
      return CATEGORY_META[this.category]?.description || '';
    },
    subcategoryDescription() {
      if (!this.category || !this.subcategory) return '';
      return SUBCATEGORY_META[this.category]?.[this.subcategory]?.description || '';
    },
    availableSubcategories() {
      const subs = SUBCATEGORY_META[this.category] || {};
      const out = {};
      for (const [slug, meta] of Object.entries(subs)) {
        out[slug] = meta.label;
      }
      return out;
    }
  },

  mounted() {
    if (window.mate) {
      this.userId = window.mate.id;
    }
    this.userLoggedIn = window.userLoggedIn;

    const fetchSpace = async () => {
      const { country, uid } = this.$route.params;
      try {
        const response = await axios.get(`/api/space/${uid}`);
        let m = response.data.space;

        this.name = m.name; 
        if (m.username == null) {
          this.username = ''; 
        }
        else {
          this.username = m.username; 
        }
        this.description = m.description;
        this.governance_type = m.governance_type || 'hosted';
        this.category = m.category || '';
        this.subcategory = m.subcategory || '';

        this.avatar = m.avatar;
        this.country = m.country;
        this.city = m.city;
        this.email = m.email;
        this.website = m.website;
        this.phone = m.phone;
        this.whatsapp = m.whatsapp;
        this.social = m.social;
        this.noise_level = m.noise_level;
        this.cleanliness = m.cleanliness;
        this.privacy = m.privacy;
        this.outdoor_activities = m.outdoor_activities;
        this.pets = m.pets;
        this.work_life_balance = m.work_life_balance;
        this.cooking = m.cooking;
        this.physical_activity = m.physical_activity;
        this.amenities = m.amenities;
        // this.location = m.location;

        this.google_url = m.google_url;
        this.longitude = m.longitude;
        this.latitude = m.latitude;

        this.private = m.private;
        
        if (m.address != '') {
          this.address = m.address;
        }
        else if (m.formatted_address != '') {
          this.address = m.formatted_address;
        }

        this.country_code = m.country_code;

        this.uid = this.$route.params.uid;
        
        this.twitter = m.twitter;
        this.facebook = m.facebook;
        this.instagram = m.instagram;
        this.youtube = m.youtube;
        this.tiktok  = m.tiktok;
        this.pinterest  = m.pinterest;
        this.linkedin  = m.linkedin;
        this.linktree = m.linktree;
        this.telegram = m.telegram;
        this.github = m.github;
        this.substack = m.substack;
        this.medium = m.medium;
      
        this.booking_com = m.booking_com;
        this.airbnb_com = m.airbnb_com;
        this.coliving_com = m.coliving_com;

        this.coliveFits['orgSpont'].value = m.orgSpont;
        this.coliveFits['quietLoud'].value = m.quietLoud;
        this.coliveFits['earlyNight'].value = m.earlyNight;
        this.coliveFits['workFun'].value = m.workFun;
        this.coliveFits['expTrad'].value = m.expTrad;
        this.coliveFits['multiLocal'].value = m.multiLocal;

        this.photos = Object.values(m.photos || {})
          .sort((a, b) => {
            const aPos = a.pos > 0 ? a.pos : 999999;
            const bPos = b.pos > 0 ? b.pos : 999999;
            if (aPos !== bPos) return aPos - bPos;
            return (a.id || 0) - (b.id || 0);
          })
          .map((photo) => ({
            key: 'p_' + photo.id,
            id: photo.id,
            url: spacePhotoUrl(photo.filename),
            file: null,
          }));

      } catch (error) {
        console.error(error);
      }
    };

    if (this.userLoggedIn && this.pageType == 'edit') {
      fetchSpace();
    }
  },  

  watch: {
    json(newValue) {
      try {
        const data = JSON.parse(newValue);
        this.name = data.name || '';
        this.username = data.username || '';
        this.description = data.description || '';
        this.email = data.email || '';
        this.website = data.website || '';
        this.phone = data.phone || '';
        this.whatsapp = data.whatsapp || '';
        this.twitter = data.twitter || '';
        this.facebook = data.facebook || '';
        this.instagram = data.instagram || '';
        this.youtube = data.youtube || '';
        this.tiktok = data.tiktok || '';
        this.pinterest = data.pinterest || '';
        this.linkedin = data.linkedin || '';
        this.linktree = data.linktree || '';
        this.telegram = data.telegram || '';
        this.github = data.github || '';
        this.substack = data.substack || '';
        this.medium = data.medium || '';
        this.google_url = data.google_url || '';
        this.longitude = data.longitude || '';
        this.latitude = data.latitude || '';
        this.address = data.address || '';
      } catch (e) {
        console.error('Invalid JSON:', e);
      }
    }
  },

  methods: {
    isSectionVisible(section) {
      return this.pageType == 'edit' || this.activeCreateStep === section;
    },

    clearValidationErrors() {
      this.errors = false;
      this.error1 = false;
      this.error2 = false;
      this.error3 = false;
      this.error4 = false;
      this.error5 = false;
      this.error6 = false;
      this.errorPhoto = '';
    },

    validateCreateStep(step) {
      this.clearValidationErrors();

      if (step === 'general') {
        if (!this.name) {
          this.errors = true;
          this.error1 = true;
          return false;
        }

        if (!this.category) {
          this.errors = true;
          this.error6 = true;
          return false;
        }
      }

      if (step === 'contact' && !this.email) {
        this.errors = true;
        this.error2 = true;
        return false;
      }

      if (step === 'location' && !this.address) {
        this.errors = true;
        this.error4 = true;
        return false;
      }

      return true;
    },

    nextCreateStep() {
      if (!this.validateCreateStep(this.activeCreateStep)) return;

      const nextStep = this.createSteps[this.activeCreateStepIndex + 1];
      if (nextStep) {
        this.activeCreateStep = nextStep.key;
        this.clearValidationErrors();
      }
    },

    previousCreateStep() {
      const previousStep = this.createSteps[this.activeCreateStepIndex - 1];
      if (previousStep) {
        this.activeCreateStep = previousStep.key;
        this.clearValidationErrors();
      }
    },

    updateCountry(c) {
      // console.log(c)
      this.country_code = c.code;
      this.country = c.name;
    },

    addressUpdate(address) {
      // console.log('aa', address)
      this.address = address.name;
    },

    locationUpdate(location) {
      this.latitude = location.latitude;
      this.longitude = location.longitude;
    },
    
    handleLocationUpdated(locationFormatted, country, country_code, city, coordinates) {
      // console.log(locationFormatted, country, city, coordinates);
      this.address = locationFormatted;
      this.country = country;
      this.city = city;
      this.country_code = country_code;
      this.latitude = coordinates.lat;
      this.longitude = coordinates.lng;
    },

    onCategoryChange() {
      this.subcategory = '';
      this.governance_type = this.isSelfManagedCategory ? 'self-managed' : 'hosted';
    },

    submitForm() {
      // Form validation
      this.clearValidationErrors();

      if (this.pageType == 'create') {
        for (const step of ['general', 'contact', 'location']) {
          if (!this.validateCreateStep(step)) {
            this.activeCreateStep = step;
            return;
          }
        }
      }
      
      if (!this.name) {
        this.errors = true;
        return this.error1 = true;
      }
      else if (!this.category) {
        this.errors = true;
        return this.error6 = true;
      }
      else if (!this.address) {
        this.errors = true;
        return this.error4 = true;
      }
      else if (!this.email) {
        this.errors = true;
        return this.error2 = true;
      } 
      // else if (!/\S+@\S+\.\S+/.test(this.email)) {
      //   this.errors = true;
      //   return this.error2 = true;
      // }

      this.isUpdating = true;
      let formData = new FormData();
      this.governance_type = this.isSelfManagedCategory ? 'self-managed' : 'hosted';

      formData.append('name', this.name);
      formData.append('username', this.username);
      formData.append('description', this.description);
      formData.append('governance_type', this.governance_type);
      formData.append('category', this.category);
      formData.append('subcategory', this.subcategory || '');
      
      formData.append('country', this.country);
      formData.append('city', this.city);
      formData.append('email', this.email);
      formData.append('website', this.website);
      formData.append('phone', this.phone);
      formData.append('whatsapp', this.whatsapp);
      formData.append('social', this.social);
      formData.append('noise_level', this.noise_level);
      formData.append('cleanliness', this.cleanliness);
      formData.append('privacy', this.privacy);
      formData.append('outdoor_activities', this.outdoor_activities);
      formData.append('pets', this.pets);
      formData.append('work_life_balance', this.work_life_balance);
      formData.append('cooking', this.cooking);
      formData.append('physical_activity', this.physical_activity);
      formData.append('amenities', this.amenities);
      // formData.append('location', this.location);
      formData.append('google_url', this.google_url);
      formData.append('longitude', this.longitude);
      formData.append('latitude', this.latitude);
      formData.append('uid', this.uid);
      formData.append('country_code', this.country_code);

      formData.append('twitter', this.twitter);
      formData.append('facebook', this.facebook);
      formData.append('instagram', this.instagram);
      formData.append('youtube', this.youtube);
      formData.append('tiktok', this.tiktok);
      formData.append('pinterest', this.pinterest);
      formData.append('linkedin', this.linkedin);
      formData.append('linktree', this.linktree);
      formData.append('telegram', this.telegram);
      formData.append('github', this.github);
      formData.append('substack', this.substack);
      formData.append('medium', this.medium);

      formData.append('coliving_com', this.coliving_com);
      formData.append('booking_com', this.booking_com);
      formData.append('airbnb_com', this.airbnb_com);

      formData.append('address', this.address);
      formData.append('formatted_address', this.formatted_address);

      formData.append('photos_meta', JSON.stringify(this.photos.map((photo) => ({
        key: photo.key,
        id: photo.id || null,
      }))));

      this.photos.forEach((photo) => {
        if (photo.file) {
          formData.append(`photo_files[${photo.key}]`, photo.file);
        }
      });

      formData.append('private', this.private);

      for (let key in this.coliveFits) {
        formData.append(key, this.coliveFits[key].value);
      }

      axios.post('/api/space', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(response => {
        if(response.data.message == 'success') {
          if (this.pageType == 'edit') {
            if (!this.username) {
              this.$router.push('/space/'+this.uid)
            }
            else {
              this.$router.push('/space/'+this.username)
            } 
          }
          else {
            this.created = true;
          }
        }
        else if (response.data == 'error_name') {
          this.errors = true;
          this.error1 = true;
          this.isUpdating = false;
        }
        else if (response.data == 'error_email') {
          this.errors = true;
          this.error2 = true;
          this.isUpdating = false;
        }
      })
      .catch(error => {
        console.log(error.response);
        this.isUpdating = false;
        this.errors = true;
        const photoError = error.response?.data?.error;
        if (photoError) {
          this.errorPhoto = photoError;
        } else {
          this.error5 = true;
        }
      });
    },

    deleteSpace() {
      if (confirm("Are you sure?")) {
        axios.delete('/api/space/'+this.$route.params.uid+'/delete')
        .then(response => {
          if (response.data.message = 'Space deleted successfully') {
            this.$router.push('/')
          }
        })
        .catch(error => console.log(error))
      }
    },

  }
}
</script>

<style scoped>
.space-create-progress {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin-bottom: 22px;
}

.space-create-step {
  display: flex;
  align-items: center;
  gap: 9px;
  min-height: 44px;
  padding: 9px 12px;
  border: 1px solid #e4e4e4;
  border-radius: 999px;
  background: #fff;
  color: #555;
  font-size: 13px;
  font-weight: 600;
}

.space-create-step.active {
  border-color: #00abff;
  background: aliceblue;
  color: #0086da;
}

.space-create-step.complete {
  border-color: #bfe8fb;
  color: #0086da;
}

.space-create-step-number {
  display: inline-grid;
  place-items: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #f1f1f1;
  color: inherit;
  flex-shrink: 0;
}

.space-create-step.active .space-create-step-number,
.space-create-step.complete .space-create-step-number {
  background: #00abff;
  color: #fff;
}

.space-create-expand {
  margin-top: 18px;
}

.self-managed-note {
  max-width: 650px;
  margin-top: 4px;
  margin-bottom: 15px;
  padding: 12px 14px;
  border: 1px solid #bfe8fb;
  border-radius: 10px;
  background: aliceblue;
  color: #36556d;
  font-size: 14px;
  line-height: 1.5;
}

.form-error-summary {
  margin-bottom: 16px;
  padding: 12px 14px;
  border: 1px solid #f2c1bd;
  border-radius: 10px;
  background: #fff6f5;
  color: #c0392b;
  font-size: 14px;
  font-weight: 600;
  line-height: 1.5;
}

.space-social-grid {
  margin-top: 12px;
}

.space-create-actions {
  display: flex;
  gap: 10px;
  align-items: center;
  justify-content: flex-start;
}

.space-create-actions .btn {
  margin: 0;
}

@media screen and (max-width: 700px) {
  .space-create-progress {
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
  }

  .space-create-step {
    justify-content: center;
    min-height: 42px;
    padding: 8px;
    border-radius: 999px;
  }

  .space-create-step span:last-child {
    display: none;
  }

  .space-create-actions {
    align-items: center;
    flex-direction: row;
    justify-content: flex-start;
  }

  .space-create-actions .btn-join {
    flex: 1;
  }

}
</style>
