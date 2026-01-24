<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateBiodataForm from './Partials/UpdateBiodataForm.vue';
import UpdateCorporateForm from './Partials/UpdateCorporateForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import Breadcrumb from '@/Flowbite/Breadcrumb/Solid.vue';

const page = usePage();
const userRole = computed(() => page.props.auth?.role);
const isPemohon = computed(() => userRole.value === 'pemohon');
const flash = computed(() => page.props.flash);

// Check if profile is incomplete for pemohon
const isProfileIncomplete = computed(() => {
    if (!isPemohon.value) return false;
    const pemohon = page.props.pemohon;
    const corporate = page.props.corporate;
    // Must have both biodata and corporate complete
    return !pemohon || !pemohon.unit_kerja || !corporate || !corporate.name || !corporate.address;
});

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    pemohon: {
        type: Object,
        default: null
    },
    corporate: {
        type: Object,
        default: null
    },
    provinsis: {
        type: Array,
        default: () => []
    },
    kotas: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                
                <Breadcrumb :crumbs="[
                    { label: 'Dashboard', route: 'dashboard' },
                    { label: 'Profile', route: 'profile.edit' }
                ]" />

                <!-- Header -->
                <div class="px-4 sm:px-0">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                        Profile & Pengaturan
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Kelola informasi profil dan pengaturan akun Anda.
                    </p>
                </div>

                <!-- Warning untuk Pemohon yang belum lengkap -->
                <div v-if="isProfileIncomplete" class="mx-4 sm:mx-0 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <Icon icon="solar:warning-triangle-bold" class="w-6 h-6 text-amber-500 flex-shrink-0 mt-0.5" />
                        <div>
                            <h3 class="font-semibold text-amber-800 dark:text-amber-200">
                                Lengkapi Data Anda Terlebih Dahulu
                            </h3>
                            <p class="text-amber-700 dark:text-amber-300 text-sm mt-1">
                                Anda harus melengkapi <strong>Data Diri</strong> dan <strong>Data Satuan Kerja</strong> sebelum dapat mengajukan permohonan kerjasama. 
                                Silakan isi semua form di bawah ini dengan lengkap.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Flash Message -->
                <div v-if="flash?.warning" class="mx-4 sm:mx-0 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-amber-700 dark:text-amber-300">
                        <Icon icon="solar:info-circle-bold" class="w-5 h-5" />
                        <span>{{ flash.warning }}</span>
                    </div>
                </div>

                <div v-if="flash?.success" class="mx-4 sm:mx-0 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-green-700 dark:text-green-300">
                        <Icon icon="solar:check-circle-bold" class="w-5 h-5" />
                        <span>{{ flash.success }}</span>
                    </div>
                </div>

                <!-- For Pemohon: Show Biodata and Corporate Forms -->
                <template v-if="isPemohon">
                    <!-- Biodata Form -->
                    <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800" :class="{ 'ring-2 ring-amber-400': isProfileIncomplete }">
                        <UpdateBiodataForm />
                    </div>

                    <!-- Corporate Form -->
                    <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800" :class="{ 'ring-2 ring-amber-400': isProfileIncomplete }">
                        <UpdateCorporateForm />
                    </div>
                </template>

                <!-- For Non-Pemohon: Show standard profile form -->
                <template v-else>
                    <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl"
                        />
                    </div>
                </template>

                <!-- Update Password -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <!-- Delete Account (hide for Pemohon) -->
                <div v-if="!isPemohon" class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
