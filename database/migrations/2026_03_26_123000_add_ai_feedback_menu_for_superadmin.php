<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = Menu::query()->where('title', 'Pengaturan')->first();

        if (!$settings) {
            return;
        }

        Menu::query()->updateOrCreate(
            [
                'parent_id' => $settings->id,
                'title' => 'AI Feedback Log',
            ],
            [
                'route' => 'settings.ai-feedback.index',
                'icon' => 'solar:chat-round-dots-linear',
                'permission_name' => null,
                'is_active' => true,
                'order' => 6,
                'roles' => 'superadmin',
            ]
        );
    }

    public function down(): void
    {
        $settings = Menu::query()->where('title', 'Pengaturan')->first();

        if (!$settings) {
            return;
        }

        Menu::query()
            ->where('parent_id', $settings->id)
            ->where('title', 'AI Feedback Log')
            ->delete();
    }
};
