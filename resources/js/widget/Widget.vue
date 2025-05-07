<script setup>
    const props = defineProps({
        title: String,
    })
</script>

<template>
    <div class="widget-container">
        <header>
            <div style="padding-top:15px;font-size:19px;font-weight:bold;color:rgb(220, 220, 220);">{{title}}</div>
        </header>
        <slot></slot>
        <div style="height:30px;"></div>
    </div>
</template>
