<template>
  <widget style="text-align:center;" :title="'LOGIN'">
    <div  class="widget-padding-25" >
      
      <form @submit.prevent="submit">
        <div class="auth-label">Email</div>
        <input id="email" type="email" class="auth-input" v-model="form.email" required autofocus />
        
        
        <div class="auth-label">Password</div>
        <input id="password" type="password" class="auth-input" v-model="form.password" required />
        
        <div style="height:5px;"></div>
        
        <div class="appify-checkbox" style="width:127px;margin:0 auto;padding-left:4px;">
          <input id="rememberme" type="checkbox" name="rememberme" class="appify-input-checkbox" v-model="form.remember"/>
          <label for="rememberme">
            <div class="checkbox-box" style="margin-top:-1px;">  
              <svg><use xlink:href="#checkmark" /></svg>
            </div> 
            Remember me
          </label>
        </div>
        
        <div style="height:10px;"></div>
        
        <validation-errors :errors="errors" v-if="errors" />
        <div style="height:20px;" v-if="errors"></div>
        
        <button class="auth-button" :class="{ 'auth-button-disabled': form.processing }" :disabled="form.processing">
          Log in
        </button>
        
      </form>
      
      <div style="height:25px;"></div>
      <router-link to="/forgot-password" class="blacklink">Forgot password?</router-link>
      
      <div style="text-align:center;">  
        <div style="height:30px;"></div>
        <router-link to="/register" class="blacklink"><b>Sign Up</b></router-link>
      </div>
      
    </div>
  </widget>
</template>

<script>
import Widget from '../../widget/Widget.vue';
import ValidationErrors from './ValidationErrors.vue'

export default {
  components: {
    Widget,
    ValidationErrors
  },
  
  computed: {
    hasErrors() {
      return Object.keys(this.errors).length > 0;
    },
  },
  
  data() {
    return {
      form: {
        email: '',
        password: '',
        remember: true,
        processing: null,
      },
      errors: null,
    }
  },
  
  methods: {            
    formProcessing() {
      if (this.form.processing) setTimeout(() => { this.form.processing = false; }, 100)
      else this.form.processing = true;
    },
    submit() {
      this.formProcessing();
      this.errors = null;
      
      axios({
        method: 'post',
        url: '/login',
        data: this.form
      })
      .then((response) => {
        // Redirect user after login
        if (response.statusText == 'OK' || response.statusText == '') {
          // window.location = '/mate/update';
          window.location = '/';
        }
      })
      .catch((error) => {
        // console.log(error.response);
        this.formProcessing();
        
        if (error.response.data.message == 'Too Many Attempts.') {
          let errors = {}
          errors.email = ['Too many attempts, please wait.'];
          this.errors = errors;
        }
        else {
          console.log(error.response.data.errors)
          this.errors = error.response.data.errors;
        }
      });
    }
  }
}
</script>