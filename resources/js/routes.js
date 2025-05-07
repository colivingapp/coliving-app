import Home from './Home.vue';
import Page from './Page.vue';

import Account from './account/Account.vue';
import AccountSettings from './account/AccountSettings.vue';
import AccountLogin from './account/Auth/Login.vue';
import AccountRegister from './account/Auth/Register.vue';
import ForgotPassword from './account/Auth/ForgotPassword.vue';
import ResetPassword from './account/Auth/ResetPassword.vue';
import VerifyEmail from './account/Auth/VerifyEmail.vue';

import FormMate from './form/FormMate.vue';
import FormSpace from './form/FormSpace.vue';

import ClaimSpace from './pages/SpaceClaim.vue';
import ManageSpace from './pages/SpaceManage.vue';
import Bookmarks from './pages/Bookmarks.vue';
import Spaces from './pages/Spaces.vue';
import SpacesCities from './pages/SpacesCities.vue';
import Search from './pages/Search.vue';
import MySpaces from './pages/SpacesMy.vue';
import SpacesByCountry from './pages/SpacesByCountry.vue';
import SpacesByCity from './pages/SpacesByCity.vue';
import Space from './pages/Space.vue';
import Mate from './pages/Mate.vue';
import Mates from './pages/Mates.vue';

import Inbox from './inbox/Inbox.vue';
import Conversation from './inbox/Main.vue';

import HubAll from './hub/HubAll.vue';
import HubSpace from './hub/Hub.vue';


