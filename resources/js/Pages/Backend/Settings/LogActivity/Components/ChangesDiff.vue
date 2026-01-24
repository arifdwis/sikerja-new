<script setup>
import { computed } from 'vue';

const props = defineProps({
    properties: {
        type: Object,
        default: () => ({})
    }
});

const changes = computed(() => {
    // Standard Spatie ActivityLog structure: "attributes" (new), "old" (old)
    const attributes = props.properties.attributes || {};
    const old = props.properties.old || {};
    
    // If only attributes exist (e.g. Created), just show them
    if (!props.properties.old && props.properties.attributes) {
        return Object.keys(attributes).map(key => ({
            key,
            oldVal: '-',
            newVal: attributes[key],
            isDifferent: true
        }));
    }

    // Merge keys from both to find all fields involved
    const allKeys = new Set([...Object.keys(attributes), ...Object.keys(old)]);
    
    return Array.from(allKeys).map(key => {
        const oldVal = old[key];
        const newVal = attributes[key];
        
        // Simple equality check (can be improved for arrays/objects)
        const isDifferent = JSON.stringify(oldVal) !== JSON.stringify(newVal);
        
        return {
            key,
            oldVal: oldVal === undefined || oldVal === null ? '-' : oldVal,
            newVal: newVal === undefined || newVal === null ? '-' : newVal,
            isDifferent
        };
    }).filter(item => item.isDifferent); // Only show actual changes
});
</script>

<template>
    <div v-if="changes.length > 0" class="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-lg">
        <table class="min-w-full text-xs text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-200">
                <tr>
                    <th scope="col" class="px-4 py-2 border-r dark:border-gray-600">Attribute</th>
                    <th scope="col" class="px-4 py-2 border-r dark:border-gray-600">Old Value</th>
                    <th scope="col" class="px-4 py-2">New Value</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="change in changes" :key="change.key" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100 border-r dark:border-gray-600">
                        {{ change.key }}
                    </td>
                    <td class="px-4 py-2 text-red-600 dark:text-red-400 border-r dark:border-gray-600 bg-red-50 dark:bg-red-900/20">
                        {{ change.oldVal }}
                    </td>
                    <td class="px-4 py-2 text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20">
                        {{ change.newVal }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div v-else class="text-xs text-gray-500 italic p-2">
        No specific changes recorded (or raw properties format).
        <!-- Fallback for raw dumping if needed -->
        <pre v-if="Object.keys(properties).length" class="mt-2 p-2 bg-gray-100 dark:bg-gray-900 rounded">{{ properties }}</pre>
    </div>
</template>
