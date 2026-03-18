<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface GuestRepositoryInterfaces
{
    public function index();

    public function store(Request $request);

    public function thanks();
}