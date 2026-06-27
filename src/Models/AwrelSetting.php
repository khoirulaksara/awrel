<?php

namespace Khoirulaksara\Awrel\Models;

use Illuminate\Database\Eloquent\Model;

class AwrelSetting extends Model
{
    protected $table = 'awrel_settings';

    protected $fillable = ['settings'];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    /**
     * Get the singleton settings record, creating it with defaults if needed.
     */
    public static function record(): self
    {
        return static::first() ?? static::create([
            'settings' => config('awrel'),
        ]);
    }
}
