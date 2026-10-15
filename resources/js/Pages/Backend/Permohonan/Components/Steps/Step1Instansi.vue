<script setup>
import { Icon } from '@iconify/vue';
import SearchSelect from '@/Components/SearchSelect.vue';

defineProps({
    form: Object,
    kategoriOptions: Array,
    provinsiOptions: Array,
    kotaOptions: Array,
    loadingKotas: Boolean
});
</script>

<template>
    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <Icon icon="solar:buildings-bold" class="w-5 h-5 mr-2 text-blue-500" />
            Data Instansi / Perusahaan / Lembaga
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">(<span class="text-red-500 font-bold">*</span>) Tidak boleh kosong</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Judul Kerjasama <span class="text-red-500">*</span>
                </label>
                <input v-model="form.label" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: Kerja Sama Pengadaan Barang Dan Jasa..." />
                <p v-if="form.errors.label" class="text-red-500 text-sm mt-1">{{ form.errors.label }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Urusan/Kategori <span class="text-red-500">*</span>
                </label>
                <SearchSelect 
                    v-model="form.id_kategori" 
                    :options="kategoriOptions" 
                    placeholder="-- Pilih Salah Satu --"
                />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Nama Instansi <span class="text-red-500">*</span>
                </label>
                <input v-model="form.nama_instansi" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: Dinas Pariwisata" />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Provinsi <span class="text-red-500">*</span>
                </label>
                <SearchSelect 
                    v-model="form.id_provinsi" 
                    :options="provinsiOptions" 
                    placeholder="-- Pilih Provinsi --"
                />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Kabupaten/Kota <span class="text-red-500">*</span>
                </label>
                <SearchSelect 
                    v-model="form.id_kota" 
                    :options="kotaOptions" 
                    :disabled="loadingKotas || !kotaOptions.length"
                    placeholder="-- Pilih Kota --"
                />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Kode Pos <span class="text-red-500">*</span>
                </label>
                <input v-model="form.kode_pos" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: 75123" />
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Alamat <span class="text-red-500">*</span>
                </label>
                <textarea v-model="form.alamat" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: Jl. Kusuma Bangsa No.66, Samarinda"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Telepon <span class="text-red-500">*</span>
                </label>
                <input v-model="form.telepon" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: 081259xxxx" />
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <input v-model="form.email" type="email" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Contoh: email@instansi.go.id" />
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Website</label>
                <input v-model="form.website" type="url" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="https://www.instansi.go.id" />
            </div>
        </div>

        <!-- Section: Data Pimpinan Instansi / Mitra -->
        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                <Icon icon="solar:user-bold" class="w-5 h-5 mr-2 text-indigo-500" />
                Data Pimpinan Instansi / Mitra
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Pejabat penandatangan kesepakatan (Direktur, Rektor, Kepala Badan/Dinas, Ketua Yayasan)</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Nama Lengkap Pimpinan <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.nama_pimpinan" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="Contoh: Dr. H. Ahmad..." />
                    <p v-if="form.errors?.nama_pimpinan" class="text-red-500 text-xs mt-1">{{ form.errors.nama_pimpinan }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Jabatan Pimpinan <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.jabatan_pimpinan" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="Contoh: Direktur Utama / Rektor" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        NIP / NIK Pimpinan
                    </label>
                    <input v-model="form.nip_pimpinan" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="Nomor identitas resmi" />
                </div>
            </div>
        </div>

        <!-- Section: Data PIC 2 Orang Sesuai Proses Bisnis SIKERJA -->
        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                <Icon icon="solar:users-group-two-rounded-bold" class="w-5 h-5 mr-2 text-teal-500" />
                Data PIC (Person In Charge) — 2 Orang
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Wajib mencantumkan 2 orang narahubung teknis untuk koordinasi pembahasan draft dan penjadwalan</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- PIC 1 -->
                <div class="p-4 rounded-xl bg-white dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 space-y-3">
                    <div class="flex items-center gap-2 text-sm font-bold text-teal-600 dark:text-teal-400">
                        <span class="w-6 h-6 rounded-full bg-teal-100 dark:bg-teal-900/60 flex items-center justify-center text-xs">1</span>
                        PIC 1 (Utama) <span class="text-red-500">*</span>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                        <input v-model="form.pic1_nama" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs" placeholder="Nama PIC 1" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Jabatan</label>
                        <input v-model="form.pic1_jabatan" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs" placeholder="Jabatan PIC 1" />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">No. WhatsApp / HP</label>
                            <input v-model="form.pic1_phone" type="tel" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs" placeholder="0812xxxx" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email Aktif</label>
                            <input v-model="form.pic1_email" type="email" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs" placeholder="pic1@email.com" />
                        </div>
                    </div>
                </div>

                <!-- PIC 2 -->
                <div class="p-4 rounded-xl bg-white dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 space-y-3">
                    <div class="flex items-center gap-2 text-sm font-bold text-sky-600 dark:text-sky-400">
                        <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-900/60 flex items-center justify-center text-xs">2</span>
                        PIC 2 (Pendamping) <span class="text-red-500">*</span>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                        <input v-model="form.pic2_nama" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs" placeholder="Nama PIC 2" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Jabatan</label>
                        <input v-model="form.pic2_jabatan" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs" placeholder="Jabatan PIC 2" />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">No. WhatsApp / HP</label>
                            <input v-model="form.pic2_phone" type="tel" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs" placeholder="0813xxxx" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email Aktif</label>
                            <input v-model="form.pic2_email" type="email" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs" placeholder="pic2@email.com" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
