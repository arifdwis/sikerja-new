<script setup>
    import { ref, onMounted } from 'vue';
    import Checkbox from '@/Components/Checkbox.vue';
    import AuthLayout from '@/Layouts/AuthLayout.vue';
    import InputError from '@/Components/InputError.vue';
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import { useToast } from 'vue-toastification';

    const toast = useToast();

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

                        <div class="relative my-6">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300 dark:border-gray-700"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-white dark:bg-gray-900 text-gray-500">atau login manual</span>
                            </div>
                        </div>

                        <form @submit.prevent="submit">
                            <div>
                                <label for="email" class="block text-gray-900 dark:text-surface-0 text-sm font-medium mb-2">Email</label>
                                <input 
                                    id="email" 
                                    type="email"
                                    :class="['w-full px-4 py-3 rounded-lg border focus:ring-2 focus:ring-gray-900 dark:focus:ring-white focus:border-transparent dark:bg-gray-800 dark:text-white transition', form.errors.email ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                                    placeholder="Email address" 
                                    v-model="form.email" 
                                    required 
                                    autofocus 
                                    autocomplete="username" 
                                    @keyup="clearEmailError" 
                                />
                                <InputError class="mt-2 font-bold" :message="form.errors.email" />
                            </div>
                            <div class="mt-4">
                                <label for="password" class="block text-gray-900 dark:text-surface-0 font-medium text-sm mb-2">Password</label>
                                <input 
                                    id="password" 
                                    type="password"
                                    :class="['w-full px-4 py-3 rounded-lg border focus:ring-2 focus:ring-gray-900 dark:focus:ring-white focus:border-transparent dark:bg-gray-800 dark:text-white transition', form.errors.password ? 'border-red-500' : 'border-gray-300 dark:border-gray-600']"
                                    placeholder="Password" 
                                    v-model="form.password" 
                                    required 
                                    @keyup="clearPasswordError" 
                                />
                                <InputError class="mt-2 font-bold" :message="form.errors.password" />
                            </div>
                            <div class="flex items-center justify-between mt-4 mb-6 gap-8">
                                <div class="flex items-center">
                                    <Checkbox v-model:checked="form.remember" id="rememberme" class="mr-2"></Checkbox>
                                    <label for="rememberme" class="text-gray-600 dark:text-gray-400 text-sm">Remember me</label>
                                </div>
                            </div>
                            <button 
                                type="submit" 
                                class="w-full py-3 bg-gray-900 dark:bg-white dark:text-gray-900 text-white font-medium rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-all duration-200"
                                :class="{'opacity-25': form.processing}" 
                                :disabled="form.processing"
                            >
                                Sign In
                            </button>
                        </form>

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
