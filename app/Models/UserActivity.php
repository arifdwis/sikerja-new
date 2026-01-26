<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    protected $table = 'user_activities';

    protected $fillable = [
        'user_id',
        'path',
        'method',
        'ip',
        'input',
        'log_name',
        'description',
        'event',
        'batch_uuid',
        'properties'
    ];

    /**
     * Accessor for Description to fallback to legacy columns.
     */
    public function getDescriptionAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        // Legacy Fallback
        $method = $this->getAttribute('method') ?? '';
        $path = $this->getAttribute('path') ?? '';

        if ($method || $path) {
            return trim("$method $path");
        }

        return 'No description';
    }

    /**
     * Relationship to User (legacy user_id).
     */
    public function causer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Include IP in properties if not present.
     */
    public function getPropertiesAttribute($value)
    {
        // Decode existing properties if any
        $props = json_decode($value ?? '{}', true);

        // Add legacy columns if they exist in attributes but not in props
        if (!isset($props['ip']) && $this->getAttribute('ip')) {
            $props['ip'] = $this->getAttribute('ip');
        }

        if (!isset($props['input']) && $this->getAttribute('input')) {
            $input = $this->getAttribute('input');
            $props['input'] = is_string($input) ? json_decode($input, true) : $input;
        }

        return collect($props);
    }

    // Alias Log Name to something if empty
    public function getLogNameAttribute($value)
    {
        return $value ?: 'system';
    }
}
