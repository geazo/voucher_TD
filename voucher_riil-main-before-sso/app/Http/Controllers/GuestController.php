<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Outlet;
use App\Models\Penerima;
use App\Repositories\Interfaces\GuestRepositoryInterfaces;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function __construct(protected GuestRepositoryInterfaces $guestRepository) {}

    public function index()
    {
        return $this->guestRepository->index();
    }

    public function store(Request $request)
    {
        return $this->guestRepository->store($request);
    }

    public function thanks()
    {
        return $this->guestRepository->thanks();
    }
}
