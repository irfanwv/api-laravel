<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\Prices\GetPricesRequest;
use App\Http\Requests\Prices\UpdatePricesRequest;

use App\Http\Controllers\Controller;
use App\Prices\PriceRepository;
use App\Prices\PriceTransformer;

class PriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index (Request $request, PriceRepository $prices)
    {
        $list = $prices->search();

        return $this->response->collection($list, new PriceTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store (Request $request, PriceRepository $prices)
    {
        $price = $prices->create($request->input());

        $prices->save($price);

        return $this->response->item($price, new PriceTransformer);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show ($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $cid, a city/tag id
     * @return Response
     */
    public function update (UpdatePricesRequest $request, $cid, PriceRepository $prices)
    {
        $price = $prices->update($cid, $request->all());

        return $this->response->item($price, new PriceTransformer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy ($id)
    {
        //
    }
}
