<template>
  <div style="margin:0 auto;text-align:center;">
    <div>
      <div style="margin: auto; width: 340px;">
        <input v-model="searchQuery" placeholder="Search..." 
               @input="showResults = searchQuery.length >= 2" 
               @blur="onBlur" 
               class="input-search" />
        <div v-if="showResults && searchQuery.length >= 2 && spacesFiltered.length > 0" class="dropdown">
          <router-link v-for="space in spacesFiltered.slice(0, 3)" :to="'/space/'+space.uid">
            <div class="dropdown-item">
              {{ space.name }}, {{ space.country }}
            </div>
          </router-link>
          <div v-if="spacesFiltered.length > 0" class="dropdown-item link" style="text-align:center;" @click="showAll">All...</div>
        </div>
      </div>

      <div style="height:10px;"></div>
      <span style="font-size:12px;">Search by country, city, address, or space name</span>
      <!-- <div style="height:4px;"></div>
      <router-link to="/spaces">View all spaces</router-link> -->
      <div style="height:25px;"></div>
    </div> 
    <div style="height:calc(100vh - 300px);">
      <map-home :locations="locations" />
    </div>
    <div>
      <div style="height:30px;"></div>
      <router-link to="/space/new"><button class="action-button button-1">Add a Space</button></router-link>
      
      <div style="height:30px;"></div>

      <div class="bottom-arrow-div" @click="scrollToContent">
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"  xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
          viewBox="0 0 78.6 45.6" style="enable-background:new 0 0 78.6 45.6;width:22px;fill:#7e7e7e;" xml:space="preserve">
          <g>
            <path style="fill:#888888;" d="M76.8,1.8L76.8,1.8c1.2,1.2,1.8,2.9,1.8,4.5c0,1.6-0.6,3.2-1.8,4.5l-33,33l0,0c-0.6,0.6-1.3,1-2,1.4
              l0,0c-0.8,0.3-1.6,0.5-2.4,0.5c-0.8,0-1.6-0.2-2.4-0.5h0c-0.7-0.3-1.4-0.8-2-1.4l0,0l-33-33C0.6,9.5,0,7.9,0,6.3
              c0-1.6,0.6-3.2,1.9-4.4l0,0l0,0C3.1,0.6,4.7,0,6.3,0c1.6,0,3.2,0.6,4.4,1.8h0l28.5,28.5L67.9,1.8l0,0C69.1,0.6,70.7,0,72.3,0
              C73.9,0,75.5,0.6,76.8,1.8L76.8,1.8L76.8,1.8z"/>
          </g>
        </svg>
      </div>
    </div>
  </div>

  <div v-if="newestSpaces.length" class="content newest-spaces" id="home-content" style="text-align:center;">
    <div style="height:10px;"></div>
    <h1 style="margin-bottom:20px;">Newest Spaces</h1>
    <div class="space-grid">
      <SpaceCard v-for="space in newestSpaces" :key="space.uid" :space="space" />
    </div>
    <div style="height:40px;"></div>
  </div>

  <div class="content" :id="newestSpaces.length ? undefined : 'home-content'" style="text-align:center;max-width:800px;">
    <div v-if="!newestSpaces.length" style="height:50px;"></div>

    <h1 style="margin-bottom:10px;">Open Coliving</h1>
    <div style="line-height:1.75;">
      <div>Coliving isn’t just a trendy label for branded buildings. It’s the broader idea of people living together - sharing a home, lowering costs, making connections, and building community.</div>
      <div style="margin-top:14px;">Coliving App is an open-source platform for shared living worldwide: coliving businesses, community houses, private rooms in shared apartments, student housing, communes, cohousing, popup &amp; temporary spaces, retirement communities, and other alternative communal living arrangements.</div>
    </div>
    <div style="height:18px;"></div>
    <div>
      <a href="https://github.com/colivingapp/coliving-app" target="_blank" rel="noopener" class="github-fork-btn">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
        Fork on GitHub
      </a>
    </div>

    <div style="height:70px;"></div>
    
    <h1 style="margin-bottom:10px;">More than a directory</h1>
    <div style="max-width:560px;margin:0 auto;color:#666;">
      Coliving App starts as a discovery tool, but it's designed to go further.
    </div>
    <div style="height:34px;"></div>
    <div class="directory-steps">
      <div class="directory-step">
        <div class="directory-step-icon" aria-hidden="true">
          <Icon icon="ph:magnifying-glass-duotone" width="34" height="34" />
        </div>
        <h3>Find spaces</h3>
        <p>Explore shared living around the world: rooms, shared apartments, student housing, coliving spaces, community houses, cohousing, communes, retirement communities, and more.</p>
      </div>
      <div class="directory-step-arrow" aria-hidden="true">
        <Icon icon="ph:arrow-right" width="22" height="22" />
      </div>
      <div class="directory-step">
        <div class="directory-step-icon" aria-hidden="true">
          <Icon icon="ph:handshake-duotone" width="34" height="34" />
        </div>
        <h3>Join spaces</h3>
        <p>Request to join and become part of a home. Coliving App doesn't manage payments - members and spaces handle arrangements directly.</p>
      </div>
      <div class="directory-step-arrow" aria-hidden="true">
        <Icon icon="ph:arrow-right" width="22" height="22" />
      </div>
      <div class="directory-step">
        <div class="directory-step-icon" aria-hidden="true">
          <Icon icon="ph:chats-circle-duotone" width="34" height="34" />
        </div>
        <h3>Use the Space Hub</h3>
        <p>Once your request is accepted, the private space Hub becomes the shared home base for everyday community life: updates, people, plans, and decisions that keep the space connected.</p>
      </div>
    </div>

    <div style="height:70px;"></div>

    <h1 style="margin-bottom:10px;">What is Coliving?</h1>
    <div class="section-subtitle">
      Coliving covers many shapes of shared living. Tap any word to browse spaces in that category.
    </div>
    <div style="height:30px;"></div>
    <div class="word-cloud">
      <router-link
        v-for="tag in categoryCloud"
        :key="tag.key"
        :to="'/spaces/' + tag.slug"
        class="word-cloud-tag"
        :class="[tag.type, 'wc-size-' + tag.size, 'wc-color-' + tag.color]"
      >{{ tag.label }}</router-link>
    </div>

    <div style="height:70px;"></div>

    <h1 style="margin-bottom:10px;">Coliving Spaces</h1>
    <div style="line-height:1.7;">
      Running a space - or just getting one started? Add it to the map, welcome join requests, and grow a real community around your home. It can be as simple as a room in an apartment or as big as a coliving house, cooperative, or commune.
    </div>
    <div style="height:18px;"></div>
    <router-link to="/space/new"><button class="action-button button-1">Add New Space</button></router-link>

    <div style="height:70px;"></div>

    <h1 style="margin-bottom:10px;">Coliving Hub</h1>
    <div style="line-height:1.7;">
      Your coliving Hub brings together the spaces you live in, manage, or have been part of. It is a simple way for each community to stay organized, connected, and involved in how the space runs.
    </div>
    <div style="height:18px;"></div>
    <router-link to="/hub"><button class="action-button button-2">Explore the Hub</button></router-link>
  </div>
  <div style="height:130px;"></div>
