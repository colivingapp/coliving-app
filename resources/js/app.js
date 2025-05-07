import { createRouter, createWebHistory } from 'vue-router';
import routes from './routes.js';

const router = createRouter({
    history: createWebHistory(),
    routes: routes.routes,
    scrollBehavior(to, from, savedPosition) {
        return { top: 0 }
    },
});

router.beforeEach(async (to, from, next) => {
    // Redirect unverified users to email verification view
    if (userVerified === '' && to.name !== 'EmailVerify') {
        next('/email/verify');
    }
    // Redirect loggedIn users from guest auth to account view
    else if (userVerified !== null) {
        if (to.name === 'Register' || to.name === 'ForgotPassword' || to.name === 'Login') {
            next('/account');
        }
    }
    // SEO title tags
    if (to.name !== 'space' && to.name !== 'spaces-by-country') {
        if (to.meta.title != null) {
            document.title = to.meta.title;
        }
    }
    next();
});


import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import { createApp } from 'vue'
import App from './App.vue'

const app = createApp(App).use(router)
app.mount('#app')