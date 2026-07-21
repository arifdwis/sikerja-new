/**
 * Pusat utility untuk mapping status permohonan ke class & label visual.
 * Dipakai di GridItem, Dashboard, Modal Detail, Riwayat agar warna konsisten.
 *
 * Status code:
 *   0 = Permohonan Baru / Menunggu Validasi
 *   1 = Dalam Pembahasan
 *   2 = Menunggu Jadwal Penandatanganan
 *   3 = Upload PKS Final (admin)
 *   4 = Menunggu Penandatanganan
 *   5 = Validasi Dokumen Pasca-Tandatangan
 *   6 = Pelaksanaan Kerjasama
 *   7 = Selesai
 *   8 = Dicabut
 *   9 = Ditolak / Revisi
 */

export const STATUS_LABEL = {
    0: 'Menunggu Validasi',
    1: 'Dalam Pembahasan',
    2: 'Menunggu Jadwal Penandatanganan',
    3: 'Upload PKS Final',
    4: 'Menunggu Penandatanganan',
    5: 'Validasi Dokumen Pasca-Tandatangan',
    6: 'Pelaksanaan Kerjasama',
    7: 'Selesai',
    8: 'Dicabut',
    9: 'Ditolak',
};

/**
 * Mapping ke set kelas Tailwind. `solid` = badge dengan background penuh,
 * `subtle` = badge dengan background tipis (bg-*-50) + text gelap,
 * `text` = warna teks saja.
 */
export const STATUS_THEME = {
    0: { solid: 'bg-amber-500 text-white',     subtle: 'bg-amber-50 text-amber-700 ring-amber-200',     text: 'text-amber-700',     accent: 'amber'   },
    1: { solid: 'bg-sky-500 text-white',       subtle: 'bg-sky-50 text-sky-700 ring-sky-200',           text: 'text-sky-700',       accent: 'sky'     },
    2: { solid: 'bg-blue-600 text-white',      subtle: 'bg-blue-50 text-blue-700 ring-blue-200',        text: 'text-blue-700',      accent: 'blue'    },
    3: { solid: 'bg-violet-600 text-white',    subtle: 'bg-violet-50 text-violet-700 ring-violet-200',  text: 'text-violet-700',    accent: 'violet'  },
    4: { solid: 'bg-pink-500 text-white',      subtle: 'bg-pink-50 text-pink-700 ring-pink-200',        text: 'text-pink-700',      accent: 'pink'    },
    5: { solid: 'bg-orange-500 text-white',    subtle: 'bg-orange-50 text-orange-700 ring-orange-200',  text: 'text-orange-700',    accent: 'orange'  },
    6: { solid: 'bg-teal-500 text-white',      subtle: 'bg-teal-50 text-teal-700 ring-teal-200',        text: 'text-teal-700',      accent: 'teal'    },
    7: { solid: 'bg-emerald-600 text-white',   subtle: 'bg-emerald-50 text-emerald-700 ring-emerald-200', text: 'text-emerald-700', accent: 'emerald' },
    8: { solid: 'bg-rose-700 text-white',      subtle: 'bg-rose-50 text-rose-700 ring-rose-200',        text: 'text-rose-700',      accent: 'rose'    },
    9: { solid: 'bg-red-600 text-white',       subtle: 'bg-red-50 text-red-700 ring-red-200',           text: 'text-red-700',       accent: 'red'     },
};

/**
 * PrimeVue Tag severity untuk konsistensi dengan Tag-based UI.
 */
export const STATUS_SEVERITY = {
    0: 'warning',
    1: 'info',
    2: 'info',
    3: 'contrast',
    4: 'contrast',
    5: 'contrast',
    6: 'success',
    7: 'success',
    8: 'danger',
    9: 'danger',
};

export function getStatusLabel(status) {
    const code = Number(status ?? 0);
    return STATUS_LABEL[code] ?? 'Unknown';
}

export function getStatusTheme(status) {
    const code = Number(status ?? 0);
    return STATUS_THEME[code] ?? STATUS_THEME[0];
}

export function getStatusSeverity(status) {
    const code = Number(status ?? 0);
    return STATUS_SEVERITY[code] ?? 'secondary';
}
