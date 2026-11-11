/**
 * Pusat utility untuk mapping status permohonan ke class & label visual.
 * Dipakai di GridItem, ListItem, Dashboard, Modal Detail, Riwayat agar warna konsisten.
 *
 * Status code (Perwali Samarinda No. 7/2020):
 *  -1 = Draft Pemohon
 *   0 = Diajukan Pemohon / Menunggu Verifikasi Administrasi Staf
 *  10 = Lolos Administrasi / Menunggu Validasi & Disposisi Kabag Kerja Sama
 *  11 = Dikembalikan untuk Perbaikan / Revisi
 *  12 = Terverifikasi Kabag (Siap Bahas KB)
 *   1 = Pembahasan Draft Kesepakatan Bersama / PKS (TKKSD)
 *  13 = Finalisasi Draft KB
 *   2 = Pengajuan Jadwal Penandatanganan
 *   3 = Jadwal Disetujui (Menunggu Upload Naskah Final oleh Admin)
 *   4 = Menunggu Penandatanganan (Pemohon Upload Dokumen Tertandatangani)
 *   5 = Validasi Dokumen Pasca-Tandatangan (KB Aktif / Menunggu Persetujuan Pelaksanaan)
 *  20 = Pengajuan Draft PKS Turunan
 *  21 = Pembahasan Teknis PKS
 *  22 = Draft PKS Final
 *  23 = Penandatanganan PKS OPD
 *   6 = Pelaksanaan Kerjasama (PKS Aktif)
 *  25 = Evaluasi Akhir (H-90 Hari)
 *  26 = Usulan Perpanjangan
 *   7 = Selesai
 *   8 = Dicabut
 *   9 = Ditolak
 */

export const STATUS_LABEL = {
    '-1': 'Draft Permohonan',
    0: 'Menunggu Verifikasi Administrasi',
    10: 'Menunggu Validasi Kabag',
    11: 'Perlu Perbaikan / Revisi',
    12: 'Terverifikasi (Siap Bahas KB)',
    1: 'Dalam Pembahasan TKKSD',
    13: 'Draft KB Final Disetujui',
    2: 'Menunggu Jadwal Penandatanganan',
    3: 'Jadwal Disetujui (Siapkan Naskah Final)',
    4: 'Menunggu Penandatanganan',
    5: 'Validasi Dokumen Pasca-Tandatangan',
    20: 'Pengajuan Draf PKS Turunan',
    21: 'Pembahasan Teknis PKS',
    22: 'Draf PKS Final',
    23: 'Penandatanganan PKS OPD',
    6: 'Pelaksanaan Kerjasama',
    25: 'Evaluasi Akhir (H-90 Hari)',
    26: 'Usulan Perpanjangan',
    7: 'Selesai',
    8: 'Dicabut',
    9: 'Ditolak',
};

/**
 * Mapping ke set kelas Tailwind. `solid` = badge dengan background penuh,
 * `subtle` = badge dengan background tipis (bg-*-50) + text gelap,
 * `card` = background & border untuk kartu GridItem/ListItem,
 * `text` = warna teks saja.
 */
