<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenusSeeder extends Seeder
{
    public function run()
    {
        $menus = [
            [
                'title' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'solar:home-2-bold',
                'order' => 1,
                'roles' => 'administrator,superadmin,kabag_kerjasama,tkksd,pemohon',
                'children' => []
            ],
            [
                'title' => 'Permohonan Saya',
                'route' => 'permohonan.index',
                'icon' => 'solar:document-text-bold',
                'order' => 2,
                'roles' => 'pemohon',
                'children' => []
            ],
            [
                'title' => 'Riwayat',
                'route' => 'riwayat.index',
                'icon' => 'solar:history-bold',
                'order' => 3,
                'roles' => 'pemohon',
                'children' => []
            ],

            [
                'title' => 'Kelola Permohonan',
                'route' => null,
                'icon' => 'solar:folder-open-bold',
                'order' => 4,
                'roles' => 'administrator,superadmin,kabag_kerjasama,tkksd',
                'children' => [
                    [
                        'title' => 'Daftar Permohonan',
                        'route' => 'permohonan.index',
                        'icon' => 'solar:document-text-linear',
                        'roles' => 'administrator,superadmin,kabag_kerjasama,tkksd',
                        'order' => 1
                    ],
                    [
                        'title' => 'Validasi & Disposisi',
                        'route' => 'validasi.index',
                        'icon' => 'solar:check-circle-linear',
                        'roles' => 'administrator,superadmin,kabag_kerjasama,tkksd',
                        'order' => 2
                    ],
                    [
                        'title' => 'Pembahasan',
                        'route' => 'pembahasan.index',
                        'icon' => 'solar:chat-round-dots-linear',
                        'roles' => 'administrator,superadmin,kabag_kerjasama,tkksd',
                        'order' => 3
                    ],
                    [
                        'title' => 'Persetujuan Jadwal',
                        'route' => 'persetujuan.index',
                        'icon' => 'solar:verified-check-linear',
                        'roles' => 'administrator,superadmin,kabag_kerjasama,tkksd',
                        'order' => 4
                    ],
                    [
                        'title' => 'Penandatanganan',
                        'route' => 'penandatanganan.index',
                        'icon' => 'solar:pen-new-square-linear',
                        'roles' => 'administrator,superadmin,kabag_kerjasama',
                        'order' => 5
                    ],
                    [
                        'title' => 'Pelaksanaan',
                        'route' => 'pelaksanaan.index',
                        'icon' => 'solar:rocket-linear',
                        'roles' => 'administrator,superadmin,kabag_kerjasama',
                        'order' => 6
                    ],
                ]
            ],
            [
                'title' => 'Kerjasama Manual',
                'route' => 'kerjasama-manual.index',
                'icon' => 'solar:archive-minimalistic-bold',
                'order' => 5,
                'roles' => 'administrator,superadmin,kabag_kerjasama',
                'children' => []
            ],
            [
                'title' => 'Laporan & Monev',
                'route' => 'laporan.index',
                'icon' => 'solar:chart-bold',
                'order' => 6,
                'roles' => 'administrator,superadmin,kabag_kerjasama,tkksd',
                'children' => [
                    [
                        'title' => 'Monitoring Kerjasama',
                        'route' => 'laporan.index',
                        'icon' => 'solar:monitor-camera-linear',
                        'roles' => 'administrator,superadmin,kabag_kerjasama,tkksd',
                        'order' => 1
                    ],
                    [
                        'title' => 'Statistik Laporan',
                        'route' => 'laporan.statistik',
                        'icon' => 'solar:pie-chart-2-linear',
                        'roles' => 'administrator,superadmin,kabag_kerjasama,tkksd',
                        'order' => 2
                    ],
                    [
                        'title' => 'Monev Kerjasama',
                        'route' => 'monev.index',
                        'icon' => 'solar:clipboard-check-linear',
                        'roles' => 'administrator,superadmin,kabag_kerjasama,tkksd',
                        'order' => 3
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
                        'title' => 'Kategori Kerjasama',
                        'route' => 'master.kategori.index',
                        'icon' => 'solar:tag-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 1
                    ],
                    [
                        'title' => 'Master OPD',
                        'route' => 'master.opd.index',
                        'icon' => 'solar:buildings-2-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 2
                    ],
                    [
                        'title' => 'Data Pemohon',
                        'route' => 'master.pemohon.index',
                        'icon' => 'solar:users-group-rounded-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 3
                    ],
                    [
                        'title' => 'Mitra Kerja Sama',
                        'route' => 'master.mitra.index',
                        'icon' => 'solar:hand-shake-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 4
                    ],
                    [
                        'title' => 'Galeri TKKSD',
                        'route' => 'master.galeri-tkksd.index',
                        'icon' => 'solar:gallery-wide-linear',
                        'roles' => 'administrator,superadmin',
                        'order' => 5
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
                    [
                        'title' => 'AI Feedback Log',
                        'route' => 'settings.ai-feedback.index',
                        'icon' => 'solar:chat-round-dots-linear',
                        'roles' => 'superadmin',
                        'order' => 6
                    ],
                ]
            ],
        ];

        foreach ($menus as $menuData) {
            $children = $menuData['children'];
            unset($menuData['children']);

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
