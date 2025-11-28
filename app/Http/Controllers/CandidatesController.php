<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CandidatesController extends Controller 
{
        
    public function index() {
        return Inertia::render('admin/candidates', []);
    }
}
