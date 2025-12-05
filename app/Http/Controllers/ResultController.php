<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
class ResultController extends Controller
{
        
    public function result() {
        return Inertia::render('admin/result', []);
    }

    public function voterResult() {
        return Inertia::render('voter/result', []);
    }
}
