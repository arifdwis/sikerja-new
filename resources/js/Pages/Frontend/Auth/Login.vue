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
        
        <div class="relative flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden bg-[#f0f2f4] dark:bg-gray-900">
            <!-- Full inline pattern background -->
            <div class="pointer-events-none absolute inset-0 opacity-60">
                <svg class="w-full h-full" viewBox="0 0 1600 900" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
                    <defs>
                        <g id="ornament" stroke="#94A3B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M137 352C184 264 262 219 344 229C408 237 470 282 495 343"/>
                            <path d="M108 377C171 263 274 193 383 209C462 221 532 279 562 360"/>
                            <path d="M160 399C218 334 299 299 378 308C435 315 486 348 517 396"/>
                            <path d="M201 437C241 405 294 388 348 393C382 396 414 408 441 428"/>
                            <path d="M96 270C122 286 137 315 135 345C132 373 116 398 90 412"/>
                            <path d="M79 236C120 260 144 304 140 350C137 394 113 433 74 456"/>
                            <circle cx="194" cy="325" r="28"/>
                            <circle cx="194" cy="325" r="15"/>
                            <path d="M251 250C267 233 292 224 316 226C336 227 355 237 367 252"/>
                            <path d="M304 248C302 226 315 205 335 198C355 190 379 196 393 213"/>
                            <path d="M315 443C321 467 340 486 365 490"/>
                            <path d="M286 445C289 480 314 511 348 519"/>
                        </g>
                    </defs>

                    <use href="#ornament" opacity="0.45" transform="translate(-80,130) scale(1.2)"/>
                    <use href="#ornament" opacity="0.45" transform="translate(1680,130) scale(-1.2,1.2)"/>

                    <use href="#ornament" opacity="0.24" transform="translate(160,-180) scale(0.9)"/>
                    <use href="#ornament" opacity="0.24" transform="translate(1440,-180) scale(-0.9,0.9)"/>

                    <use href="#ornament" opacity="0.22" transform="translate(180,560) scale(0.9,-0.9)"/>
                    <use href="#ornament" opacity="0.22" transform="translate(1420,560) scale(-0.9,-0.9)"/>

                    <use href="#ornament" opacity="0.14" transform="translate(540,240) scale(0.72)"/>
                    <use href="#ornament" opacity="0.14" transform="translate(1060,240) scale(-0.72,0.72)"/>
                </svg>
            </div>

            <!-- Soft overlay to keep form readable -->
            <div class="pointer-events-none absolute inset-0 bg-[#f0f2f4]/52 dark:bg-gray-900/65"></div>

            <div class="flex flex-col items-center justify-center">
                <div :class="`relative z-10 rounded-[26px] p-0 shadow-none lg:shadow-2xl ${!form.errors.email && !form.errors.password ? 'lg:bg-gray-300/70 lg:dark:bg-white lg:dark:shadow-gray-600 lg:shadow-gray-300 lg:dark:p-1' : 'lg:p-1 lg:bg-red-500 lg:shadow-red-500'} ${disabled ? 'shake' : ''}`">
                    <div class="w-full lg:bg-white bg-[#f0f2f4] dark:bg-gray-900 py-16 px-8 lg:px-12 rounded-[23px]">
                        <div class="text-center mb-8">
                            <img src="/foto/logo-sikerja.svg" alt="SiKerja" class="mb-4 w-24 h-24 shrink-0 mx-auto rounded-2xl shadow-lg" />
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
