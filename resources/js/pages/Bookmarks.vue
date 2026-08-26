<template>
    <div class="content">
        <h1>My Bookmarks</h1>

        <div v-if="!isLoaded">
            Loading...
        </div>
        <div v-else-if="!sortedBookmarks.length">
            You have no bookmarks.
        </div>
        <div v-else>
            <div style="height:10px;"></div>

            <map-by-country :locations="sortedBookmarks" />

            <div style="height:10px;"></div>

            <h2>Bookmarked Spaces</h2>

            <div class="space-grid">
                <SpaceCard
                    v-for="space in sortedBookmarks"
                    :key="space.uid"
                    :space="space"
                    removable
                    @remove="removeBookmark(space.uid)"
                />
            </div>

            <div style="height:50px;"></div>
        </div>
    </div>
</template>

<script setup>
    import { onMounted, ref, computed } from 'vue';

    import MapByCountry from '../map/MapByCountry.vue';
    import SpaceCard from '../components/SpaceCard.vue';

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
        bookmarks.value = bookmarks.value.filter(bookmark => (bookmark.uid || bookmark.space_uid) !== uid);

        axios.post('/api/bookmark/'+uid)
        .then(res => {})
        .catch(err => {})
    }

    const sortedBookmarks = computed(() => {
        return [...bookmarks.value]
            .filter(bookmark => bookmark.name)
            .map(bookmark => ({
                ...bookmark,
                uid: bookmark.uid || bookmark.space_uid,
            }))
            .sort((a, b) =>
                new Date(b.created_at) - new Date(a.created_at)
            );
    });
</script>
