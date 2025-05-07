<template>
  <widget style="text-align:center;" :title="'SIGN UP'">
    <div class="widget-padding-25" >
      
      <form @submit.prevent="submit">
        
        <div class="auth-label">Name</div>
        <input id="name" type="text" class="auth-input" v-model="form.name" required />
        
        <div class="auth-label">Email</div>
        <input id="email" type="email" class="auth-input" v-model="form.email" required />
        
        <div class="auth-label">Password</div>
        <input id="password" type="password" class="auth-input" v-model="form.password" required autocomplete="new-password" />
        
        <div class="auth-label">Confirm Password</div>
        <input id="password_confirmation" type="password" class="auth-input" v-model="form.password_confirmation" required autocomplete="new-password" />
        
        <!-- <div class="auth-label">Current location</div>
        <select id="country" class="auth-input" v-model="form.country" required>
          <option value="" disabled>Choose country</option>
          <option v-for="country in countries" :key="country.code" :value="country.name">
            {{ country.name }}
          </option>
        </select> -->
        
        <div style="height:5px;"></div>
        
        <validation-errors :errors="errors" v-if="errors" />
        <div style="height:20px;" v-if="errors"></div>
        
        <div style="height:5px;"></div>
        
        <div class="appify-checkbox" style="width:155px;margin:0 auto;">
          <input id="newsletter" type="checkbox" name="newsletter" class="appify-input-checkbox" v-model="form.newsletter" />
          <label for="newsletter">
            <div class="checkbox-box" style="margin-top:-1px;">  
              <svg><use xlink:href="#checkmark" /></svg>
            </div> 
            Coliving App News
          </label>
        </div>
        
        <div style="height:15px;"></div>
        
        <button class="auth-button" :class="{ 'auth-button-disabled': form.processing }" :disabled="form.processing">
          Sign up
        </button>
      </form>
      
      <div style="height:25px;"></div>
      
      <router-link to="/login" class="blacklink"><b>Login</b></router-link>
    </div>
  </widget>
</template>

<script>
import Widget from '../../widget/Widget.vue';
import ValidationErrors from './ValidationErrors.vue';

export default {
  components: {
    Widget,
    ValidationErrors
  },
  
  data() {
    return {
      form: {
        email: '',
        name: '',
        password: '',
        password_confirmation: '',
        terms: false,
        processing: null,
        newsletter: true,
      },
      errors: null,
    }
  },
  
  computed: {
    hasErrors() {
      return Object.keys(this.errors).length > 0;
    },
  },
  
  methods: {            
    formProcessing() {
      if (this.form.processing) setTimeout(() => { this.form.processing = false; }, 100)
      else this.form.processing = true;
    },
    submit() {
      this.errors = null;
      this.formProcessing();
      
      axios({
        method: 'post',
        url: '/register',
        data: this.form
      })
      .then((response) => {
        // console.log(response)
        window.location = '/email/verify';
      })
      .catch((error) => {
        this.formProcessing();
        this.errors = error.response.data.errors;
      });
    }
  }
}
</script>