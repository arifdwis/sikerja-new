<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import { Icon } from "@iconify/vue";

const page = usePage();
const role = computed(() => page.props.auth?.role);
const currentUrl = computed(() => page.url);

const menuItems = computed(() => {
    const items = page.props.auth?.menu || [];
    return items.filter(item => item != null);
});

const openDropdowns = ref({});

const isActive = (routeName) => {
    if (!routeName) return false;
    if (routeName.startsWith('/')) {
         const normalizedRoute = routeName.replace(/\/$/, '');
         const normalizedCurrentUrl = currentUrl.value.split('?')[0].replace(/\/$/, '');
         return normalizedCurrentUrl === normalizedRoute || normalizedCurrentUrl.startsWith(normalizedRoute + '/');
    }
    // Handle named routes using Ziggy
    try {
        return route().current(routeName + '*');
    } catch (e) {
        return false;
    }
};

const hasActiveChild = (item) => {
    if (!item.children) return false;
    return item.children.some(child => isActive(child.route));
};

const toggleDropdown = (index) => {
    openDropdowns.value[index] = !openDropdowns.value[index];
};

const navigateTo = (routeName) => {
    if (!routeName) return;
    try {
        if (routeName.startsWith('/')) {
            router.visit(routeName);
        } else {
            router.visit(route(routeName));
        }
    } catch (e) {
        console.warn('Navigation failed for route:', routeName);
    }
};

const getHref = (routeName) => {
     if (!routeName) return '#';
     if (routeName.startsWith('/')) return routeName;
     try {
         return route(routeName);
     } catch (e) {
         console.warn('Invalid route:', routeName);
         return '#';
     }
};

onMounted(() => {
    if (menuItems.value && Array.isArray(menuItems.value)) {
        menuItems.value.forEach((item, index) => {
            if (item && item.children && hasActiveChild(item)) {
                openDropdowns.value[index] = true;
            }
        });
    }
});
</script>

<template>
    <aside class="fixed top-0 left-0 z-20 flex flex-col flex-shrink-0 w-64 h-full pt-16 font-normal duration-300 lg:flex transition-width bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidebar">
        <div class="relative flex flex-col flex-1 min-h-0 pt-0">
            <div class="flex flex-col flex-1 pt-2 overflow-y-auto">
                <div class="flex-1 px-3 space-y-1 divide-dashed divide-y divide-gray-200 dark:divide-gray-700">
                    <ul class="py-2 space-y-2">
                        <li v-for="(item, index) in menuItems" :key="item.id || index">
                            <!-- Menu item with children (dropdown) -->
                            <template v-if="item.children && item.children.length > 0">
                                <div 
                                    @click="toggleDropdown(index)" 
                                    class="flex items-center justify-between py-2 px-2 text-base font-medium rounded-lg cursor-pointer transition-all duration-150 group"
                                    :class="{
                                        'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white': hasActiveChild(item) || openDropdowns[index],
                                        'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200': !hasActiveChild(item) && !openDropdowns[index],
                                    }"
                                >
                                    <div class="flex items-center">
                                        <Icon 
                                            class="w-6 h-6 transition duration-75" 
                                            :class="{
                                                'text-gray-900 dark:text-white': hasActiveChild(item) || openDropdowns[index],
                                                'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !hasActiveChild(item) && !openDropdowns[index],
                                            }" 
                                            :icon="item.icon" 
                                        />
                                        <span class="ml-3">{{ item.title }}</span>
                                    </div>
                                    <Icon 
                                        icon="solar:alt-arrow-down-linear"
                                        class="w-4 h-4 transition-transform duration-200" 
                                        :class="{ 'rotate-180': openDropdowns[index] }"
                                    />
                                </div>
                                <!-- Dropdown children -->
                                <ul 
                                    v-show="openDropdowns[index]" 
                                    class="py-2 space-y-2 pl-4"
                                >
                                    <li v-for="child in item.children" :key="child.route">
                                        <Link 
                                            :href="getHref(child.route)" 
                                            class="block py-2 px-2 text-sm font-medium border-l-[1.5px] border-dashed transition-all duration-150 rounded-r-lg"
                                            :class="{
                                                'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': isActive(child.route),
                                                'border-gray-200 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200': !isActive(child.route),
                                            }"
                                        >
                                            <span class="ml-3">{{ child.title }}</span>
                                        </Link>
                                    </li>
                                </ul>
                            </template>
                            
                            <!-- Regular menu item (no children) -->
                            <template v-else>
                                <div 
                                    @click="navigateTo(item.route)" 
                                    class="flex items-center justify-between py-2 px-2 text-base font-medium rounded-lg cursor-pointer transition-all duration-150 group"
                                    :class="{
                                        'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white': isActive(item.route),
                                        'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200': !isActive(item.route),
                                    }"
                                >
                                    <div class="flex items-center">
                                        <Icon 
                                            class="w-6 h-6 transition duration-75" 
                                            :class="{
                                                'text-gray-900 dark:text-white': isActive(item.route),
                                                'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white': !isActive(item.route),
                                            }" 
                                            :icon="item.icon" 
                                        />
                                        <span class="ml-3">{{ item.title }}</span>
                                    </div>
                                </div>
                            </template>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer with version -->
            <div class="px-3 py-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500">
                    <span>SiKerja v2.0</span>
                    <span v-if="role" class="bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded capitalize">{{ role }}</span>
                </div>
            </div>
        </div>
    </aside>
</template>
