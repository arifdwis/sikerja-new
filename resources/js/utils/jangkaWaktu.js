/**
 * Hitung jangka waktu otomatis dari tanggal_mulai → tanggal_berakhir.
 * Output dalam format: "X Tahun, Y Bulan, Z Hari" (komponen 0 di-skip).
 *
 * @param {string|Date} mulai
 * @param {string|Date} akhir
 * @returns {string}
 */
export function computeJangkaWaktu(mulai, akhir) {
    if (!mulai || !akhir) return '';
    const start = new Date(mulai);
    const end = new Date(akhir);
    if (isNaN(start.getTime()) || isNaN(end.getTime())) return '';
    if (end < start) return '';

    let years = end.getFullYear() - start.getFullYear();
    let months = end.getMonth() - start.getMonth();
    let days = end.getDate() - start.getDate();

    if (days < 0) {
        months -= 1;
        const prevMonth = new Date(end.getFullYear(), end.getMonth(), 0);
        days += prevMonth.getDate();
    }
    if (months < 0) {
        years -= 1;
        months += 12;
    }

    const parts = [];
    if (years > 0) parts.push(`${years} Tahun`);
    if (months > 0) parts.push(`${months} Bulan`);
    if (days > 0) parts.push(`${days} Hari`);

    return parts.length > 0 ? parts.join(', ') : '0 Hari';
}
