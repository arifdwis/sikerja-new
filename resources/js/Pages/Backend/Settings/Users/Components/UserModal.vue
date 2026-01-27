<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import AutoComplete from 'primevue/autocomplete';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const props = defineProps({
    visible: Boolean,
    user: Object,
    isEdit: Boolean,
    roles: Array,
});

const emit = defineEmits(['update:visible', 'success']);

const toast = useToast();
const share = computed(() => usePage().props.share);

const modalTitle = computed(() => props.isEdit ? 'Edit User' : 'Tambah User');

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: null,
    uid: null,
});

const ssoUsers = ref([]);
const selectedSsoUser = ref(null);
const isSsoLocked = ref(false);

watch(() => props.visible, (newVal) => {
    if (newVal) {
        form.clearErrors();
        if (props.isEdit && props.user) {
            form.name = props.user.name;
            form.email = props.user.email;
            form.uid = props.user.uid;
            form.role_id = props.user.roles?.[0]?.id || null;
            if (props.user.uid) {
                isSsoLocked.value = true;
                selectedSsoUser.value = { text: props.user.name, uid: props.user.uid };
            } else {
                isSsoLocked.value = false;
                selectedSsoUser.value = null;
            }
        } else {
            form.reset();
            clearSso();
        }
    }
});

const searchSso = async (event) => {
    try {
        const res = await axios.get(route('settings.users.search-sso'), {
            params: { query: event.query }
        });
        
        let results = res.data;
        if (Array.isArray(results) && results.length > 0) {
            if (typeof results[0] === 'string' && results[0].trim().startsWith('{')) {
                try { results = results.map(item => JSON.parse(item)); } catch (e) {}
            }
        }
        ssoUsers.value = results.slice(0, 50);
    } catch (e) {
        ssoUsers.value = [];
    }
};

const onSsoSelect = (event) => {
    const item = event.value;
    let name = item.text || item.name;
    let email = item.email || '';
    let uid = item.uid || item.id;

    if (typeof item.id === 'string' && item.id.includes('|||')) {
        const parts = item.id.split('|||');
        if (parts.length >= 3) {
            uid = parts[0];
            email = parts[1];
            name = parts[2];
        }
    }

    form.name = name;
    form.email = email;
    form.uid = uid;
    form.password = 'Substituted by SSO';
    form.password_confirmation = 'Substituted by SSO';
    isSsoLocked.value = true;
    toast.info("Data user SSO berhasil dimuat.");
};

const clearSso = () => {
    selectedSsoUser.value = null;
    isSsoLocked.value = false;
    form.uid = null;
    form.name = '';
    form.email = '';
    form.password = '';
    form.password_confirmation = '';
};

const close = () => {
    emit('update:visible', false);
    form.reset();
    clearSso();
};

const submit = () => {
    if (!form.role_id) {
        toast.warning('Pilih Role terlebih dahulu.');
        return;
    }

    if (props.isEdit) {
        form.put(route(share.value.prefix + '.update', props.user.id), {
            onSuccess: () => {
                toast.success('User berhasil diperbarui');
                emit('success');
                close();
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            },
        });
    } else {
        form.post(route(share.value.prefix + '.store'), {
            onSuccess: () => {
                toast.success('User berhasil ditambahkan');
                emit('success');
                close();
            },
            onError: (errors) => {
                Object.values(errors).forEach(msg => toast.error(msg));
            },
        });
    }
};

const roleOptions = computed(() => props.roles.map(role => ({
    label: role.name,
    value: role.id
})));
</script>

<template>
    <Dialog :visible="visible" @update:visible="emit('update:visible', $event)" :header="modalTitle" modal class="w-full md:w-1/2">
        <div class="flex flex-col gap-4 mt-2">
            <div v-if="!isEdit" class="p-3 bg-gray-50 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cari User SSO (Samarinda SSO)</label>
                <div class="flex gap-2">
                    <AutoComplete 
                        v-model="selectedSsoUser" 
                        :suggestions="ssoUsers" 
                        @complete="searchSso" 
                        @item-select="onSsoSelect" 
                        field="text" 
                        placeholder="Ketik Nama / NIP / Email..." 
                        class="w-full"
                        inputClass="w-full"
                        :disabled="isSsoLocked"
                    >
                        <template #item="slotProps">
                            <div class="flex flex-col">
                                <span class="font-semibold">{{ slotProps.item.text || slotProps.item.name }}</span>
                                <span v-if="slotProps.item.email" class="text-xs text-gray-500">{{ slotProps.item.email }}</span>
                            </div>
                        </template>
                    </AutoComplete>
                    <Button v-if="isSsoLocked" icon="pi pi-times" severity="secondary" @click="clearSso" v-tooltip="'Reset Form'" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                 <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                    <InputText v-model="form.name" :disabled="isSsoLocked" class="w-full" placeholder="Nama Lengkap" />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>
                
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <InputText v-model="form.email" :disabled="isSsoLocked" class="w-full" placeholder="Email Address" type="email" />
                     <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                </div>

                 <div class="flex flex-col gap-2 md:col-span-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                    <Dropdown 
                        v-model="form.role_id" 
                        :options="roleOptions" 
                        optionLabel="label" 
                        optionValue="value" 
                        placeholder="Pilih Role" 
                        class="w-full"
                        filter
                    />
                     <small v-if="form.errors.role_id" class="text-red-500">{{ form.errors.role_id }}</small>
                </div>
                
                <template v-if="!isSsoLocked">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <InputText v-model="form.password" class="w-full" placeholder="Password" type="password" />
                        <small class="text-xs text-gray-500" v-if="isEdit">Kosongkan jika tidak ingin mengganti.</small>
                         <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                        <InputText v-model="form.password_confirmation" class="w-full" placeholder="Konfirmasi Password" type="password" />
                    </div>
                </template>
            </div>
        </div>
        <template #footer>
            <div class="flex justify-end gap-2 pt-4">
                <Button label="Batal" icon="pi pi-times" text @click="close" severity="secondary" />
                <Button label="Simpan" icon="pi pi-check" @click="submit" :loading="form.processing" />
            </div>
        </template>
    </Dialog>
</template>
