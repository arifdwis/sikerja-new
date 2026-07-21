<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
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

const isEmailMissing = computed(() => {
    const user = page.props.auth?.user;
    return !user?.notification_email || !user?.notification_email.includes('@');
});

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
        <div class="py-12">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-8">
                
                <Breadcrumb :crumbs="[
                    { label: 'Dashboard', route: 'dashboard' },
                    { label: 'Profile', route: 'profile.edit' }
                ]" />

                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                        Profile & Pengaturan
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Kelola informasi profil dan pengaturan akun Anda.
                    </p>
                </div>

                <!-- Warning Peringatan Wajib Isi Email Notifikasi -->
                <div v-if="isEmailMissing" class="bg-red-50 dark:bg-red-900/30 border-2 border-red-400 dark:border-red-600 rounded-xl p-5 shadow-md">
                    <div class="flex items-start gap-4">
                        <Icon icon="solar:danger-triangle-bold" class="w-7 h-7 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                        <div>
                            <h3 class="font-bold text-red-800 dark:text-red-200 text-base">
                                Wajib Mengisi Email Notifikasi Terlebih Dahulu
                            </h3>
                            <p class="text-red-700 dark:text-red-300 text-sm mt-1 leading-relaxed">
                                Harap lengkapi <strong>Alamat Email Notifikasi</strong> Anda pada form <strong>Informasi Akun & Email Notifikasi</strong> di bawah ini agar Anda dapat menerima email notifikasi dari SIKERJA. 
                                <br />
                                <span class="text-xs text-red-600 dark:text-red-400 italic">* Catatan: Username / NIP SSO Anda di sistem tetap aman dan tidak akan terpengaruh.</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Warning untuk Pemohon yang biodatanya belum lengkap -->
                <div v-if="isProfileIncomplete && !isEmailMissing" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
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
                <div v-if="flash?.warning && !isEmailMissing" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-amber-700 dark:text-amber-300">
                        <Icon icon="solar:info-circle-bold" class="w-5 h-5" />
                        <span>{{ flash.warning }}</span>
                    </div>
                </div>

                <div v-if="flash?.success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-green-700 dark:text-green-300">
                        <Icon icon="solar:check-circle-bold" class="w-5 h-5" />
                        <span>{{ flash.success }}</span>
                    </div>
                </div>

                <!-- Form 1: Informasi Akun & Email Notifikasi (Wajib Tampil untuk SEMUA Role: Pengguna, Admin, Superadmin, TKKSD) -->
                <div 
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-all duration-300"
                    :class="{ 'ring-2 ring-red-500 border-red-500 shadow-xl': isEmailMissing }"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- Form 2: Khusus Pemohon (Biodata & Corporate) -->
                <template v-if="isPemohon">
                    <!-- Biodata Form -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6" :class="{ 'ring-2 ring-amber-400': isProfileIncomplete }">
                        <UpdateBiodataForm />
                    </div>

                    <!-- Corporate Form -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6" :class="{ 'ring-2 ring-amber-400': isProfileIncomplete }">
                        <UpdateCorporateForm />
                    </div>
                </template>

                <!-- Delete Account (hide for Pemohon) -->
                <div v-if="!isPemohon" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <DeleteUserForm class="max-w-xl" />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
