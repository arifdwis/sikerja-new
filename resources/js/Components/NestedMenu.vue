<script setup>
import draggable from 'vuedraggable';
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';

const props = defineProps({
    modelValue: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'edit', 'delete', 'add-child']);

const dragOptions = {
    animation: 200,
    group: "menu",
    disabled: false,
    ghostClass: "ghost",
    fallbackOnBody: true,
    swapThreshold: 0.65,
};

// Update handler for nested children
const updateChildList = (index, newChildren) => {
    const updated = [...props.modelValue];
    updated[index] = { ...updated[index], children: newChildren };
    emit('update:modelValue', updated);
};
</script>

<template>
    <draggable 
        class="dragArea gap-2 min-h-[10px]" 
        :modelValue="modelValue" 
        @update:modelValue="emit('update:modelValue', $event)"
        :group="{ name: 'menu' }" 
        item-key="key"
        handle=".handle"
        v-bind="dragOptions"
    >
        <template #item="{ element, index }">
            <div class="menu-node-wrapper mb-2">
                <!-- Node Content -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 flex items-center justify-between shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center gap-3 overflow-hidden flex-1">
                        <!-- Drag Handle -->
                        <div class="handle cursor-grab active:cursor-grabbing p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                             <Icon icon="solar:hamburger-menu-linear" class="w-5 h-5" />
                        </div>
                        
                        <!-- Icon -->
                         <div v-if="element.data.icon" class="flex-shrink-0 w-9 h-9 flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded-lg text-blue-600 dark:text-blue-400 border border-gray-100 dark:border-gray-600">
                             <Icon :icon="element.data.icon" class="w-5 h-5"/>
                         </div>
                         <div v-else class="flex-shrink-0 w-9 h-9 flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-400 dark:text-gray-500 border border-gray-100 dark:border-gray-600">
                             <Icon icon="solar:folder-path-connect-linear" class="w-5 h-5"/>
                         </div>
                         
                         <!-- Info -->
                         <div class="flex flex-col min-w-0">
                             <div class="flex items-center gap-2">
                                 <span class="font-semibold text-gray-800 dark:text-gray-100 truncate text-sm">{{ element.label }}</span>
                                 <span v-if="!element.data.is_active" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-600 uppercase border border-red-200">Non-Aktif</span>
                             </div>
                             <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                 <span v-if="element.data.route" class="font-mono bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-400">{{ element.data.route }}</span>
                                 
                                 <span v-if="element.data.roles" class="text-[10px] bg-blue-50 text-blue-700 border border-blue-100 px-1.5 py-0.5 rounded truncate max-w-[200px] bg-opacity-50">
                                     {{ element.data.roles }}
                                 </span>
                             </div>
                         </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                        <Button 
                            icon="pi pi-plus" 
                            size="small"
                            text
                            rounded
                            severity="success"
                            v-tooltip.top="'Tambah Submenu'"
                            @click="$emit('add-child', element)" 
                            class="!w-8 !h-8 hover:bg-green-50"
                        />
                        <Button 
                            icon="pi pi-pencil" 
                            size="small"
                            text
                            rounded
                            severity="secondary"
                            v-tooltip.top="'Edit'"
                            @click="$emit('edit', element)"
                            class="!w-8 !h-8 hover:bg-gray-100"
                        />
                        <Button 
                            icon="pi pi-trash" 
                            size="small"
                            text
                            rounded
                            severity="danger"
                            v-tooltip.top="'Hapus'"
                            @click="$emit('delete', element)"
                            class="!w-8 !h-8 hover:bg-red-50"
                        />
                    </div>
                </div>

                <!-- Children (Recursive) - Always render to allow dropping new items -->
                <div class="pl-8 border-l-2 border-dashed border-gray-200 dark:border-gray-700 ml-6 mt-2 pb-2">
                     <NestedMenu 
                        :modelValue="element.children || []" 
                        @update:modelValue="updateChildList(index, $event)"
                        @add-child="$emit('add-child', $event)" 
                        @edit="$emit('edit', $event)"
                        @delete="$emit('delete', $event)"
                    />
                </div>
            </div>
        </template>
    </draggable>
</template>

<style scoped>
.ghost {
  opacity: 0.5;
  background: #eff6ff;
  border: 2px dashed #3b82f6;
}
</style>
