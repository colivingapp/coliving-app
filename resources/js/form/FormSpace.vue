<template>
  <div v-if="created" class="content">
    <h1>Your Space Is Pending Approval</h1>
    <br>
    Your new space has been successfully created and is currently under review. Approval typically takes up to 24 hours.
    <br><br>
    Once approved, you will receive an email notification, and your space profile will go live!
  </div>
  <div v-else>
    <div v-if="!userLoggedIn" class="content">
      <h1>Add New Coliving Space</h1>
      Add a new coliving space to Coliving App to enhance your space's visibility, connect directly with potential guests, and manage coliving experiences through our platform.

      <div style="height:30px;"></div>

      Log in to continue
      <div style="height:10px;"></div>
      <router-link to="/login"><button class="auth-button">Login</button></router-link>
      
      <div style="height:30px;"></div>

      Create a free account
      <div style="height:10px;"></div>
      <router-link to="/register"><button class="auth-button">Sign Up</button></router-link> 
    </div>
    <div v-else class="content">
      <h1 v-if="pageType == 'create'">Add New Coliving Space</h1>
      <h1 v-else>Edit Coliving Space</h1>
      
      <div style="height:10px;"></div>

      <form @submit.prevent="submitForm">

        <div class="form-section">
          <span v-if="userId == 1 && pageType == 'create' && true == false">
            <textarea v-model="json"></textarea>
          </span>

          <h2>General info</h2>

          <div class="form-group">
            <label for="email">Name <span style="color:red;">*</span></label>
            <input type="text" id="name" v-model="name" class="input">
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

        <div class="form-section">
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
        </div>


        <div class="form-section">
          <h2>Location</h2>
          
          <label>Address <span style="color:red;">*</span></label>
          <FormGoogleAutoComplete :address="address" @location-updated="handleLocationUpdated" />

          <div style="height:10px;"></div>
          <label for="google_url">Google Maps URL</label>
          <input type="text" id="google_url" v-model="google_url" class="input">
          
          <div style="height:20px;"></div>
          <MapSpacesSearchable :location="{ 'latitude': latitude, 'longitude': longitude }"   />
        </div>


        <div class="form-section">
          <h2>Photos</h2>

          <div>
            <input type="file" id="photos" v-on:change="onFileSelected" multiple>
          </div>
          <div style="height:20px;"></div>
          
          <div v-if="imageUrls.length >= 2">
            Drag and drop to change the order of your photos.
            <div style="height:20px;"></div>
          </div>

          <draggable v-model="imageUrls" class="thumbnails" item-key="id" @start="drag=true" @end="drag=false" @update="updatePhotoPositions">
            <template #item="{element, index}">
              <div style="position:relative;max-width:250px;">
                <img :src="element.url" height="100">
                <div class="delete-button" @click="deletePhoto(index)">X</div>
                <div style="height:10px;"></div>
              </div>
            </template>
          </draggable>
        </div>
        
        <div class="form-section" v-if="pageType == 'edit'">
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
          <div class="form-group">
            <label for="linktree">Linktree</label>
            <input type="text" id="linktree" v-model="linktree" class="input">
          </div>
          <div class="form-group">
            <label for="telegram">Telegram</label>
            <input type="text" id="telegram" v-model="telegram" class="input">
          </div>
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
          <h2>Hub</h2>

          Hub is where your space mates gather. Provide some general information about your space, like cleaning timetable, house rules, entrance codes, contact info, agenda, etc. This information will be visible under the Info section in your space hub.
          <br><br>
          <div>
            <label for="info">Space info for the Hub</label>
            <textarea id="info" v-model="info" class="textarea" placeholder="Hub info" style="height:200px;"></textarea>
          </div>
        </div>


        <div class="form-section" v-if="pageType == 'edit'">
          <h2>CoMatch Score</h2>

          CoMatch Score helps you find coliving spaces and roommates that match your lifestyle.
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
          <a v-if="!username && pageType != 'create'" :href="'/space/'+uid" target="_blank">View profile</a>
          <a v-else-if="pageType != 'create'" :href="'/space/'+username" target="_blank">View profile</a>
        </div>


        <div v-if="errors" style="color:red;font-weight:bold;">
          <br>
          <br>
          <span v-if="error1">* The name is required. Please enter your space name to proceed.<br></span>
          <span v-if="error2">* Email address is required for communication. Please enter your space email.<br></span>
          <span v-if="error3">* Country is required. Please select the country where your space is located.<br></span>
          <span v-if="error4">* Address is required. Please enter the address of your space.</span>
          <span v-if="error5">* Unknown error. Please contact site admin.</span>
        </div>
        
        <div style="height:20px"></div>

        <div v-if="pageType == 'create'" >
          <button type="submit" :disabled="isUpdating" class="action-button button-post">Submit</button>
        </div>
        <div v-else>
          <button :disabled="isUpdating" type="submit" class="action-button button-post">Update</button>
          &nbsp;&nbsp;
          <button type="button" @click="deleteSpace" class="button">Delete</button>
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
import draggable from 'vuedraggable';

