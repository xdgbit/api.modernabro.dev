<template>
    <Toaster
        position="top-right"
        dir="rtl"
        rich-colors
        :expand="true"
        :close-button="true"
        :duration="2000"
    />
    <router-view />
</template>

<script>

export default {
    name: 'AdminApp',
}
</script>