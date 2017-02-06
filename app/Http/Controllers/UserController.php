<?php

namespace App\Http\Controllers;

use Auth;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateRequest;

use App\Users\UserRepository;

use App\Http\Requests\ManagementRequest;

class UserController extends Controller
{
    protected $users;

    public function __construct (UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * Full text search for accounts
     *
     * @return Response
     */
    public function index (Request $request)
    {
        $sql = "SELECT DISTINCT id, first_name, last_name, phone, email, studio_name, studio_email, studio_phone, studio_retail, studio_studio

        FROM 
            (SELECT users.id as id,
                users.first_name as first_name,
                users.last_name as last_name,
                users.phone as phone,
                users.email as email,
                studios.name as studio_name,
                studios.email as studio_email,
                studios.phone as studio_phone,
                studios.is_retailer as studio_retail,
                studios.has_classes as studio_studio,
                count(passports.number) as passport_count,
                to_tsvector(
                    coalesce(users.first_name, ' ') || ' ' || 
                    coalesce(users.last_name, ' ') || ' ' || 
                    coalesce(users.email, ' ') || ' ' ||
                    coalesce(users.phone, ' ') || ' ' ||
                    coalesce(regexp_replace(users.email, '@|\.|-', ' ', 'g'), ' ') || ' ' || 
                    coalesce(regexp_replace(users.phone, '@|\.|-', ' ', 'g'), ' ') || ' ' || 
                    coalesce(studios.name, ' ') || ' ' || 
                    coalesce(studios.email, ' ') || ' ' ||
                    coalesce(studios.phone, ' ') || ' ' ||
                    coalesce(studios.is_retailer, false) || ' ' ||
                    coalesce(studios.has_classes, false) || ' ' ||
                    coalesce(regexp_replace(studios.email, '@|\.|-', ' ', 'g'), ' ') || ' ' || 
                    coalesce(regexp_replace(studios.phone, '@|\.|-', ' ', 'g'), ' ') || ' ' || 
                    coalesce(passports.number, '0') || ' ' ||
                    coalesce(orders.charge_id, '0')
                ) AS document
            FROM users
            LEFT JOIN studios on studios.owner_id = users.id
            LEFT JOIN passports on passports.customer_id = users.id
            LEFT JOIN orders on orders.customer_id = users.id
            GROUP BY users.id, studios.name, studios.email, studios.phone, studios.is_retailer, studios.has_classes, passports.number, orders.charge_id) b_search

        WHERE b_search.document @@ to_tsquery(?)
        LIMIT 50;";

        $search = explode(' ', $request->get('search'));
        $search = collect($search)
            ->map(function ($str)
            {
                return "*$str:* & ";
            });

        $search = implode(' ', $search->toArray());

        $result = \DB::select($sql, [trim($search, ' & ')]);

        return $this->response->array(['data' => $result]);
    }

    /**
     * Store a newly created customer in storage.
     *
     * @param  Request  $request
     * @return Response
     */

    public function store (Request $request)
    {

    }

    /**
     * Display the specified customer.
     *
     * @param  int  $id
     * @return Response
     */
    public function show ($uid)
    {
        $user = $this->users->find($uid);

        return $this->response->item($user, $user->getTransformer());
    }
    
    /**
     * Update the specified customer in storage.
     *
     * @param  Request  $request
     * @param  int  $cid
     * @return Response
     */
    public function update (UpdateRequest $request, $uid = null)
    {
        if ($uid) $user = $this->users->find($uid);

        else $user = user();

        $this->users->update ($user, $request->all());

        return $this->response->item ($user, $user->getTransformer());
    }

    /**
     * Remove the specified customer from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy ($uid)
    {
        //
    }

    public function activate (ManagementRequest $request, $uid)
    {
        $user = $this->users->find ($uid);
        
        if ($user->active) {
            return $this->response->error('That user is already active.', 422);
        }

        $user = $this->users->activateByCode ($user->activation_code);

        return $this->response->item($user, $user->getTransformer());
    }

    public function deactivate (ManagementRequest $request, $uid)
    {
        $user = $this->users->find ($uid);

        if (!$user->active) {
            return $this->response->error('That user is already inactive.', 422);
        }

        $user = $this->users->deactivate($user);

        return $this->response->item($user, $user->getTransformer());
    }

    public function sendActivationLetter (ManagementRequest $request, $uid)
    {
        $user = $this->users->find ($uid);

        event(new \App\Events\CustomerHasRegistered($user));

        return $this->response->noContent();
    }
}
