<?php

namespace App\Http\Controllers;

use Dingo\Api\Exception\ResourceException;

use Illuminate\Http\Request;

use Captcha;
use DB;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Http\Requests\Customers\RegistrationRequest;
use App\Http\Requests\Customers\UpdateRequest;
use App\Http\Requests\Customers\UpdateAddressRequest;

use App\Customers\CustomerRepository;
use App\Customers\CustomerTransformer;

use App\Passports\PassportRepository;

use App\Events\CustomerHasRegistered;

use App\Users\UserRepository;

class CustomerController extends Controller
{
    protected $customers;
    protected $users;

    public function __construct (CustomerRepository $customers, UserRepository $users)
    {
        $this->customers = $customers;
        $this->users = $users;
    }

    /**
     * Display a listing of the customer.
     *
     * @return Response
     */
    public function index (Request $request)
    {
        $customers = $this->customers->searchAndPaginate();

        return $this->response->paginator($customers, new CustomerTransformer);
    }

    /**
     * Store a newly created customer in storage.
     *
     * @param  Request  $request
     * @return Response
     */

    public function store (RegistrationRequest $request, PassportRepository $passports)
    {
        DB::beginTransaction();

        try {

            if ($num = $request->get('number')) {

                $passport = $passports->findByNumber ($num);

                if (!$passport) {
                    throw new ResourceException ('Sorry, there is no such membership number, please check your card and try again.');
                }

                if ($passport->isFresh()) {
                    // this actually means the card exists but was never configured, they don't need to know that
                    throw new ResourceException ('Sorry, there is no such membership number, please check your card and try again.');
                }

                if (!$passport->isAvailable()) {
                    throw new ResourceException ('Sorry, this card is already activated, please contact customer support if you have any concerns.');
                }

            } else {
                
                $passport = null;
            }

            $personal = $request->only (['first_name', 'last_name', 'email', 'phone', 'password', 'promo']);
            if (!$personal['promo']) $personal['promo'] = false;

            $billing = $request->get ('billing');
            $shipping = $request->get ('shipping');

            if (env('APP_ENV') !== 'adam') {
                Captcha::check ($request->get('recaptcha'));
            }

            $personal['password'] = bcrypt($personal['password']);
            $personal['active'] = true; // everyone is active now

            $user = $this->users->create ($personal);
        
            $this->users->save ($user);

            $customer = $this->customers->makeCustomer ($user);

            if ($passport) $passports->activate ($customer, $passport);

            if ($billing) $this->customers->useBillingAddress ($customer, $billing);

            if ($shipping) $this->customers->useShippingAddress ($customer, $shipping);
        
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        event(new CustomerHasRegistered ($user));

        return $this->response->item ($user, $user->getTransformer());
    }

    /**
     * Update the specified customer in storage.
     *
     * @param  Request  $request
     * @param  int  $cid
     * @return Response
     */
    public function billing (UpdateAddressRequest $request, $cid)
    {
        DB::beginTransaction();
        
        try {
            // $customer = $this->customers->find ($cid);
            $user = $this->users->find ($cid); // users and customers have the same id

            if (!$customer = $user->customer) { 
                $customer = $this->customers->makeCustomer ($user); 
            }

            $location = $this->customers->useBillingAddress ($customer, $request->all());

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $this->response->item ($location, $location->getTransformer());
    }

    /**
     * Update the specified customer in storage.
     *
     * @param  Request  $request
     * @param  int  $cid
     * @return Response
     */
    public function shipping (UpdateAddressRequest $request, $cid)
    {
        DB::beginTransaction();
        
        try {
            $user = $this->users->find ($cid);

            if (!$customer = $user->customer) { 
                $customer = $this->customers->makeCustomer ($user); 
            }

            $location = $this->customers->useShippingAddress ($customer, $request->all());

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $this->response->item ($location, $location->getTransformer());
    }
}
