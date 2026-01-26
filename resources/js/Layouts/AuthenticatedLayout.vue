<script setup>
import Navbar from "@/Layouts/Partials/Navbar.vue";
import Sidebar from "@/Layouts/Partials/Sidebar.vue";
import Footer from "@/Layouts/Partials/Footer.vue";
import { ref, onMounted, onUnmounted } from "vue";
import { eventBus } from "@/utils/eventBus";

const isCollapsed = ref(JSON.parse(localStorage.getItem("sidebarCollapsed") || "true"));
const isExpandAll = ref(JSON.parse(localStorage.getItem('sidebarExpandAll') || 'false'));

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
};

const handleExpandAll = (state) => {
    isExpandAll.value = state;
};

const handleSidebar = (state) => {
    isCollapsed.value = state;
};

onMounted(() => {
    eventBus.on('toggle-expandall', handleExpandAll);
    eventBus.on('toggle-sidebar', handleSidebar);
});

onUnmounted(() => {
    eventBus.off('toggle-expandall', handleExpandAll);
    eventBus.off('toggle-sidebar', handleSidebar);
});

defineEmits(['toggleSidebar'])

</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<template>
    <div>
        <Navbar />
        <Sidebar @toggleSidebar="toggleSidebar" />
        <div class="flex overflow-hidden bg-gray-100 dark:bg-gray-900">
            <main class="pt-12 w-full h-full min-h-screen overflow-y-auto bg-gray-100 dark:bg-gray-900 transition-all duration-300" :class="{ 'ml-64': isCollapsed, 'ml-16': !isCollapsed }">
                <keep-alive>
                    <slot />
                </keep-alive>
                <Footer />
            </main>
        </div>
    </div>
</template>
