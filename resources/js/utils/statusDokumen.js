export const VALID_STATUS = {
  SESUAI: 1,
  PERBAIKI: 9,
  PROSES: 0,
};

export const getStatusLabel = (status) => {
  if (status === VALID_STATUS.SESUAI) return 'Dokumen Sesuai';
  if (status === VALID_STATUS.PERBAIKI) return 'Perlu Perbaikan';
  return 'Proses Validasi';
};

// Warna disesuaikan agar tidak terlalu keras dan tetap elegan di mode gelap
export const getStatusClass = (status) => {
  if (status === VALID_STATUS.SESUAI)
    return 'bg-emerald-100 text-emerald-700 border border-emerald-300 dark:bg-emerald-900/30 dark:text-emerald-600 dark:border-emerald-800';
  if (status === VALID_STATUS.PERBAIKI)
    return 'bg-amber-100 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800';
  return 'bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-800/50 dark:text-gray-300 dark:border-gray-700';
};

export const getStatusIcon = (status) => {
  if (status === VALID_STATUS.SESUAI) return 'solar:check-circle-bold';
  if (status === VALID_STATUS.PERBAIKI) return 'solar:refresh-circle-bold';
  return 'solar:clock-circle-bold';
};
