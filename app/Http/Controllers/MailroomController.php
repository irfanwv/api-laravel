<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Mailrooms\MailroomRepository;
use App\Mailrooms\MailroomTransformer;
use App\Orders\OrderRepository;
use App\Orders\OrderTransformer;

class MailroomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index (Request $request, OrderRepository $orders)
    {
        $pending = $orders->getPendingShipments();

        return $this->response->collection ($pending, new OrderTransformer);
    }

}
