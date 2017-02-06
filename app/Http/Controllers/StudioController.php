<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Notifier;

use Illuminate\Http\Request;

use Dingo\Api\Exceptions\ResourceException;

use App\Http\Controllers\Controller;
use App\Http\Requests\Studios\CreateRequest;
use App\Http\Requests\Studios\UpdateRequest;
use App\Http\Requests\Studios\UpdateAddressRequest;
use App\Http\Requests\Studios\DisableRequest;
use App\Http\Requests\Studios\RestoreRequest;

use App\Events\StudioWasCreated;

use App\Mailers\StudioMailer;

use App\Locations\LocationRepository;
use App\Tags\TagRepository;

use App\Studios\StudioRepository;
use App\Studios\StudioTransformer;

use App\Users\UserRepository;
use App\Tags\TagTransformer;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StudioController extends Controller
{
    protected $studios;

    protected $users;

    protected $locations;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct (StudioRepository $studios, UserRepository $users, LocationRepository $locations)
    {
        $this->studios = $studios;
        $this->users = $users;
        $this->locations = $locations;
    }

    public function search (Request $request)
    {
        $include = $request->input('include');
        $filter = $request->input('filter');
        
        // we can request no paging by setting this false
        if ($request->input('paging') && 
            !filter_var($request->input('paging'), FILTER_VALIDATE_BOOLEAN)) {
            
            $studios = $this->studios->search($include, $filter);

            return $this->response->collection($studios, new StudioTransformer);
        }

        // otherwise page it
        $studios = $this->studios->searchAndPaginate($include, $filter);
        
        return $this->response->paginator($studios, new StudioTransformer);
    }

    public function show (Request $request, $slug)
    {
        $city = $request->get('location');

        if (is_numeric($slug)) { 
            $studio = $this->studios->find ($slug);
        } else {
            $studio = $this->studios->findBySlug ($slug, $city);
        }

        if (!$studio) {
            throw new NotFoundHttpException('Can\'t seem to find what you\'re looking for.  We\'ve been alerted, please try again later.');
        }
        
        return $this->response->item($studio, $studio->getTransformer());
    }

    public function locations (Request $request, TagRepository $tags)
    {
        $tags = $tags->listCities();
        
        return $this->response->collection($tags, new TagTransformer);
    }

    /*  This is more like, a studio application.
     *  Just send an e-mail to the admin.
     **/
    public function register (Request $request, StudioMailer $mailer)
    {
        $params = $request->only([
            'market',
            'studio_name',
            'first_name',
            'last_name',
            'title',
            'email',
            'phone',
            'website',
            'comments'
        ]);

        $mailer->submitApplicationRequest ($params);

        $msg = $params['studio_name'] . ' in ' . $params['market'] 
            . ' has applied to the program.';

        Notifier::notify($msg)->via('log')->via('slack');

        return $this->response->noContent();
    }

    public function store (CreateRequest $request)
    {
        // create a new user
        $user = $this->users
            ->create($request->only([
                'first_name', 'last_name', 'email', 'phone',
                // password will be set during activation
            ]));

        // create a new studio // 'partner'
        $studio = $this->studios
            ->create([
                'area_id'   =>  $request->get('area_id'),
                'name'      =>  $request->get('partner_name'),
                'email'     =>  $request->get('partner_email'),
                'phone'     =>  $request->get('partner_phone'),
                'website'   =>  $request->get('website'),
                'description' => $request->get('description'),
                'has_classes' => $request->get('studio', false),
                'is_retailer' => $request->get('retailer', false),
            ]);

        if (!$request->get('promo')) {
            // create a new studio location
            $location = $this->locations
                ->createNewLocation($studio, [
                    'type'      =>  'studio',
                    'address1'  =>  $request->get('address1'),
                    'address2'  =>  $request->get('address2'),
                    'postal'    =>  $request->get('postal'),
                    'city'      =>  $request->get('city'),
                    'province'  =>  $request->get('province'),
                    'country'   =>  $request->get('country'),
                    'lat'       =>  $request->get('lat'),
                    'lng'       =>  $request->get('lng'),
                ]);
        }

        // get the tag id's
        $types = collect($request->get('types'));

        DB::beginTransaction();

        try {

            // save the user
            $this->users->save ($user);
            
            // add the user to the studio owners tag
            $this->users->addStudioTag ($user);
            
            // save the studio with the user as the owner
            $this->users->ownStudio ($user, $studio);
            
            // save the location as the studios primary
            if ($location) {
                $this->studios->useLocation ($studio, $location);
            }

            // attach any type tags
            $types->each(function ($type) use ($studio)
            {
                $this->studios->addTypeById ($studio, $type);
            });

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();
        
        // emit an event
        event(new StudioWasCreated($studio));

        return $this->response->item($studio, $studio->getTransformer());
    }

    public function update (UpdateRequest $request, $sid = null)
    {
        $params = $request->all();

        if ($this->auth->user()->isAdmin() && $sid !== null) {
            
            $studio = $this->studios->find($sid);
        
        } else if ($this->auth->user()->isAdmin()) {
        
            throw new ResourceException ('You must supply a studio id');
        
        } else {
        
            $studio = $this->auth->user()->studio;

        }

        $studio = $this->studios->update($studio, $params);

        return $this->response->item($studio, $studio->getTransformer());
    }

    public function address (UpdateAddressRequest $request, $sid = null)
    {
        $params = $request->all();
        
        if ($this->auth->user()->isAdmin() && $sid !== null) {
            
            $studio = $this->studios->find($sid);
        
        } else if ($this->auth->user()->isAdmin()) {
        
            throw new ResourceException ('You must supply a studio id');
        
        } else {
        
            $studio = $this->auth->user()->studio;

        }

        $location = $this->locations->createNewLocation($studio, $params);

        $this->studios->useLocation($studio, $location);

        return $this->response->item($location, $location->getTransformer());
    }

    public function destroy (DisableRequest $request, $id)
    {
        $studio = $this->studios->find($id);

        $this->studios->disable($studio);

        return $this->response->noContent();
    }

    public function restore (RestoreRequest $request, $id)
    {
        $studio = $this->studios->find($id);

        $this->studios->restore($studio);

        return $this->response->noContent();
    }

    public function addType (Request $request, $sid, $tid, TagRepository $tags)
    {
        $studio = $this->studios->find($sid);

        $tag = $tags->find ($tid);

        $this->studios->addType ($studio, $tag);

        return $this->response->item($tag, $tag->getTransformer());
    }

    public function removeType (Request $request, $sid, $tid, TagRepository $tags)
    {
        $studio = $this->studios->find($sid);

        $tag = $tags->find ($tid);

        $this->studios->removeType ($studio, $tag);

        return $this->response->noContent();
    }
}