const routes = [

    // Views
    {
        path: '/',
        component: Home,
        name: 'Home',
        meta: {
            title: 'Coliving App - Experience the Coliving Revolution'
        },
    },

    // Pages
    {
        path: '/about',
        component: Page,
        name: 'About',
        meta: {
            title: 'Coliving App - About'
        },
    },
    {
        path: '/privacy',
        component: Page,
        name: 'Privacy',
        meta: {
            title: 'Coliving App - Privacy Policy'
        },
    },
    {
        path: '/terms',
        component: Page,
        name: 'Terms',
        meta: {
            title: 'Coliving App - Terms & Conditions'
        },
    },
    {
        path: '/contacts',
        component: Page,
        name: 'Contacts',
        meta: {
            title: 'Coliving App - Contacts'
        },
    },

    // Account
    {
        path: '/account',
        component: Account,
        name: 'Account',
        meta: {
            title: 'Coliving App - Account'
        },
    },
    {
        path: '/account/settings',
        component: AccountSettings,
        name: 'AccountSettings',
        meta: {
            title: 'Coliving App - Account Settings'
        },
    },
    {
        path: '/login',
        component: AccountLogin,
        name: 'Login',
        meta: {
            title: 'Coliving App - Login'
        },
    },
    {
        path: '/register',
        component: AccountRegister,
        name: 'Register',
        meta: {
            title: 'Coliving App - Sign Up for a Free Account'
        },
    },
    {
        path: '/forgot-password',
        component: ForgotPassword,
        name: 'ForgotPassword',
        meta: {
            title: 'Coliving App - Password Recovery'
        },
    },
    {
        path: '/password/reset/:token',
        component: ResetPassword,
        name: 'ResetPassword',
        meta: {
            title: 'Coliving App - Create a New Password'
        },
    },
    {
        path: '/email/verify',
        component: VerifyEmail,
        name: 'EmailVerify',
        meta: {
            title: 'Coliving App - Verify Your Email Address'
        },
    },
    {
        path: '/mate/update',
        component: FormMate,
        name: 'MateUpdate',
        meta: {
            title: 'Coliving App - My Profile'
        },
    },
    {
        path: '/space/new',
        component: FormSpace,
        name: 'CreateNewSpace',
        meta: {
            title: 'Coliving App - Add New Space'
        },
    },

    {
        path: '/search/:searchTerm',
        name: 'Search',
        component: Search,
        meta: {
            title: 'Coliving App - Search'
        },
    },

    {
        path: '/space/:uid',
        name: 'space',
        component: Space,
        meta: {
            title: 'Coliving App'
        },
    },
    
    {
        path: '/space/:uid/edit',
        name: 'SpaceEdit',
        component: FormSpace,
        meta: {
            title: 'Coliving App'
        },
    },

    {
        path: '/space/:uid/hub/chat',
        name: 'HubChat',
        component: HubSpace,
        meta: {
            title: 'Coliving App'
        },
    },

    {
        path: '/space/:uid/hub',
        name: 'HubInfo',
        component: HubSpace,
        meta: {
            title: 'Coliving App'
        },
    },

    {
        path: '/space/:uid/hub/mates',
        name: 'HubMates',
        component: HubSpace,
        meta: {
            title: 'Coliving App'
        },
    },

    {
        path: '/space/:uid/hub/settings',
        name: 'HubSettings',
        component: HubSpace,
        meta: {
            title: 'Coliving App'
        },
    },

    {
        path: '/space/:uid/claim',
        name: 'ClaimSpace',
        component: ClaimSpace,
        meta: {
            title: 'Coliving App'
        },
    },

    {
        path: '/space/:uid/dashboard',
        name: 'ManageSpace',
        component: ManageSpace,
        meta: {
            title: 'Coliving App'
        },
    },
    
    {
        path: '/mates',
        name: 'mates',
        component: Mates,
        meta: {
            title: 'Coliving App - Mates'
        },
    },

    {
        path: '/mate/:mate',
        name: 'mate',
        component: Mate,
        meta: {
            title: null
        },
    },

    {
        path: '/inbox/space/:uid',
        name: 'ConversationMateSpace',
        component: Conversation,
        meta: {
            title: 'Coliving App'
        },
    
    },

    {
        path: '/bookmarks',
        name: 'Bookmarks',
        component: Bookmarks,
        meta: {
            title: 'Coliving App - Bookmarks'
        },
    
    },

    {
        path: '/inbox/mate/:uid',
        name: 'ConversationMateMate',
        component: Conversation,
        meta: {
            title: 'Coliving App'
        },
    
    },

    {
        path: '/inbox/space/:uid/mate/:mate_uid',
        name: 'ConversationSpacePOV',
        component: Conversation,
        meta: {
            title: 'Coliving App'
        },
    
    },

    {
        path: '/my-spaces',
        name: 'my-spaces',
        component: MySpaces,
        meta: {
            title: 'Coliving App - My Spaces'
        },
    },
    {
        path: '/hub',
        name: 'HubAll',
        component: HubAll,
        meta: {
            title: 'Coliving App - Hub'
        },
    },

    {
        path: '/inbox',
        name: 'inbox',
        component: Inbox,
        meta: {
            title: 'Coliving App - Inbox'
        },
    },
    
    {
        path: '/inbox/my-spaces',
        name: 'inboxMySpaces',
        component: Inbox,
        meta: {
            title: 'Coliving App - Inbox | My Spaces'
        },
    },

    {
        path: '/spaces',
        name: 'spaces',
        component: Spaces,
        meta: {
            title: 'Coliving App - Spaces'
        },
    },

    {
        path: '/spaces/:countrySlug',
        name: 'spaces-by-country',
        component: SpacesByCountry,
        meta: {
            title: 'Coliving App - Spaces by Country'
        },
    },

    // {
    //     path: '/spaces/cities',
    //     name: 'spaces-cities',
    //     component: SpacesCities,
    //     meta: {
    //         title: 'Coliving Spaces in Cities - Coliving App'
    //     },
    // },
    
    // {
    //     path: '/spaces/:countrySlug/:citySlug',
    //     name: 'spaces-by-city',
    //     component: SpacesByCity,
    //     meta: {
    //         title: 'Coliving App - Spaces by City'
    //     },
    // }

]

// ADMIN ROUTES ('live' branch only)
// import adminRoutes from './admin/routes_admin.js';
// routes.push(...adminRoutes);
  
export default { routes };