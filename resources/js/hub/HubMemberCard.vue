<template>
  <article class="hub-mate-card" :class="{ 'hub-mate-card-rich': isRich, 'hub-mate-card-deleted': isDeleted }">
    <component :is="profileTag" :to="profileTo" class="hub-mate-card-link">
      <div class="hub-mate-photo">
        <div v-if="isDeleted" class="hub-mate-photo-placeholder">
          <img src="/img/coliving-app-logo-symbol.png" alt="">
        </div>
        <profile-pic v-else :profile_pic="mate.profile_pic" :avatar="mate.avatar" :photo="mate.photo" />
        <span v-if="countryCode" class="hub-mate-flag" :title="countryName(countryCode)">
          <span :class="'fi fi-' + countryCode"></span>
        </span>
        <span class="hub-mate-role" :class="'hub-mate-role-' + roleKind">{{ roleLabel }}</span>
      </div>

      <div class="hub-mate-body">
        <p class="hub-mate-name">{{ mate.displayName }}</p>
        <p v-if="showUsername" class="hub-mate-username">@{{ mate.username }}</p>

        <p v-if="aboutText" class="hub-mate-about">{{ aboutText }}</p>

        <div v-if="lifestyleTags.length" class="hub-mate-tags">
          <span v-for="tag in lifestyleTags" :key="tag" class="hub-mate-tag">{{ tag }}</span>
        </div>

        <div v-if="visitedPreview.length" class="hub-mate-visited">
          <span
            v-for="code in visitedPreview"
            :key="code"
            class="hub-mate-visited-flag"
            :title="countryName(code)"
          >
            <span :class="'fi fi-' + code"></span>
          </span>
          <span v-if="visitedExtra" class="hub-mate-visited-more">+{{ visitedExtra }}</span>
        </div>

        <p v-if="metaLine" class="hub-mate-meta">{{ metaLine }}</p>
      </div>
    </component>

    <div v-if="socials.length" class="hub-mate-socials">
      <a
        v-for="social in socials"
        :key="social.name"
        :href="social.href"
        :title="social.label"
        target="_blank"
        rel="nofollow noopener"
        class="hub-mate-social"
        @click.stop
      >
        <social-field-icon :name="social.name" />
      </a>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import "/node_modules/flag-icons/css/flag-icons.min.css";

import ProfilePic from '../components/ProfilePic.vue';
import SocialFieldIcon from '../components/SocialFieldIcon.vue';
import { MATE_SOCIAL_NETWORKS, normalizeSocialUrl } from '../socialUrls.js';

const LIFESTYLE = [
  { key: 'orgSpont', low: 'Organized', high: 'Spontaneous' },
  { key: 'quietLoud', low: 'Quiet', high: 'Outgoing' },
  { key: 'earlyNight', low: 'Early bird', high: 'Night owl' },
  { key: 'workFun', low: 'Work-focused', high: 'Fun-focused' },
  { key: 'expTrad', low: 'Experimental', high: 'Traditional' },
  { key: 'multiLocal', low: 'Global', high: 'Local' },
];

const props = defineProps({
  mate: {
    type: Object,
    required: true,
  },
});

const regionNames = typeof Intl !== 'undefined' && Intl.DisplayNames
  ? new Intl.DisplayNames(['en'], { type: 'region' })
  : null;

function countryName(code) {
  if (!code) return '';
  try {
    return regionNames?.of(code.toUpperCase()) || code.toUpperCase();
  } catch (error) {
    return code.toUpperCase();
  }
}

function formatShortDate(value) {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
}

const isDeleted = computed(() => !!props.mate.account_deleted);

const profileTo = computed(() => {
  if (isDeleted.value) return undefined;
  return '/mate/' + (props.mate.username ? props.mate.username : props.mate.uid);
});

const profileTag = computed(() => {
  if (isDeleted.value) return 'div';
  if (!props.mate.uid && !props.mate.username) return 'div';
  return 'router-link';
});

const countryCode = computed(() => {
  const code = String(props.mate.location_country || '').toLowerCase();
  return code && code !== 'none' ? code : '';
});

const aboutText = computed(() => String(props.mate.hobbies_interests || '').trim());

const showUsername = computed(() => {
  if (isDeleted.value) return false;
  const username = String(props.mate.username || '').trim();
  if (!username) return false;
  return username !== String(props.mate.displayName || '').trim();
});

const visitedCountries = computed(() => {
  const list = Array.isArray(props.mate.visited_countries) ? props.mate.visited_countries : [];
  return list
    .map(code => String(code).toLowerCase())
    .filter(code => code && code !== 'none');
});

const visitedPreview = computed(() => visitedCountries.value.slice(0, 8));
const visitedExtra = computed(() => Math.max(0, visitedCountries.value.length - visitedPreview.value.length));

const socials = computed(() => MATE_SOCIAL_NETWORKS
  .map(network => {
    const href = normalizeSocialUrl(network.name, props.mate[network.name]);
    return href ? { ...network, href } : null;
  })
  .filter(Boolean));

const lifestyleTags = computed(() => LIFESTYLE.reduce((tags, item) => {
  const raw = props.mate[item.key];
  if (raw === null || raw === undefined || raw === '') return tags;
  const value = Number(raw);
  if (!Number.isFinite(value) || value === 5) return tags;
  if (value <= 3) tags.push(item.low);
  if (value >= 7) tags.push(item.high);
  return tags;
}, []).slice(0, 3));

