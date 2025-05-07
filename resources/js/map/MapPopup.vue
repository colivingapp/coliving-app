<template>
  <div class="leaflet-popup-content">
    <div class="overflow-dots">
      <router-link :href="popupContent.link">
        <img :src="popupContent.image" style="width:100%;border-radius: 5px;">
        <div style="height:5px;"></div>
        <span style="font-size:14px;font-weight:bold;">{{ popupContent.name }}</span>
      </router-link>
      <div style="height:5px;"></div>
      <router-link :to="popupContent.countryLink">{{ popupContent.country }}</router-link>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    popupContent: {
      type: Object,
      required: true,
    },
  },
};
</script>
  