const parseDate = (value) => {
    if (!value) return null;

    const [year, month, day] = value.split('-').map(Number);

    if (!year || !month || !day) return null;

    return new Date(Date.UTC(year, month - 1, day));
};

const shiftUtcDate = (date, amount, unit) => {
    const shifted = new Date(date);

    if (unit === 'year') shifted.setUTCFullYear(shifted.getUTCFullYear() + amount);
    if (unit === 'month') shifted.setUTCMonth(shifted.getUTCMonth() + amount);
    if (unit === 'day') shifted.setUTCDate(shifted.getUTCDate() + amount);

    return shifted;
};

export const calculateJangkaWaktu = (startValue, endValue) => {
    const start = parseDate(startValue);
    const end = parseDate(endValue);

    if (!start || !end || end < start) return '';

    // Periode PKS menghitung tanggal mulai dan tanggal akhir.
    const inclusiveEnd = shiftUtcDate(end, 1, 'day');
    let cursor = new Date(start);
    let years = 0;
    let months = 0;

    while (shiftUtcDate(cursor, 1, 'year') <= inclusiveEnd) {
        cursor = shiftUtcDate(cursor, 1, 'year');
        years += 1;
    }

    while (shiftUtcDate(cursor, 1, 'month') <= inclusiveEnd) {
        cursor = shiftUtcDate(cursor, 1, 'month');
        months += 1;
    }

    const days = Math.round((inclusiveEnd - cursor) / 86400000);
    const parts = [
        years ? `${years} Tahun` : null,
        months ? `${months} Bulan` : null,
        days ? `${days} Hari` : null,
    ].filter(Boolean);

    return parts.join(' ') || '1 Hari';
};
