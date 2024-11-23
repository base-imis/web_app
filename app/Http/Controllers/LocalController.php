<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocalController extends Controller
{
    public function setLang($locale){
         App::setLocale($locale);
        Session::put("locale", $locale);


        return redirect()->back();
    }
}
