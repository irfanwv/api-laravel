<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Http\Requests\ManagementRequest;
use App\Http\Requests\Passports\ActivatePassportRequest;

use App\Passports\PassportRepository;
use App\Users\UserRepository;

class CustomerPassportsController extends Controller
{
    protected $passports;
    protected $users;

    public function __construct (PassportRepository $passports, UserRepository $users)
    {
        $this->passports = $passports;
        $this->users = $users;
    }

    /**
     * Makes sense from a REST persepective to have a copy of this function here
     * even though it's for management only at the moment
     *
     * PUT /customers/{cid}/passports/{$ppnum}/renew
     * A renewal technically creates a new record, though from the standpoint of 
     *  the user, they are updating their current physical pass to continue working
     * 
     * @param  int  $id
     * @return Response
     */
    public function renew (ManagementRequest $request, $cid, $ppnum)
    {
        $pp = $this->passports->findByNumber($ppnum);

        if ($pp->customer_id != $cid)
            return $this->response->errorForbidden('That passport doesn\'t belong to that user.');

        $pp = $this->passports->renew ($pp);

        return $this->response->item($pp, $pp->getTransformer());
    }

    /**
     * Activate a passport on the given users account.
     *
     * POST /customers/{cid}/passports/{$ppnum}/activate
     * Even though activate is technically an update, it's more like a create
     *  in the sense that this passport was not active in the system before.
     *
     * @param  Request  $request
     * @return Response
     */
    public function activate (ActivatePassportRequest $request, $cid, $ppnum)
    {
        $user = $this->users->find ($cid);
        
        $pp = $this->passports->findByNumber ($ppnum);

        $this->passports->activate ($user->customer, $pp);

        return $this->response->item ($pp, $pp->getTransformer());
    }

}
