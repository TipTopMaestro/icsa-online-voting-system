<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
class CandidatesController extends Controller
{
        
        public function candidates() {
        return Inertia::render('admin/candidates', []);
    }
}