const roleKind = computed(() => {
  if (isDeleted.value) return 'deleted';
  if (props.mate.status === 'left') return 'left';
  if (props.mate.role === 'admin') return 'admin';
  return 'member';
});

const roleLabel = computed(() => {
  if (roleKind.value === 'admin') return 'Admin';
  if (roleKind.value === 'deleted') return 'Account Deleted';
  if (roleKind.value === 'left') return 'Left Space';
  return 'Member';
});

const metaLine = computed(() => {
  const parts = [];
  if (countryCode.value) parts.push(countryName(countryCode.value));
  if (visitedCountries.value.length) parts.push(`${visitedCountries.value.length} countries`);
  if (props.mate.status === 'left' && props.mate.left_at) {
    parts.push('Left ' + formatShortDate(props.mate.left_at));
  } else if (props.mate.joined_at) {
    parts.push('Joined ' + formatShortDate(props.mate.joined_at));
  } else if (isDeleted.value) {
    parts.push('Account deleted');
  }
  return parts.join(' · ');
});

const isRich = computed(() => (
  !!aboutText.value
  || !!countryCode.value
  || socials.value.length > 0
  || visitedCountries.value.length > 0
  || lifestyleTags.value.length > 0
));
</script>

<style>
.hub-mate-card {
  display: flex;
  flex-direction: column;
  position: relative;
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .08), 0 6px 16px rgba(0, 0, 0, .05);
  transition: transform .15s ease, box-shadow .15s ease;
  color: inherit;
}

.hub-mate-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, .1), 0 14px 28px rgba(0, 0, 0, .09);
}

@media (hover: none) {
  .hub-mate-card:hover {
    transform: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .08), 0 6px 16px rgba(0, 0, 0, .05);
  }
}

.hub-mate-card-link {
  display: block;
  color: inherit;
  text-decoration: none;
  min-width: 0;
}

.hub-mate-card-link:hover {
  color: inherit;
  text-decoration: none;
}

.hub-mate-photo {
  position: relative;
  height: 104px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(180deg, #eef6ff 0%, #f4f4f4 100%);
  overflow: hidden;
}

.hub-mate-photo > div {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 1px 4px rgba(0, 0, 0, .12);
}

.hub-mate-photo > div > div {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  overflow: hidden;
}

.hub-mate-photo img,
.hub-mate-photo svg {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.hub-mate-photo-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f7f7f7;
}

.hub-mate-photo-placeholder img {
  width: auto;
  height: 36px;
  object-fit: contain;
  opacity: .6;
}

.hub-mate-flag {
  position: absolute;
  top: 10px;
  left: 10px;
  background: rgba(255, 255, 255, .94);
  border-radius: 999px;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
  overflow: hidden;
}

.hub-mate-flag .fi {
  border-radius: 3px;
  line-height: 1em;
  width: 1.3em;
}

.hub-mate-role {
  position: absolute;
  top: 10px;
  right: 10px;
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .18);
}

.hub-mate-role-admin {
  background: #00abff;
  color: #fff;
}

.hub-mate-role-member {
  background: rgba(255, 255, 255, .94);
  color: #3a3a3a;
}

.hub-mate-card-deleted {
  opacity: .88;
}

.hub-mate-card-deleted .hub-mate-card-link {
  cursor: default;
}

.hub-mate-role-deleted {
  background: #f3f3f3;
  color: #777;
}

.hub-mate-role-left {
  background: rgba(255, 255, 255, .94);
  color: #777;
}

.hub-mate-body {
  padding: 12px 14px 14px;
  min-width: 0;
}

.hub-mate-name {
  margin: 0 0 2px;
  font-weight: 700;
  font-size: 16px;
  line-height: 1.25;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.hub-mate-username {
  margin: 0 0 8px;
  font-size: 13px;
  color: #6e6e6e;
}

.hub-mate-about {
  margin: 8px 0 0;
  font-size: 13px;
  line-height: 1.45;
  color: #444;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.hub-mate-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 10px;
}

.hub-mate-tag {
  background: #f3f8ff;
  border: 1px solid #d7ebff;
  color: #0a6dad;
  border-radius: 999px;
  padding: 3px 8px;
  font-size: 11px;
  font-weight: 600;
}

.hub-mate-visited {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 4px;
  margin-top: 10px;
}

.hub-mate-visited-flag {
  width: 22px;
  height: 16px;
  overflow: hidden;
  border-radius: 3px;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, .06);
}

.hub-mate-visited-flag .fi {
  display: block;
  width: 22px;
  height: 16px;
}

.hub-mate-visited-more {
  font-size: 12px;
  font-weight: 700;
  color: #6e6e6e;
  margin-left: 2px;
}

.hub-mate-meta {
  margin: 10px 0 0;
  font-size: 12px;
  color: #6e6e6e;
}

.hub-mate-socials {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 0 14px 14px;
}

.hub-mate-social {
  width: 28px;
  height: 28px;
  border-radius: 999px;
  background: #f4f7fb;
  color: #2f2f2f;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hub-mate-social:hover {
  background: #e7f4ff;
  color: #0086da;
}

.hub-mate-social svg {
  width: 15px;
  height: 15px;
}
</style>
