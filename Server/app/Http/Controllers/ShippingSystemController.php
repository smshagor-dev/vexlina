<?php

namespace App\Http\Controllers;

use App\Models\ShippingSystem;
use Illuminate\Http\Request;

class ShippingSystemController extends Controller
{
    public function list()
    {
        $shipping_systems = ShippingSystem::all();
        return view('backend.shipping_system.index', compact('shipping_systems'));
    }
}
