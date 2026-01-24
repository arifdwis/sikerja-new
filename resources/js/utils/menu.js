// Menu configuration for SiKerja V2
// Based on role: administrator, superadmin, tkksd, pemohon

export const menuItems = {
    // Menu untuk semua user yang login
    default: [
        {
            title: 'Dashboard',
            route: '/dashboard',
            icon: 'solar:home-2-bold',
        },
    ],

    // Menu untuk Pemohon
    pemohon: [
        {
            title: 'Dashboard',
            route: '/dashboard',
            icon: 'solar:home-2-bold',
        },
        {
            title: 'Permohonan Saya',
            route: '/permohonan',
            icon: 'solar:document-text-bold',
        },
        {
            title: 'Riwayat',
            route: '/riwayat',
            icon: 'solar:history-bold',
        },
    ],

    // Menu untuk TKKSD (Tim Koordinasi Kerjasama Daerah)
    tkksd: [
        {
            title: 'Dashboard',
            route: '/dashboard',
            icon: 'solar:home-2-bold',
        },
        {
            title: 'Kelola Permohonan',
            icon: 'solar:folder-open-bold',
            children: [
                {
                    title: 'Daftar Permohonan',
                    route: '/permohonan',
                    icon: 'solar:document-text-linear',
                },
                {
                    title: 'Validasi',
                    route: '/validasi',
                    icon: 'solar:check-circle-linear',
                },
                {
                    title: 'Pembahasan',
                    route: '/pembahasan',
                    icon: 'solar:chat-round-dots-linear',
                },
                {
                    title: 'Persetujuan',
                    route: '/persetujuan',
                    icon: 'solar:verified-check-linear',
                },
            ],
        },
        {
            title: 'Penjadwalan',
            route: '/penjadwalan',
            icon: 'solar:calendar-bold',
        },
        {
            title: 'Laporan',
            route: '/laporan',
            icon: 'solar:chart-bold',
        },
    ],

    // Menu untuk Administrator
    administrator: [
        {
            title: 'Dashboard',
            route: '/dashboard',
            icon: 'solar:home-2-bold',
        },
        {
            title: 'Kelola Permohonan',
            icon: 'solar:folder-open-bold',
            children: [
                {
                    title: 'Daftar Permohonan',
                    route: '/permohonan',
                    icon: 'solar:document-text-linear',
                },
                {
                    title: 'Validasi',
                    route: '/validasi',
                    icon: 'solar:check-circle-linear',
                },
                {
                    title: 'Pembahasan',
                    route: '/pembahasan',
                    icon: 'solar:chat-round-dots-linear',
                },
                {
                    title: 'Persetujuan',
                    route: '/persetujuan',
                    icon: 'solar:verified-check-linear',
                },
            ],
        },
        {
            title: 'Penjadwalan',
            route: '/penjadwalan',
            icon: 'solar:calendar-bold',
        },
        {
            title: 'Laporan',
            route: '/laporan',
            icon: 'solar:chart-bold',
        },
        {
            title: 'Master Data',
            icon: 'solar:database-bold',
            children: [
                {
                    title: 'Kategori',
                    route: '/master/kategori',
                    icon: 'solar:tag-linear',
                },
                {
                    title: 'Pemohon',
                    route: '/master/pemohon',
                    icon: 'solar:users-group-rounded-linear',
                },
            ],
        },
        {
            title: 'Pengaturan',
            icon: 'solar:settings-bold',
            children: [
                {
                    title: 'Users',
                    route: '/settings/users',
                    icon: 'solar:user-linear',
                },
                {
                    title: 'Roles',
                    route: '/settings/roles',
                    icon: 'solar:shield-user-linear',
                },
                {
                    title: 'Permissions',
                    route: '/settings/permissions',
                    icon: 'solar:lock-keyhole-linear',
                },
                {
                    title: 'Menu',
                    route: '/settings/menu',
                    icon: 'solar:hamburger-menu-linear',
                },
                {
                    title: 'Log Activity',
                    route: '/settings/log-activity',
                    icon: 'solar:clipboard-list-linear',
                },
            ],
        },
    ],
};

// Get menu based on user role
export function getMenuByRole(role) {
    if (!role) return menuItems.default;

    switch (role) {
        case 'administrator':
        case 'superadmin':
            return menuItems.administrator;
        case 'tkksd':
            return menuItems.tkksd;
        case 'pemohon':
            return menuItems.pemohon;
        default:
            return menuItems.default;
    }
}

export default menuItems;
