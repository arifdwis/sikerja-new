<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import { useToast } from "vue-toastification";
import { ref, watch } from 'vue';

const toast = useToast();
const page = usePage();
const pemohon = page.props.pemohon;
const opds = page.props.opds || [];
const userIdOpd = page.props.userIdOpd;

const genderOptions = [
    { label: 'Laki-laki', value: 'L' },
    { label: 'Perempuan', value: 'P' }
];

// Cek apakah unit_kerja yang sudah ada cocok dengan salah satu OPD
const findMatchingOpd = (name) => {
    if (!name) return null;
    return opds.find(o => o.nama?.toUpperCase() === name.toUpperCase());
};
const initialOpd = userIdOpd
    ? opds.find(o => o.id === userIdOpd)
    : findMatchingOpd(pemohon?.unit_kerja);

const isManualMode = ref(!initialOpd && !!pemohon?.unit_kerja);

const form = useForm({
    name: pemohon?.name || page.props.auth?.user?.name || '',
    nickname: pemohon?.nickname || '',
    nik: pemohon?.nik || '',
    gender: pemohon?.gender || 'L',
    phone: pemohon?.phone || '',
    email: pemohon?.email || page.props.auth?.user?.email || '',
    kota_id: pemohon?.kota_id ? Number(pemohon.kota_id) : null,
    address: pemohon?.address || '',
    id_opd: initialOpd?.id ?? null,
    unit_kerja: pemohon?.unit_kerja || '',
    nip: pemohon?.nip || '',
    jabatan: pemohon?.jabatan || '',
});

// Auto-fill unit_kerja saat user pilih OPD
watch(() => form.id_opd, (newId) => {
    if (newId) {
        const opd = opds.find(o => o.id === newId);
        if (opd) {
            form.unit_kerja = opd.nama;
        }
    }
});

const switchToManual = () => {
    isManualMode.value = true;
    form.id_opd = null;
};

const switchToPick = () => {
    isManualMode.value = false;
    form.unit_kerja = '';
    form.id_opd = null;
};

const submit = () => {
    form.put(route('profile.biodata.update'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Biodata berhasil diperbarui'),
        onError: (errors) => {
            Object.values(errors).forEach(msg => toast.error(msg));
        }
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Data Diri / Biodata
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Lengkapi informasi data diri Anda sebagai pemohon kerjasama.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nama Lengkap -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.name" class="w-full" placeholder="Contoh: Dr. John Doe, M.Sc" />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>

                <!-- Nama Panggilan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Panggilan <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.nickname" class="w-full" placeholder="Contoh: John" />
                    <small v-if="form.errors.nickname" class="text-red-500">{{ form.errors.nickname }}</small>
                </div>

                <!-- NIK -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        NIK <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.nik" class="w-full" placeholder="Nomor Induk Kependudukan" />
                    <small v-if="form.errors.nik" class="text-red-500">{{ form.errors.nik }}</small>
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <Dropdown 
                        v-model="form.gender" 
                        :options="genderOptions" 
                        optionLabel="label" 
                        optionValue="value"
                        class="w-full" 
                        placeholder="Pilih Jenis Kelamin" 
                    />
                    <small v-if="form.errors.gender" class="text-red-500">{{ form.errors.gender }}</small>
                </div>

                <!-- Telepon -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Telepon <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.phone" class="w-full" placeholder="Contoh: 081234567890" />
                    <small v-if="form.errors.phone" class="text-red-500">{{ form.errors.phone }}</small>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.email" type="email" class="w-full" placeholder="email@example.com" />
                    <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                </div>

                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Alamat
                    </label>
                    <Textarea v-model="form.address" rows="3" class="w-full" placeholder="Alamat lengkap tempat tinggal" />
                </div>

                <div class="md:col-span-2">
                    <hr class="border-gray-200 dark:border-gray-700 my-2">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Informasi Kepegawaian</p>
                </div>

                <!-- Unit Kerja: dropdown OPD atau manual -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Unit Kerja <span class="text-red-500">*</span>
                        </label>
                        <button v-if="!isManualMode" type="button" @click="switchToManual" class="text-xs text-blue-600 hover:underline">
                            Tidak ada di daftar? Ketik manual
                        </button>
                        <button v-else type="button" @click="switchToPick" class="text-xs text-blue-600 hover:underline">
                            Pilih dari daftar OPD
                        </button>
                    </div>
                    <Dropdown
                        v-if="!isManualMode"
                        v-model="form.id_opd"
                        :options="opds"
                        optionLabel="nama"
                        optionValue="id"
                        class="w-full"
                        placeholder="Pilih OPD / Unit Kerja"
                        filter
                        showClear
                    >
                        <template #option="slotProps">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ slotProps.option.nama }}</span>
                                <span v-if="slotProps.option.singkatan" class="text-xs text-gray-500">({{ slotProps.option.singkatan }})</span>
                            </div>
                        </template>
                    </Dropdown>
                    <InputText v-else v-model="form.unit_kerja" class="w-full" placeholder="Contoh: Universitas Mulawarman" />
                    <small v-if="form.errors.unit_kerja" class="text-red-500">{{ form.errors.unit_kerja }}</small>
                    <small v-if="form.errors.id_opd" class="text-red-500">{{ form.errors.id_opd }}</small>
                </div>

                <!-- NIP -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        NIP/Nomor Kepegawaian
                    </label>
                    <InputText v-model="form.nip" class="w-full" placeholder="Nomor Induk Pegawai" />
                </div>

                <!-- Jabatan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Jabatan <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.jabatan" class="w-full" placeholder="Contoh: Kepala Bidang / Staff" />
                    <small v-if="form.errors.jabatan" class="text-red-500">{{ form.errors.jabatan }}</small>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" label="Simpan Biodata" :loading="form.processing" />

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
