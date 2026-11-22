
<template>
    <div class="min-h-screen bg-slate-50">
        <AdminHeader />

        <AdminNavbar />

        <main class="p-6">
            <router-view />
        </main>
    </div>
</template>

<script setup>
import AdminHeader from '@/components/special/layouts/AdminHeader.vue'
import AdminNavbar from '@/components/special/layouts/AdminNavbar.vue'
</script>
