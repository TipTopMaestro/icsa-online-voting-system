<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
class AnnouncementController extends Controller
{
        
        public function index() {
        return Inertia::render('Announcement/index', []);
    }
}