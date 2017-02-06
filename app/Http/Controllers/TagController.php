<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Http\Requests;
use App\Http\Requests\Tags\CreateTagRequest;
use App\Http\Requests\Tags\DeleteTagRequest;

use App\Tags\TagRepository;
use App\Tags\TagTransformer;

class TagController extends Controller
{
    protected $tags;

    public function __construct (TagRepository $tags)
    {
        $this->tags = $tags;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index (Request $request)
    {
        $include = $request->get('include');
        $filter = $request->get('filter');

        $tags = $this->tags->search($include, $filter);

        return $this->response->collection($tags, new TagTransformer);
    }

    /**
     * Do a custom search for unauthorized users
     * Returns a list of all available yoga types.
     *
     * @return Response
     */
    public function yogaTypes (Request $request)
    {
        $include = '';
        $filter = 'yogaTypes';

        $tags = $this->tags->search($include, $filter);

        return $this->response->collection($tags, new TagTransformer);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param  Request  $request
     * @return Response
     */
    public function store (CreateTagRequest $request)
    {
        $input = $request->all();

        if ($this->tags->findByName($input['name'])) {
            return $this->response->error('That tag already exists.', 422);
        }

        $tag = $this->tags->create($input);

        $this->tags->save($tag);

        return $this->response->item($tag, new TagTransformer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy (DeleteTagRequest $request, $id)
    {
        $tag = $this->tags->find($id);

        $this->tags->delete($tag);

        return $this->response->noContent();
    }
}
