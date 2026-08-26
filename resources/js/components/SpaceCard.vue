<template>
  <div class="space-card">
    <router-link :to="'/space/' + space.uid" class="space-card-link">
      <div class="space-card-photo">
        <img
          v-if="space.first_photo"
          :src="'/storage/spaces/' + space.first_photo"
          class="space-card-img"
          :alt="space.name"
        >
        <div v-else class="space-card-placeholder">
          <img src="/img/coliving-app-logo-symbol.png" alt="">
        </div>
        <span v-if="space.country_code" class="space-card-flag">
          <span :class="'fi fi-' + space.country_code.toLowerCase()"></span>
        </span>
      </div>
      <div class="space-card-body" :class="{ 'space-card-body-actions': manageable }">
        <p class="space-card-title">{{ space.name }}</p>
        <p v-if="space.address" class="space-card-address">{{ space.address }}</p>
        <p v-else-if="space.country" class="space-card-address">{{ space.country }}</p>
      </div>
    </router-link>
    <div v-if="manageable" class="space-card-actions">
      <router-link :to="'/space/' + space.uid + '/edit'">
        <button type="button" class="button">Edit</button>
      </router-link>
      <router-link :to="'/space/' + space.uid + '/dashboard'">
        <button type="button" class="button">Manage</button>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import "/node_modules/flag-icons/css/flag-icons.min.css";

defineProps({
  space: {
    type: Object,
    required: true,
  },
  manageable: {
    type: Boolean,
    default: false,
  },
});
</script>

<style>
.space-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 20px;
}

.space-card {
  display: block;
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .08), 0 6px 16px rgba(0, 0, 0, .05);
  transition: transform .15s ease, box-shadow .15s ease;
  color: inherit;
}

.space-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, .1), 0 14px 28px rgba(0, 0, 0, .09);
  text-decoration: none;
  color: inherit;
}

.space-card-link {
  display: block;
  color: inherit;
  text-decoration: none;
}

.space-card-link:hover {
  color: inherit;
  text-decoration: none;
}

.space-card-actions {
  display: flex;
  gap: 8px;
  padding: 0 14px 14px;
}

.space-card-actions .button {
  width: 100%;
  margin: 0;
  padding: 6px 10px;
  font-size: 13px;
}

.space-card-actions a {
  flex: 1;
  min-width: 0;
}

.space-card-photo {
  position: relative;
  height: 160px;
  background: #f2f2f2;
}

.space-card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}

.space-card-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.space-card-placeholder img {
  height: 60px;
  opacity: .6;
}

.space-card-flag {
  position: absolute;
  top: 10px;
  left: 10px;
  background: rgba(255, 255, 255, .92);
  border-radius: 999px;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
  overflow: hidden;
}

.space-card-flag .fi {
  border-radius: 3px;
  line-height: 1em;
  width: 1.3em;
}

.space-card-body {
  padding: 12px 14px 14px;
}

.space-card-body-actions {
  padding-bottom: 10px;
}

.space-card-title {
  margin: 0 0 4px;
  font-weight: 600;
  font-size: 15px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.space-card-address {
  margin: 0;
  font-size: 13px;
  color: rgb(89, 89, 89);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
