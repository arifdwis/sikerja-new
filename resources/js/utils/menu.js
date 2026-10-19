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
            title: 'Alur Kerja Sama',
            icon: 'solar:folder-open-bold',
            children: [
                {
                    title: '1. Pengajuan Usulan',
                    route: '/permohonan',
                    icon: 'solar:document-add-linear',
                },
                {
                    title: '2. Verifikasi & Telaah',
                    route: '/validasi',
                    icon: 'solar:checklist-minimalistic-linear',
                },
                {
                    title: '3. Pembahasan Draf KB',
                    route: '/pembahasan?type=kb',
                    icon: 'solar:chat-round-dots-linear',
                },
                {
                    title: '4. Penandatanganan KB',
                    route: '/persetujuan',
                    icon: 'solar:verified-check-linear',
                },
                {
                    title: '5. Pembahasan Draf PKS',
                    route: '/pembahasan?type=pks',
                    icon: 'solar:documents-minimalistic-linear',
                },
                {
                    title: '6. Penandatanganan PKS',
                    route: '/penandatanganan',
                    icon: 'solar:pen-new-square-linear',
                },
                {
                    title: '7. Pelaksanaan Kerja Sama',
                    route: '/pelaksanaan',
                    icon: 'solar:play-circle-linear',
                },
                {
                    title: '8. Evaluasi & Monev',
                    route: '/monev',
                    icon: 'solar:clipboard-check-linear',
                },
                {
                    title: 'Database Rekapitulasi',
                    route: '/permohonan/selesai',
                    icon: 'solar:archive-check-linear',
                },
            ],
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
            title: 'Alur Kerja Sama',
            icon: 'solar:folder-open-bold',
            children: [
                {
                    title: '1. Pengajuan Usulan',
                    route: '/permohonan',
                    icon: 'solar:document-add-linear',
                },
                {
                    title: '2. Verifikasi & Telaah',
                    route: '/validasi',
                    icon: 'solar:checklist-minimalistic-linear',
                },
                {
                    title: '3. Pembahasan Draf KB',
                    route: '/pembahasan?type=kb',
                    icon: 'solar:chat-round-dots-linear',
                },
                {
                    title: '4. Penandatanganan KB',
                    route: '/persetujuan',
                    icon: 'solar:verified-check-linear',
                },
                {
                    title: '5. Pembahasan Draf PKS',
                    route: '/pembahasan?type=pks',
                    icon: 'solar:documents-minimalistic-linear',
                },
                {
                    title: '6. Penandatanganan PKS',
                    route: '/penandatanganan',
                    icon: 'solar:pen-new-square-linear',
                },
                {
                    title: '7. Pelaksanaan Kerja Sama',
                    route: '/pelaksanaan',
                    icon: 'solar:play-circle-linear',
                },
                {
                    title: '8. Evaluasi & Monev',
                    route: '/monev',
                    icon: 'solar:clipboard-check-linear',
                },
                {
                    title: 'Database Rekapitulasi',
                    route: '/permohonan/selesai',
                    icon: 'solar:archive-check-linear',
                },
            ],
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
        case 'kabag_kerjasama':
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
