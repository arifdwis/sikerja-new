<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenusSeeder extends Seeder
{
    public function run()
    {
        // Truncate tables first (optional, be careful)
        // Menu::truncate(); 

        $menus = [
            [
                'title' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'solar:home-2-bold',
                'order' => 1,
                'roles' => 'administrator,superadmin,tkksd,pemohon',
                'children' => []
            ],
            [
                'title' => 'Permohonan Saya',
                'route' => 'permohonan.index', // Adjust if route different
                'icon' => 'solar:document-text-bold',
                'order' => 2,
                'roles' => 'pemohon',
                'children' => []
            ],
            [
                'title' => 'Riwayat',
                'route' => 'riwayat.index', // Adjust if route different
                'icon' => 'solar:history-bold',
                'order' => 3,
                'roles' => 'pemohon',
                'children' => []
            ],
            [
                'title' => 'Monev',
                'route' => 'monev.index',
                'icon' => 'solar:clipboard-check-bold',
                'order' => 4,
                'roles' => 'pemohon,administrator,superadmin',
                'children' => []
            ],
            [
                'title' => 'Kelola Permohonan',
                'route' => null,
                'icon' => 'solar:folder-open-bold',
                'order' => 4,
                'roles' => 'administrator,superadmin,tkksd',
                'children' => [
                    [
                        'title' => 'Daftar Permohonan',
                        'route' => 'permohonan.index',
                        'icon' => 'solar:document-text-linear',
                        'roles' => 'administrator,superadmin,tkksd',
                        'order' => 1
                    ],
                    [
                        'title' => 'Validasi',
                        'route' => 'validasi.index',
                        'icon' => 'solar:check-circle-linear',
                        'roles' => 'administrator,superadmin,tkksd',
                        'order' => 2
                    ],
                    [
                        'title' => 'Pembahasan',
                        'route' => 'pembahasan.index',
                        'icon' => 'solar:chat-round-dots-linear',
                        'roles' => 'administrator,superadmin,tkksd',
                        'order' => 3
                    ],
                    [
                        'title' => 'Persetujuan',
                        'route' => 'persetujuan.index',
                        'icon' => 'solar:verified-check-linear',
                        'roles' => 'administrator,superadmin,tkksd',
                        'order' => 4
                    ],
                ]
            ],
            [
                'title' => 'Penjadwalan',
                'route' => 'penjadwalan.index',
                'icon' => 'solar:calendar-bold',
                'order' => 5,
                'roles' => 'administrator,superadmin,tkksd',
                'children' => []
            ],
            [
                'title' => 'Laporan',
                'route' => 'laporan.index',
                'icon' => 'solar:chart-bold',
                'order' => 6,
                'roles' => 'administrator,superadmin,tkksd',
                'children' => [
                    [
                        'title' => 'Monitoring Kerjasama',
                        'route' => 'laporan.index',
                        'icon' => 'solar:monitor-camera-linear',
                        'roles' => 'administrator,superadmin,tkksd',
                        'order' => 1
                    ],
                    [
                        'title' => 'Akumulatif Kerjasama',
                        'route' => 'laporan.akumulatif',
                        'icon' => 'solar:chart-square-linear',
                        'roles' => 'administrator,superadmin,tkksd',
                        'order' => 2
                    ],
                    [
                        'title' => 'Rekapitulasi Mitra',
                        'route' => 'laporan.rekap-mitra',
                        'icon' => 'solar:users-group-two-rounded-linear',
                        'roles' => 'administrator,superadmin,tkksd',
                        'order' => 3
                    ],
                    [
                        'title' => 'Persentase Perangkat Daerah',
                        'route' => 'laporan.persentase-opd',
                        'icon' => 'solar:pie-chart-2-linear',
                        'roles' => 'administrator,superadmin,tkksd',
                        'order' => 4
                    ],
                    [
                        'title' => 'Persentase Bidang Kerjasama',
                        'route' => 'laporan.persentase-bidang',
                        'icon' => 'solar:graph-new-linear',
                        'roles' => 'administrator,superadmin,tkksd',
                        'order' => 5
                    ],
                ]
            ],
            [
                'title' => 'Master Data',
                'route' => null,
                'icon' => 'solar:database-bold',
                'order' => 7,
                'roles' => 'administrator,superadmin',
                'children' => [
                    [
                        'title' => 'Kategori',
                        'route' => 'master.kategori.index',
                        'icon' => 'solar:tag-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 1
                    ],
                    [
                        'title' => 'Pemohon',
                        'route' => 'master.pemohon.index',
                        'icon' => 'solar:users-group-rounded-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 2
                    ],
                ]
            ],
            [
                'title' => 'Pengaturan',
                'route' => null,
                'icon' => 'solar:settings-bold',
                'order' => 8,
                'roles' => 'administrator,superadmin',
                'children' => [
                    [
                        'title' => 'Users',
                        'route' => 'settings.users.index',
                        'icon' => 'solar:user-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 1
                    ],
                    [
                        'title' => 'Roles',
                        'route' => 'settings.roles.index',
                        'icon' => 'solar:shield-user-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 2
                    ],
                    [
                        'title' => 'Permissions',
                        'route' => 'settings.permissions.index',
                        'icon' => 'solar:lock-keyhole-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 3
                    ],
                    [
                        'title' => 'Menu',
                        'route' => 'settings.menu.index',
                        'icon' => 'solar:hamburger-menu-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 4
                    ],
                    [
                        'title' => 'Log Activity',
                        'route' => 'settings.log-activity.index',
                        'icon' => 'solar:clipboard-list-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 5
                    ],
                ]
            ],
        ];

        foreach ($menus as $menuData) {
            $children = $menuData['children'];
            unset($menuData['children']);

            // Use updateOrCreate to avoid duplicates
            // Identify by title + route? or just title? Route might be null.
            // Let's use title as unique identifier for top level
            $parent = Menu::updateOrCreate(
                ['title' => $menuData['title']],
                $menuData
            );

            if (!empty($children)) {
                foreach ($children as $childData) {
                    $childData['parent_id'] = $parent->id;
                    Menu::updateOrCreate(
                        ['title' => $childData['title'], 'parent_id' => $parent->id],
                        $childData
                    );
                }
            }
        }
    }
}
