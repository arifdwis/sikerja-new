export const status_pengajuan = {
  0: {
    label: "Pengunggahan Dokumen oleh OPD",
    label_short: "Unggah Dokumen",
    color: "text-gray-50 bg-teal-600 dark:bg-teal-600",
    textColor: "text-teal-600 dark:text-teal-50",
    borderColor: "border-teal-600 dark:border-teal-400",
    desc: "Tahap awal di mana OPD mengunggah seluruh dokumen persyaratan sesuai ketentuan sebelum dilakukan proses validasi.",
    icon: "solar:upload-minimalistic-broken"
  },
  1: {
    label: "Validasi oleh Administrator",
    label_short: "Validasi Admin",
    color: "text-gray-50 bg-teal-700 dark:bg-teal-600",
    textColor: "text-teal-800 dark:text-teal-700",
    borderColor: "border-teal-700 dark:border-teal-700",
    desc: "Administrator memeriksa kelengkapan dan kesesuaian berkas yang diunggah oleh OPD.",
    icon: "solar:checklist-minimalistic-broken"
  },
  2: {
    label: "Verifikasi oleh Kepala Bagian Hukum",
    label_short: "Verifikasi Kabag",
    color: "text-gray-50 bg-sky-600 dark:bg-sky-700",
    textColor: "text-sky-700 dark:text-sky-700",
    borderColor: "border-sky-600 dark:border-sky-400",
    desc: "Kepala Bagian Hukum melakukan verifikasi substantif terhadap isi dokumen.",
    icon: "solar:shield-check-broken"
  },
  3: {
    label: "Penyusunan Draft oleh Tim Penyusun",
    label_short: "Penyusunan Draft",
    color: "text-gray-50 bg-sky-700 dark:bg-sky-600",
    textColor: "text-sky-800 dark:text-sky-700",
    borderColor: "border-sky-700 dark:border-sky-700",
    desc: "Tim penyusun membuat atau menyesuaikan draft dokumen.",
    icon: "solar:document-add-broken"
  },
  4: {
    label: "Paraf oleh Pejabat (Executive)",
    label_short: "Paraf Pejabat",
    color: "text-gray-50 bg-teal-600 dark:bg-teal-700",
    textColor: "text-teal-700 dark:text-teal-700",
    borderColor: "border-teal-600 dark:border-teal-400",
    desc: "Dokumen sedang dalam proses persetujuan dan paraf dari pimpinan.",
    icon: "solar:pen-broken"
  },
  5: {
    label: "Finalisasi dan Penandatanganan Dokumen",
    label_short: "Finalisasi",
    color: "text-gray-50 bg-teal-700 dark:bg-teal-600",
    textColor: "text-teal-800 dark:text-teal-700",
    borderColor: "border-teal-700 dark:border-teal-700",
    desc: "Dokumen telah disetujui dan ditandatangani secara resmi.",
    icon: "solar:pen-2-broken"
  },
  6: {
    label: "Selesai",
    label_short: "Selesai",
    color: "text-gray-50 bg-emerald-700 dark:bg-emerald-600",
    textColor: "text-emerald-800 dark:text-emerald-700",
    borderColor: "border-emerald-700 dark:border-emerald-700",
    desc: "Seluruh proses telah rampung dan dokumen resmi siap diterbitkan.",
    icon: "solar:check-circle-broken"
  },
};