</template>


<script setup>
  import { ref, computed, watch, onMounted } from 'vue';
  import MapHome from './map/MapHome.vue';
  import SpaceCard from './components/SpaceCard.vue';
  import { CATEGORY_ORDER, CATEGORY_META, SUBCATEGORY_META } from './spaceTaxonomy.js';

  import { useRouter } from 'vue-router';
  const router = useRouter();

  const caUser = window.caUser;

  const categoryCloud = computed(() => {
    const tags = [];
    CATEGORY_ORDER.forEach((slug, catIndex) => {
      const meta = CATEGORY_META[slug];
      if (!meta) return;
      tags.push({
        key: 'cat-' + slug,
        slug,
        label: meta.label,
        type: 'category',
        size: catIndex % 3,
        color: catIndex % 5,
      });
      const subs = SUBCATEGORY_META[slug] || {};
      Object.keys(subs).forEach((subSlug, subIndex) => {
        tags.push({
          key: 'sub-' + slug + '-' + subSlug,
          slug,
          label: subs[subSlug].label,
          type: 'subcategory',
          size: (catIndex + subIndex) % 3,
          color: (catIndex + subIndex + 1) % 5,
        });
      });
    });
    return tags;
  });

  const searchQuery = ref('');
  const showResults = ref(false);

  const spaces = ref(window.spaces || []);
  const newestSpaces = ref([]);

  if (!window.spaces) {
    async function fetchSpaces() {
      try {
        const response = await axios.get('/api');
        // console.log(response.data.spaces)
        // spaces.value = response.data[0] || [];
        // console.log(spaces.value)
        spaces.value = response.data.spaces;
      } catch (error) {
        console.error(error);
      }
    }
    fetchSpaces();
  }

  // Improved search function with relevance scoring
  function calculateRelevanceScore(space, searchValue) {
    const { country, name, formatted_address, city } = space;
    const searchLower = searchValue.toLowerCase();
    let score = 0;

    // Normalize fields
    const nameLower = (name || '').toLowerCase();
    const countryLower = (country || '').toLowerCase();
    const cityLower = (city || '').toLowerCase();
    const addressLower = (formatted_address || '').toLowerCase();

    // Exact name match (highest priority)
    if (nameLower === searchLower) {
      score += 1000;
    }
    // Name starts with query
    else if (nameLower.startsWith(searchLower)) {
      score += 500;
    }
    // Word boundary match in name (whole word)
    else if (new RegExp(`\\b${searchLower.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'i').test(nameLower)) {
      score += 400;
    }
    // Name contains query
    else if (nameLower.includes(searchLower)) {
      score += 300;
    }

    // City exact match
    if (cityLower === searchLower) {
      score += 200;
    }
    // City starts with query
    else if (cityLower.startsWith(searchLower)) {
      score += 150;
    }
    // Word boundary match in city
    else if (cityLower && new RegExp(`\\b${searchLower.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'i').test(cityLower)) {
      score += 100;
    }
    // City contains query
    else if (cityLower.includes(searchLower)) {
      score += 50;
    }

    // Country exact match
    if (countryLower === searchLower) {
      score += 100;
    }
    // Country starts with query
    else if (countryLower.startsWith(searchLower)) {
      score += 75;
    }
    // Word boundary match in country
    else if (new RegExp(`\\b${searchLower.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'i').test(countryLower)) {
      score += 50;
    }
    // Country contains query
    else if (countryLower.includes(searchLower)) {
      score += 25;
    }

    // Address contains query (lowest priority)
    if (addressLower.includes(searchLower)) {
      score += 10;
    }

    return score;
  }

  const spacesFiltered = computed(() => {
    if (searchQuery.value && spaces.value && searchQuery.value.length >= 2) {
      const searchValue = searchQuery.value.trim();
      if (!searchValue) {
        return spaces.value || [];
      }

      // Filter and score results
      const results = spaces.value
        .map(space => {
          const score = calculateRelevanceScore(space, searchValue);
          return { space, score };
        })
        .filter(item => item.score > 0) // Only include matches
        .sort((a, b) => b.score - a.score) // Sort by relevance (highest first)
        .map(item => item.space); // Extract just the space objects

      return results;
    } else {
      return spaces.value || [];
    }
  });

  let locations = ref([]);

  function updateLocations() {
    if (spaces.value) {
      locations.value = spacesFiltered.value.reduce((acc, space) => {
        if (space.latitude && space.longitude) {
          const { uid, name_slug, name, country_slug, country, latitude, longitude } = space;
          acc.push({ latitude, longitude, uid, name_slug, name, country_slug, country });
        }
        return acc;
      }, []);
    } else {
      locations.value = [];
    }
  }

  watch(spacesFiltered, updateLocations);

  if (spaces.value) {
    updateLocations();
  }

  function onBlur() {
    setTimeout(() => { showResults.value = false; }, 150);
  }

  function showAll() {
    router.push('/search/'+searchQuery.value);
  }

  function scrollToContent() {
    document.getElementById('home-content').scrollIntoView({ behavior: 'smooth' });
  }

  onMounted(async () => {
    try {
      const response = await axios.get('/api/spaces/newest');
      newestSpaces.value = response.data || [];
    } catch (error) {
      console.error(error);
    }
  });
</script>


<style>
  /* Search input on home page */
  .input-search {
    border: 1px solid #888!important;
    font-family: Montserrat,sans-serif;
    font-size: 20px;  
    border: 1px solid #87c9eb;
    border-radius: 50px;
    padding: 10px 20px;
  }

  .dropdown {
    border: 1px solid #ddd;
    max-height: 200px;
    overflow-y: auto;
    position: absolute;
    width: 340px;
    background: white;
    z-index: 1000;
    margin-top: 5px;
    border-radius: 10px;
    font-size: 14px;
    padding: 5px;
  }

  .dropdown a {
    text-decoration: none;
  }

  .dropdown-item {
    padding: 5px;
    cursor: pointer;
    text-align: left;
    font-size: 18px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .dropdown-item:hover {
    background-color: #87c9eb;
    color: white;
  }

  .more-results {
    text-align: center;
    padding: 5px;
    height: 41px;
    font-size: 22px;
    margin-top: -11px;
  }

  .newest-spaces .space-grid {
    text-align: left;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }

  @media screen and (max-width: 700px) {
    .newest-spaces .space-grid {
      grid-template-columns: 1fr;
    }
  }

  @media screen and (min-width: 701px) and (max-width: 799px) {
    .newest-spaces .space-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .section-subtitle {
    margin: 0 auto;
    color: #666;
    white-space: nowrap;
  }

  @media screen and (max-width: 700px) {
    .section-subtitle {
      white-space: normal;
    }
  }

  /* Fork on GitHub button */
  .github-fork-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-family: Arial, sans-serif;
    font-size: 20px;
    padding: 12px 28px;
    border: 1px solid #24292e;
    border-radius: 50px;
    background-color: #24292e;
    color: #fff !important;
    cursor: pointer;
    text-decoration: none !important;
    transition-duration: 0.4s;
  }

  .github-fork-btn:hover {
    background-color: #fff;
    color: #24292e !important;
  }

  .github-fork-btn svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    fill: currentColor;
  }

  /* More than a directory: steps */
  .directory-steps {
    display: flex;
    align-items: stretch;
    justify-content: center;
    gap: 8px;
    max-width: 920px;
    margin: 0 auto;
  }

  .directory-step {
    flex: 1 1 0;
    position: relative;
    padding: 28px 20px 24px;
    border-radius: 16px;
    background: linear-gradient(160deg, #f4fbff 0%, #ffffff 60%);
    border: 1px solid #e3eef5;
    box-shadow: 0 2px 10px rgba(0, 0, 0, .04);
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  }

  .directory-step:hover {
    transform: translateY(-4px);
    border-color: #87c9eb;
    box-shadow: 0 12px 28px rgba(135, 201, 235, .25);
  }

  .directory-step:hover .directory-step-icon {
    background: linear-gradient(165deg, #d4eef8 0%, #bfe4f3 100%);
    color: #1f6a96;
  }

  .directory-step-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 58px;
    height: 58px;
    margin-bottom: 14px;
    border-radius: 16px;
    background: linear-gradient(165deg, #e4f4fb 0%, #d2ebf6 100%);
    color: #2b7aa8;
    line-height: 1;
  }

  .directory-step h3 {
    margin: 0 0 10px;
    font-size: 17px;
    font-weight: 600;
    color: #333;
  }

  .directory-step p {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
    color: #666;
    text-align: left;
  }

  .directory-step-arrow {
    display: flex;
    align-items: center;
    flex: 0 0 auto;
    color: #b9d8e8;
  }

  .directory-step-arrow svg {
    width: 22px;
    height: 22px;
  }

  @media screen and (max-width: 800px) {
    .directory-steps {
      flex-direction: column;
      gap: 0;
    }

    .directory-step-arrow {
      justify-content: center;
      transform: rotate(90deg);
      margin: -4px 0;
    }

    .directory-step p {
      text-align: center;
    }
  }

  /* What is Coliving: word cloud */
  .word-cloud {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 10px 14px;
    max-width: 780px;
    margin: 0 auto;
  }

  .word-cloud-tag {
    text-decoration: none !important;
    line-height: 1.3;
    white-space: nowrap;
    transition: transform 0.15s ease, opacity 0.15s ease;
  }

  .word-cloud-tag:hover {
    transform: scale(1.08);
  }

  .word-cloud-tag.category {
    font-weight: 700;
  }

  .word-cloud-tag.subcategory {
    font-weight: 400;
    opacity: 0.85;
  }

  .word-cloud-tag.wc-size-0 { font-size: 15px; }
  .word-cloud-tag.wc-size-1 { font-size: 20px; }
  .word-cloud-tag.wc-size-2 { font-size: 26px; }

  .word-cloud-tag.category.wc-size-0 { font-size: 20px; }
  .word-cloud-tag.category.wc-size-1 { font-size: 26px; }
  .word-cloud-tag.category.wc-size-2 { font-size: 32px; }

  .word-cloud-tag.wc-color-0 { color: #87c9eb; }
  .word-cloud-tag.wc-color-1 { color: #f2a154; }
  .word-cloud-tag.wc-color-2 { color: #6bbf8e; }
  .word-cloud-tag.wc-color-3 { color: #e07a9b; }
  .word-cloud-tag.wc-color-4 { color: #8b7fd6; }

  .word-cloud-tag:hover.wc-color-0 { color: #4fa9d8; }
  .word-cloud-tag:hover.wc-color-1 { color: #d97f2c; }
  .word-cloud-tag:hover.wc-color-2 { color: #439a63; }
  .word-cloud-tag:hover.wc-color-3 { color: #c74d75; }
  .word-cloud-tag:hover.wc-color-4 { color: #6355c2; }
</style>