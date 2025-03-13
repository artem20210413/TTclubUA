<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class ImportController extends Controller
{

    public function importMain(Request $request)
    {
        set_time_limit(300); // Увеличивает лимит до 5 минут
//        Excel::import(new UsersImport, $request->file('file'));
        $import = new UsersImport();
        Excel::import($import, request()->file('file'));

        $processedData = $import->getProcessedData();

        foreach ($processedData as $data) {
            $import->createUserAndCar($data);
        }

        return success('Пользователи успешно импортированы!');
    }


}