export const STATUS_THEME = {
    '-1': { solid: 'bg-slate-500 text-white',     subtle: 'bg-slate-50 text-slate-700 ring-slate-200',       cardBg: 'bg-slate-300',   cardText: 'text-slate-950',   cardBorder: 'border-slate-500',   text: 'text-slate-700',     accent: 'slate'   },
    0:    { solid: 'bg-amber-500 text-white',     subtle: 'bg-amber-50 text-amber-700 ring-amber-200',       cardBg: 'bg-amber-300',   cardText: 'text-amber-950',   cardBorder: 'border-amber-500',   text: 'text-amber-700',     accent: 'amber'   },
    10:   { solid: 'bg-indigo-600 text-white',    subtle: 'bg-indigo-50 text-indigo-700 ring-indigo-200',    cardBg: 'bg-indigo-300',  cardText: 'text-indigo-950',  cardBorder: 'border-indigo-500',  text: 'text-indigo-700',    accent: 'indigo'  },
    11:   { solid: 'bg-orange-500 text-white',    subtle: 'bg-orange-50 text-orange-700 ring-orange-200',    cardBg: 'bg-amber-400',   cardText: 'text-amber-950',   cardBorder: 'border-amber-600',   text: 'text-orange-700',    accent: 'orange'  },
    12:   { solid: 'bg-cyan-600 text-white',      subtle: 'bg-cyan-50 text-cyan-700 ring-cyan-200',          cardBg: 'bg-cyan-300',    cardText: 'text-cyan-950',    cardBorder: 'border-cyan-500',    text: 'text-cyan-700',      accent: 'cyan'    },
    1:    { solid: 'bg-sky-500 text-white',       subtle: 'bg-sky-50 text-sky-700 ring-sky-200',             cardBg: 'bg-sky-300',     cardText: 'text-sky-950',     cardBorder: 'border-sky-500',     text: 'text-sky-700',       accent: 'sky'     },
    13:   { solid: 'bg-purple-600 text-white',    subtle: 'bg-purple-50 text-purple-700 ring-purple-200',    cardBg: 'bg-purple-300',  cardText: 'text-purple-950',  cardBorder: 'border-purple-500',  text: 'text-purple-700',    accent: 'purple'  },
    2:    { solid: 'bg-blue-600 text-white',      subtle: 'bg-blue-50 text-blue-700 ring-blue-200',          cardBg: 'bg-blue-400',    cardText: 'text-blue-950',    cardBorder: 'border-blue-600',    text: 'text-blue-700',      accent: 'blue'    },
    3:    { solid: 'bg-violet-600 text-white',    subtle: 'bg-violet-50 text-violet-700 ring-violet-200',    cardBg: 'bg-violet-400',  cardText: 'text-violet-950',  cardBorder: 'border-violet-600',  text: 'text-violet-700',    accent: 'violet'  },
    4:    { solid: 'bg-pink-500 text-white',      subtle: 'bg-pink-50 text-pink-700 ring-pink-200',          cardBg: 'bg-pink-300',    cardText: 'text-pink-950',    cardBorder: 'border-pink-500',    text: 'text-pink-700',      accent: 'pink'    },
    5:    { solid: 'bg-orange-500 text-white',    subtle: 'bg-orange-50 text-orange-700 ring-orange-200',    cardBg: 'bg-orange-300',  cardText: 'text-orange-950',  cardBorder: 'border-orange-500',  text: 'text-orange-700',    accent: 'orange'  },
    20:   { solid: 'bg-amber-600 text-white',     subtle: 'bg-amber-50 text-amber-700 ring-amber-200',       cardBg: 'bg-amber-300',   cardText: 'text-amber-950',   cardBorder: 'border-amber-500',   text: 'text-amber-700',     accent: 'amber'   },
    21:   { solid: 'bg-cyan-600 text-white',      subtle: 'bg-cyan-50 text-cyan-700 ring-cyan-200',          cardBg: 'bg-cyan-300',    cardText: 'text-cyan-950',    cardBorder: 'border-cyan-500',    text: 'text-cyan-700',      accent: 'cyan'    },
    22:   { solid: 'bg-indigo-600 text-white',    subtle: 'bg-indigo-50 text-indigo-700 ring-indigo-200',    cardBg: 'bg-indigo-300',  cardText: 'text-indigo-950',  cardBorder: 'border-indigo-500',  text: 'text-indigo-700',    accent: 'indigo'  },
    23:   { solid: 'bg-purple-600 text-white',    subtle: 'bg-purple-50 text-purple-700 ring-purple-200',    cardBg: 'bg-purple-300',  cardText: 'text-purple-950',  cardBorder: 'border-purple-500',  text: 'text-purple-700',    accent: 'purple'  },
    6:    { solid: 'bg-teal-500 text-white',      subtle: 'bg-teal-50 text-teal-700 ring-teal-200',          cardBg: 'bg-teal-300',    cardText: 'text-teal-950',    cardBorder: 'border-teal-500',    text: 'text-teal-700',      accent: 'teal'    },
    25:   { solid: 'bg-yellow-600 text-white',    subtle: 'bg-yellow-50 text-yellow-700 ring-yellow-200',    cardBg: 'bg-yellow-300',  cardText: 'text-yellow-950',  cardBorder: 'border-yellow-500',  text: 'text-yellow-700',    accent: 'yellow'  },
    26:   { solid: 'bg-blue-500 text-white',      subtle: 'bg-blue-50 text-blue-700 ring-blue-200',          cardBg: 'bg-blue-300',    cardText: 'text-blue-950',    cardBorder: 'border-blue-500',    text: 'text-blue-700',      accent: 'blue'    },
    7:    { solid: 'bg-emerald-600 text-white',   subtle: 'bg-emerald-50 text-emerald-700 ring-emerald-200', cardBg: 'bg-emerald-400', cardText: 'text-emerald-950', cardBorder: 'border-emerald-600', text: 'text-emerald-700',   accent: 'emerald' },
    8:    { solid: 'bg-rose-700 text-white',      subtle: 'bg-rose-50 text-rose-700 ring-rose-200',          cardBg: 'bg-rose-300',    cardText: 'text-rose-950',    cardBorder: 'border-rose-500',    text: 'text-rose-700',      accent: 'rose'    },
    9:    { solid: 'bg-red-600 text-white',       subtle: 'bg-red-50 text-red-700 ring-red-200',             cardBg: 'bg-red-300',     cardText: 'text-red-950',     cardBorder: 'border-red-500',     text: 'text-red-700',       accent: 'red'     },
};

/**
 * PrimeVue Tag severity untuk konsistensi dengan Tag-based UI.
 */
export const STATUS_SEVERITY = {
    '-1': 'secondary',
    0: 'warning',
    10: 'info',
    11: 'warning',
    12: 'info',
    1: 'info',
    13: 'contrast',
    2: 'info',
    3: 'contrast',
    4: 'contrast',
    5: 'contrast',
    20: 'warning',
    21: 'info',
    22: 'contrast',
    23: 'contrast',
    6: 'success',
    25: 'warning',
    26: 'info',
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
