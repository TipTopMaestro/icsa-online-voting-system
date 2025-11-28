<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
class VotersController extends Controller
{
        
    public function voters() {
        return Inertia::render('admin/voters', []);
    }
}
