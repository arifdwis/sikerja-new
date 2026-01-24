<script setup>
import { Icon } from '@iconify/vue';

const props = defineProps({
  counts: {
    type: Object,
    required: true,
    default: () => ({
      total: 0,
      permohonan: 0,
      pembahasan: 0,
      penjadwalan: 0,
      disetujui: 0,
      selesai: 0,
      ditolak: 0
    })
  }
});

// Mapping Sikerja status to Si Cerdas Style Cards
// 0: Permohonan Baru (Teal)
// 1: Pembahasan (Cyan)
// 2: Penjadwalan (Sky)
// 3: Disetujui (Blue) -> Using Purple for distinction? Or keep flow.
// 4: Selesai (Emerald)
// 9: Ditolak (Red - Custom addition as Si Cerdas didn't have rejection in the list?)

const status_pengajuan = {
  permohonan: { label: "Permohonan Baru",      label_short: "Baru Masuk",   color: "from-teal-800 to-teal-600",   icon: "solar:upload-minimalistic-broken" },
  pembahasan: { label: "Dalam Pembahasan",   label_short: "Pembahasan",   color: "from-cyan-800 to-cyan-600",   icon: "solar:checklist-minimalistic-broken" },
  penjadwalan: { label: "Menunggu Penjadwalan",    label_short: "Penjadwalan", color: "from-sky-800 to-sky-600",     icon: "solar:shield-check-broken" },
  disetujui: { label: "Disetujui / TTD",          label_short: "Disetujui", color: "from-blue-800 to-blue-600",   icon: "solar:document-add-broken" },
  selesai: { label: "Selesai",                   label_short: "Selesai",          color: "from-emerald-800 to-emerald-600", icon: "solar:check-circle-broken" },
  ditolak: { label: "Ditolak / Revisi",        label_short: "Ditolak",    color: "from-red-800 to-red-600", icon: "solar:close-circle-broken" },
};
</script>

<template>
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
    <template v-for="(config, key) in status_pengajuan" :key="key">
      <div class="relative flex cursor-pointer flex-col bg-clip-border rounded-xl text-white shadow-md overflow-hidden
                  hover:shadow-xl transition-shadow duration-300 bg-white dark:bg-gray-800">
        <!-- Gradient Background -->
        <div :class="`bg-gradient-to-tr ${config.color} px-6 py-4 h-full`">
          <div class="flex justify-between items-start">
            <!-- Angka + Label -->
            <div class="text-left">
              <h1 class="text-3xl font-bold tracking-tight text-white/90">
                {{ counts[key] || 0 }}
              </h1>
              <p class="mt-2 text-xs uppercase font-semibold text-white/80">
                {{ config.label_short }}
              </p>
            </div>

            <!-- Icon -->
            <div class="flex items-center justify-center">
              <Icon :icon="config.icon" class="w-10 h-10 text-white/60" />
            </div>
          </div>
        </div>

        <!-- Hover: tampilkan label panjang -->
        <div class="absolute inset-0 bg-black/60 opacity-0 hover:opacity-100 transition-opacity duration-300 
                    flex items-center justify-center px-4 rounded-xl backdrop-blur-sm">
          <p class="text-center text-sm font-medium text-white">
            {{ config.label }}
          </p>
        </div>
      </div>
    </template>
  </div>
</template>
