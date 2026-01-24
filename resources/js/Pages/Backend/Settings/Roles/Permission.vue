<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { useForm, Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';
import { useToast } from "vue-toastification";
import Button from 'primevue/button';

const toast = useToast();

const props = defineProps(["role", "permissions"]);

const selectedOptions = ref([]);
const toggleAll = ref(false);

const allPermissions = computed(() =>
    Object.values(props.permissions || {}).flatMap((group) => group || [])
);

const isAllSelected = computed(() =>
    selectedOptions.value.length === allPermissions.value.length && allPermissions.value.length > 0
);

const isGroupSelected = (groupKey) => {
    return props.permissions[groupKey].every(option =>
        selectedOptions.value.includes(option.name)
    );
};

const toggleSelection = (option) => {
    const index = selectedOptions.value.indexOf(option.name);
    if (index === -1) {
        selectedOptions.value.push(option.name);
    } else {
        selectedOptions.value.splice(index, 1);
    }
    updateToggleAllState();
};

const toggleAllSelection = (value) => {
    toggleAll.value = value;
    selectedOptions.value = value ? allPermissions.value.map(option => option.name) : [];
};

const toggleGroupSelection = (groupKey) => {
    const groupOptions = props.permissions[groupKey].map(option => option.name);
    // Jika semua di group terpilih, maka deselect semua
    // Jika ada yang belum, select semua
    
    // Cek apakah group sudah full selected
    const allSelectedInGroup = groupOptions.every(name => selectedOptions.value.includes(name));
    
    if (allSelectedInGroup) {
        // Deselect all in group
        selectedOptions.value = selectedOptions.value.filter(name => !groupOptions.includes(name));
    } else {
        // Add missing ones
        const missing = groupOptions.filter(name => !selectedOptions.value.includes(name));
        selectedOptions.value = [...selectedOptions.value, ...missing];
    }
    updateToggleAllState();
};

const updateToggleAllState = () => {
    toggleAll.value = selectedOptions.value.length === allPermissions.value.length && allPermissions.value.length > 0;
};

// Initialize from existing permissions
onMounted(() => {
    if (props.role && props.role.permissions) {
        selectedOptions.value = props.role.permissions.map(permission => permission.name);
        updateToggleAllState();
    }
});

watch(selectedOptions, () => {
    updateToggleAllState();
});

const form = useForm({
    permissions: [],
});

const submit = () => {
    form.permissions = selectedOptions.value;
    form.put(route('settings.roles.permission.update', props.role.id), {
        onSuccess: () => toast.success("Hak akses role berhasil diperbarui."),
        onError: (errors) => {
            Object.values(errors).forEach((errorMessages) => {
                toast.error(errorMessages);
            });
        },
    });
};

</script>

<template>
    <Head :title="`Permissions - ${role.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Kelola Permission: <span class="capitalize text-blue-600">{{ role.name }}</span>
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-full sm:px-6 lg:px-8 space-y-4">
                <Breadcrumb :crumbs="[
                    { label: 'Pengaturan', route: 'settings.users.index' },
                    { label: 'Roles', route: 'settings.roles.index' },
                    { label: 'Permission', route: 'settings.roles.permission', args: [role.id] }
                ]" />
                
                <form @submit.prevent="submit">
                    <div class="max-w-full w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 dark:text-gray-100">
                        <div class="p-4 mb-4 text-sm text-gray-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-300 border border-blue-200" role="alert">
                            <span class="font-bold">Info:</span> Pilih permission yang ingin diberikan kepada role ini. Kelompok permission ditampilkan berdasarkan prefix nama (contoh: <code>users.index</code> masuk grup <code>users</code>).
                        </div>
                        
                        <div class="relative overflow-x-auto">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th class="py-2 px-4 border dark:border-gray-700 w-16 text-center">
                                            <!-- Toggle All -->
                                            <fwb-toggle color="teal" v-model="toggleAll" @update:model-value="toggleAllSelection" />
                                        </th>
                                        <th class="py-2 px-4 border dark:border-gray-700 w-32">Group</th>
                                        <th class="py-2 px-4 border dark:border-gray-700">Permissions</th>
                                    </tr>
                                </thead>
                                <tbody class="text-xs text-gray-700 dark:text-gray-200">
                                    <tr v-for="(groups, groupKey) in props.permissions" :key="groupKey" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="py-4 px-4 border dark:border-gray-700 text-center align-top">
                                            <fwb-toggle color="teal" :model-value="isGroupSelected(groupKey)" @update:model-value="toggleGroupSelection(groupKey)" />
                                        </td>
                                        <td class="py-4 px-4 border dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 align-top font-bold uppercase text-gray-600 dark:text-gray-300">
                                            {{ groupKey }}
                                        </td>
                                        <td class="py-4 px-4 border dark:border-gray-700">
                                            <div class="flex flex-wrap gap-2">
                                                <button 
                                                    type="button" 
                                                    v-for="option in groups || []" 
                                                    :key="option.name" 
                                                    @click="toggleSelection(option)" 
                                                    :class="{
                                                        'bg-teal-600 text-white shadow-md': selectedOptions.includes(option.name), 
                                                        'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300': !selectedOptions.includes(option.name)
                                                    }" 
                                                    class="py-1.5 px-3 rounded-md text-xs font-semibold cursor-pointer transition-all duration-200 border border-transparent"
                                                >
                                                    {{ option.name }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="Object.keys(props.permissions).length === 0">
                                        <td colspan="3" class="text-center py-8 text-gray-500">
                                            Belum ada data Permissions. Silakan tambahkan di menu Permissions.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div class="mt-6 flex justify-end">
                                <Button severity="contrast" type="submit" label="Simpan Perubahan" icon="pi pi-save" :loading="form.processing" size="large" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
