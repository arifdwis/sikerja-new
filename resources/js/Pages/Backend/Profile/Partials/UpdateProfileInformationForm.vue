<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    notification_email: user.notification_email || (user.email && user.email.includes('@') ? user.email : ''),
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                Informasi Akun & Email Notifikasi
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Kelola nama akun dan alamat email tempat Anda ingin menerima notifikasi aplikasi SIKERJA.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Nama Lengkap" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="notification_email" value="Alamat Email Notifikasi (Wajib Isi)" />

                <TextInput
                    id="notification_email"
                    type="email"
                    class="mt-1 block w-full border-blue-300 dark:border-blue-700 focus:border-blue-500"
                    v-model="form.notification_email"
                    required
                    placeholder="contoh: emailanda@samarindakota.go.id"
                />
                <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                    * Seluruh notifikasi sistem (permohonan, persetujuan, jadwal, monev) akan dikirimkan ke alamat email ini.
                </p>

                <InputError class="mt-2" :message="form.errors.notification_email" />
            </div>

            <div>
                <InputLabel for="email" value="Email / Username SSO (NIP/NIK)" />

                <TextInput
                    id="email"
                    type="text"
                    class="mt-1 block w-full bg-gray-50 text-gray-500 cursor-not-allowed dark:bg-gray-700 dark:text-gray-400"
                    v-model="form.email"
                    disabled
                    readonly
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    * Identitas akun SSO (NIP/NIK). Field ini dikunci agar tidak merusak login SSO Anda.
                </p>

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Simpan Email Notifikasi</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-green-600 dark:text-green-400 font-medium"
                    >
                        Email notifikasi berhasil disimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
