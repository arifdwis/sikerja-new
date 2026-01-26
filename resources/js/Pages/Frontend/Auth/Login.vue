<script setup>
    import { ref, onMounted } from 'vue';
    import Checkbox from '@/Components/Checkbox.vue';
    import AuthLayout from '@/Layouts/AuthLayout.vue';
    import InputError from '@/Components/InputError.vue';
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import { useToast } from 'vue-toastification';
    import { auth } from '@/utils/firebase';
    import { signInWithPopup, GoogleAuthProvider } from "firebase/auth";
    import { router } from '@inertiajs/vue3';

    const toast = useToast();

    const isGoogleLoading = ref(false);

    const loginWithGoogle = async () => {
        if (isGoogleLoading.value) return;
        isGoogleLoading.value = true;
        const provider = new GoogleAuthProvider();
        try {
            const result = await signInWithPopup(auth, provider);
            const user = result.user;
            
            // Send user info to backend
            router.post(route('google.callback'), {
                email: user.email,
                name: user.displayName,
                uid: user.uid
            }, {
                onFinish: () => { isGoogleLoading.value = false; },
                onError: (errors) => {
                    toast.error("Gagal login dengan Google.");
                    console.error(errors);
                    isGoogleLoading.value = false;
                }
            });
        } catch (error) {
            console.error("Firebase Auth Error:", error);
            if (error.code === 'auth/cancelled-popup-request' || error.code === 'auth/popup-closed-by-user') {
                 toast.info("Login dibatalkan.");
            } else if (error.code === 'auth/popup-blocked') {
                 toast.error("Popup login diblokir oleh browser. Izinkan popup untuk melanjutkan.");
            } else {
                 toast.error("Terjadi kesalahan: " + error.message);
            }
            isGoogleLoading.value = false;
        }
    };

    defineProps({
        status: String,
        error: String,
    });

    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const disabled = ref(false);

    const clearEmailError = () => {
        form.errors.email = null;
        form.errors.password = null;
    };

    const clearPasswordError = () => {
        form.errors.email = null;
        form.errors.password = null;
    };

    const submit = () => {
        form.post(route('login'), {
            onFinish: () => form.reset('password'),
            onError: (errors) => {
                Object.values(errors).forEach(error => {
                    toast.error(error);
                });
                disabled.value = true;
                setTimeout(() => {
                    disabled.value = false;
                }, 1500);
            }
        });
    };

    onMounted(() => {
        localStorage.clear();
        sessionStorage.clear();
    });
</script>
<template>
    <AuthLayout>
        <Head title="Login - SiKerja" />
        
        <div class="flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden bg-slate-100 dark:bg-gray-900">
            <div class="flex flex-col items-center justify-center">
                <div :class="`rounded-[26px] p-0 shadow-none lg:shadow-2xl ${!form.errors.email && !form.errors.password ? 'lg:bg-gray-900 lg:dark:bg-white lg:dark:shadow-gray-600 lg:shadow-gray-400 lg:dark:p-1' : 'lg:p-1 lg:bg-red-500 lg:shadow-red-500'} ${disabled ? 'shake' : ''}`">
                    <div class="w-full lg:bg-white bg-slate-100 dark:bg-gray-900 py-16 px-8 lg:px-12 rounded-[23px]">
                        <div class="text-center mb-8">
                            <Icon icon="solar:buildings-3-bold" :class="`mb-4 w-24 h-24 shrink-0 mx-auto ${!form.errors.email && !form.errors.password ? 'text-gray-900 dark:text-gray-50' : 'text-red-500'}`" />
                            <div class="text-gray-900 dark:text-surface-0 text-2xl font-bold mb-2">SiKerja</div>
                            <span class="text-muted-color font-medium text-sm">Sistem Informasi Kerjasama Daerah</span>
                            <p class="text-gray-500 text-xs mt-1">Kota Samarinda</p>
                        </div>

                        <!-- SSO Login Button -->
                        <div class="mb-6">
                            <a 
                                :href="route('sso.authorize')"
                                class="w-full flex items-center justify-center px-6 py-3 bg-gray-900 dark:bg-white dark:text-gray-900 text-white font-medium rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-all duration-200"
                            >
                                <Icon icon="solar:login-2-bold" class="w-5 h-5 mr-3" />
                                Login dengan SSO Samarinda
                            </a>
                        </div>
                        
                        <!-- Google Login Button -->
                        <div class="mb-6">
                            <button 
                                type="button"
                                @click="loginWithGoogle"
                                :disabled="isGoogleLoading"
                                class="w-full flex items-center justify-center px-6 py-3 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <Icon v-if="isGoogleLoading" icon="svg-spinners:ring-resize" class="w-5 h-5 mr-3" />
                                <Icon v-else icon="flat-color-icons:google" class="w-5 h-5 mr-3" />
                                {{ isGoogleLoading ? 'Menghubungkan...' : 'Login dengan Google' }}
                            </button>
                        </div>



                        <!-- Register Link -->
                        <div class="mt-6 text-center">
                            <a 
                                href="https://sso.samarindakota.go.id"
                                target="_blank"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                            >
                                Belum punya akun? <span class="font-medium underline">Daftar SSO</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        © {{ new Date().getFullYear() }} Bagian Tata Pemerintahan
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Sekretariat Daerah Kota Samarinda
                    </p>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.shake {
    animation: shake 0.5s;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}
</style>
