<?php

namespace App\Eloquent;

use App\Models\Car;
use App\Models\Color;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ColorEloquent
{
    /**
     * @return Collection|Color[]
     */
    public static function list(): Collection
    {
        return Color::query()->where('active', true)->orderBy('priority', 'asc')->get();
    }
}
