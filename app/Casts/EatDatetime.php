<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class EatDatetime implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }

        // DB gives us UTC — convert to EAT on the way out
        return Carbon::parse($value, 'UTC')->timezone('Africa/Kampala');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        // User gives us EAT — convert to UTC on the way in
        return Carbon::parse($value, 'Africa/Kampala')->utc()->toDateTimeString();
    }
}
