<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Http\Requests;
use App\Http\Requests\Tags\CreateCityTagRequest;
use App\Http\Requests\Tags\DisableRequest;

use App\Tags\TagRepository;
use App\Tags\TagTransformer;

class CityController extends Controller
{
    /**
     * Display a grouped listing of the areas and their cities
     *
     * @param  Request  $request
     * @param  TagRepository  $tags
     * @return Response
     */
    public function index (Request $request, TagRepository $tags)
    {
        $tags = $tags->search('parent.price', 'isChild,locations')
            ->groupBy('parent.name')
            ->map(function ($group, $key)
            {
                return collect($group)
                    ->map(function ($tag)
                    {
                        return $tag->getTransformer()->transform($tag);
                    })
                    ->toArray();
            })
            ->toArray();

        // manual transformations to accomodate the group by
        return $this->response->array(['data' => $tags]);
    }
    
    /**
     * Store a newly created resource in storage.
     *  Currently, we are only creating location tags.
     * 
     * @param  Request  $request
     * @return Response
     */
    public function store (CreateCityTagRequest $request, TagRepository $tags)
    {
        $input = array_merge($request->input(), ['type' => 'App\Studios\Studio']);
        
        $tag = $tags->create($input);

        $tags->save($tag);

        if ($tag->isParent()) {
            
            $input['area_id'] = $tag->id;

            $prices = $this->api
                ->be(auth()->user())
                ->with($input)
                ->post('prices');
        }

        return $this->response->item($tag, $tag->getTransformer());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update (Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy (DisableRequest $request, $id, TagRepository $tags)
    {
        $city = $tags->find($id);

        $tags->disableCity($city);

        return $this->response->noContent();
    }
    
    
    public function test()
    {
        echo 'Welcome To laravel';
    }
}
