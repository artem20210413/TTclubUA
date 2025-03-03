<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class ImportController extends Controller
{

    public function importMain(Request $request)
    {
        Excel::import(new UsersImport, $request->file('file'));

        return success('Пользователи успешно импортированы!');
    }


}
