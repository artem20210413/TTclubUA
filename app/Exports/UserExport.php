<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class UserExport implements FromView, WithColumnFormatting
{
    /**
     * @var Collection|User[] $users
     */
    protected Collection $users;

    public function __construct(Collection $users)
    {
        $this->users = $users;

    }

    public function view(): View
    {
        return view('exports.users_export', [
            'users' => $this->users,
//            'coefficients' => $this->coefficients
        ]);
    }
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT, // колонка "Телефон" — TEXT
        ];
    }
}