export default {
  components: {
    MapSpacesSearchable,
    FormCountriesDropDown,
    draggable,
    FormGoogleAutoComplete,
  },
  data() {
    return {
      isUpdating: false,
      json: '',

      uid: '',
      name: '',
      username: '',
      description: '',
      info: '',
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
      files: [],
      imageUrls: [],
      
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
      
      drag: false,
      private: 0,

      errors: false,
      error1: false,
      error2: false,
      error3: false,
      error4: false,
      error5: false,

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
        this.info = m.info;

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

        // Set the existing photos in the imageUrls array
        this.imageUrls = Object.values(m.photos || {})
        .sort((a, b) => a.pos - b.pos)
        .map((photo, index) => ({
          id: photo.id,
          pos: photo.pos,
          url: `/storage/spaces/${photo.filename}`,
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
    files: {
      handler: function(newFiles) {
        for (let i = 0; i < newFiles.length; i++) {
          let file = newFiles[i]
          let reader = new FileReader()
          reader.onload = (event) => {
            file.preview = event.target.result
          }
          reader.readAsDataURL(file)
        }
      },
      deep: true
    },
    
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
    updatePhotoPositions(event) {
      // Update 'pos' property based on the new order
      this.imageUrls.forEach((photo, index) => {
        photo.pos = index + 1;
      });
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

    submitForm() {
      // Form validation
      this.errors = false;
      this.error1 = false;
      this.error2 = false;
      this.error3 = false;
      this.error4 = false;
      
      if (!this.name) {
        this.errors = true;
        return this.error1 = true;
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

      formData.append('name', this.name);
      formData.append('username', this.username);
      formData.append('description', this.description);
      formData.append('info', this.info);
      
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

      formData.append('imageUrls', JSON.stringify(this.imageUrls));

      formData.append('private', this.private);

      for (let key in this.coliveFits) {
        formData.append(key, this.coliveFits[key].value);
      }

      // append all the selected files to the form data as an array
      this.files.forEach(file => {
        formData.append('photos[]', file);
      }); 

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
            // console.log(response.data.space.uid);

            if (this.userId == 1 || this.userId == 18) {
              this.$router.push('/space/'+response.data.space.uid)
            }
            else {
              this.created = true;
            }
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
        this.error5 = true;
        // show an error message
      });
    },

    onFileSelected(event) {
      const selectedFiles = Array.from(event.target.files);

      // Check file format and size before updating files
      const invalidFiles = this.validateFiles(selectedFiles);

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

      // Proceed with updating files
      this.files = selectedFiles;

      // Check if there are existing photos with pos not equal to 0
      const hasExistingPhotos = this.imageUrls.some(photo => photo.pos !== 0);

      if (hasExistingPhotos) {
        // Update imageUrls
        const startPos = Math.max(...this.imageUrls.map(photo => photo.pos)) + 1;

        this.files.forEach((file, index) => {
          const reader = new FileReader();
          reader.onload = () => {
            let res = {
              url: reader.result,
              pos: startPos + index,
            };
            this.imageUrls.push(res);
          };
          reader.readAsDataURL(file);
        });
      } else {
        // No existing photos with pos not equal to 0, set pos to 0 for new files
        this.files.forEach((file, index) => {
          const reader = new FileReader();
          reader.onload = () => {
            let res = {
              url: reader.result,
              pos: 0,
            };
            this.imageUrls.push(res);
          };
          reader.readAsDataURL(file);
        });
      }
    },

    validateFiles(files) {
      const invalidFiles = [];

      // Check file format and size
      files.forEach(file => {
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
          invalidFiles.push({ name: file.name, format: true });
        } else if (file.size > 4 * 1024 * 1024) {
          invalidFiles.push({ name: file.name, size: true });
        }
      });

      // Check maximum number of files
      if (this.imageUrls.length + files.length > 20) {
        invalidFiles.push({ count: this.imageUrls.length + files.length });
      }

      return invalidFiles;
    },

    deleteSpace() {
      if (this.userId == 1) {
        axios.delete('/api/space/'+this.$route.params.uid+'/delete')
        .then(response => {
          if (response.data.message = 'Space deleted successfully') {
            this.$router.push('/')
          }
        })
        .catch(error => console.log(error))
      }
      else if (confirm("Are you sure?")) {
        axios.delete('/api/space/'+this.$route.params.uid+'/delete')
        .then(response => {
            if (response.data.message = 'Space deleted successfully') {
              this.$router.push('/')
            }
          }
        )
        .catch(error => console.log(error))
      }
    },

    deletePhoto(index) {
      this.imageUrls.splice(index, 1);
    },

  }
}
</script>
