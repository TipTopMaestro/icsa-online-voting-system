<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
class ElectionController extends Controller
{
    public function index() {
        return Inertia::render('election/index', []);
    }
}
