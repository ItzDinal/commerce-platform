<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user();

        return view('admin.dashboard', [
            'admin' => $admin,
        ]);
    }
}