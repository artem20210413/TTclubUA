<?php

namespace App\Eloquent;

use App\Models\Finance;
use App\Models\Mention;
use App\Models\MonoTransaction;
use App\Models\User;
use App\Services\Image\ImageWebpService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;

class FinanceEloquent
{
    public static function createByMono(MonoTransaction $monoTransaction): Finance
    {
        $f = new Finance();
        $f->user_id = $monoTransaction->user_id;
        $f->amount = $monoTransaction->amount / 100;
        $f->description = 'Came from mono';
        $f->save();

        return $f;
    }


}
