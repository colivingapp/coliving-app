<template>
    <div class="content">
        <h1>My Bookmarks</h1>
        
        <div style="height:10px;"></div>

        <div v-for="bookmark in sortedBookmarks" :key="bookmark.space_uid">
            <div style="float:left;clear:both;margin-bottom:15px;"><router-link :to="'/space/'+bookmark.space_uid">{{ bookmark.name }}</router-link></div>
            <div class="delete-button" style="float:left;margin-left:10px;font-size:11px;width:17px;height:17px;padding-top:1px;position:relative;top:0px;" @click="removeBookmark(bookmark.space_uid)">✖</div>
        </div>

        <span v-if="!isLoaded">Loading...</span>
        <span v-if="!sortedBookmarks.length && isLoaded"><br>You have no bookmarks.</span>

        <div style="height:40px;clear:both;"></div>

        <map-by-country :locations="sortedBookmarks" v-if="sortedBookmarks.length" />

    </div>
</template>

<script setup>
    import { onMounted, ref, computed } from 'vue';

    import MapByCountry from '../map/MapByCountry.vue';

    let bookmarks = ref([]);
    let isLoaded = ref(false);

    onMounted(() => {
        axios.get('/api/my-bookmarks')
        .then(resp => {
            bookmarks.value = resp.data
            isLoaded.value = true;
        })
        .catch(err => console.log(err))
    });

    function removeBookmark(uid) {
        bookmarks.value = bookmarks.value.filter(bookmark => bookmark.space_uid !== uid);
        
        axios.post('/api/bookmark/'+uid)
        .then(res => {})
        .catch(err => {})
    }

    const sortedBookmarks = computed(() => {
        return [...bookmarks.value].sort((a, b) =>
            new Date(b.created_at) - new Date(a.created_at)
        );
    });
</script>