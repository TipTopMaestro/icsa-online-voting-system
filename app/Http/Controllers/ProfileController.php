<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
class ProfileController extends Controller
{
        
        public function profile() {
        return Inertia::render('voter/Profile', []);
    }
}