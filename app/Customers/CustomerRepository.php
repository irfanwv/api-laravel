<?php

namespace App\Customers;

use App\Users\User;

use App\Locations\LocationRepository;

use Stripe\Customer as StripeCustomer;

use Dingo\Api\Exception\ResourceException;

class CustomerRepository
{
    protected $model;

    protected $locations;

    public function __construct (Customer $customer, LocationRepository $locations)
    {
        $this->model = $customer;
        $this->locations = $locations;
    }

    public function find ($cid)
    {
        return (new $this->model)->findOrFail($cid);
    }

    public function findByPassportNumber ($number)
    {
        $customer = (new $this->model)
            ->whereHas ('passports', function ($q) use ($number)
            {
                $q->where ('number', $number);
            })
            ->first();

        if (!$customer) throw new ResourceException ('That passport hasn\'t been registered');

        return $customer;
    }

    public function search ($includes = [], $filters = [])
    {
        return $this->model->search($includes, $filters)->get();
    }

    public function searchAndPaginate ($includes = [], $filters = [], $per_page = 20)
    {
        return $this->model->search($includes, $filters)->paginate($per_page);
    }

    public function create ($params)
    {
        return (new $this->model)->fill($params);
    }

    public function save (Customer $customer)
    {
        return $customer->save();
    }

    public function addToCustomerTag (User $user)
    {
        return $user->tags()->attach(2);
    }

    public function makeCustomer (User $user)
    {
        if ($user->customer) return $user->customer;

        $this->addToCustomerTag ($user);

        $customer = $this->create(['user_id'   =>  $user->id]);

        $this->save ($customer);

        return $customer;
    }

    public function useBillingAddress (Customer $customer, $params)
    {
        if ($customer->billing) {
            if ($this->locations->matchLocations ($customer->billing, $params)) {
                return $customer->billing;
            } else {
                $customer->billing->delete();
            }
        }

        $params['type'] = 'billing';

        $location = $this->locations->createNewLocation ($customer, $params);

        $customer->billing()->save ($location);

        return $location;
    }

    public function useShippingAddress (Customer $customer, $params)
    {
        if ($customer->shipping) {
            if ($this->locations->matchLocations ($customer->shipping, $params)) {
                return $customer->shipping;
            } else {
                $customer->shipping->delete();
            }
        }

        $params['type'] = 'shipping';

        $location = $this->locations->createNewLocation($customer, $params);

        $customer->shipping()->save ($location);

        return $location;
    }

    public function prepareForBilling (Customer $customer, $token)
    {
        if (!$customer->readyForBilling()) {

            $gate = $customer->subscription();
            
            $stripe = $gate->createStripeCustomer($token);

            $customer->setStripeId ($stripe->id)
                ->setLastFourCardDigits ($stripe->sources->retrieve($stripe->default_source)->last4)
                ->setStripeIsActive (true)
                ->saveBillableInstance ();
        }

        return collect($this->getCards ($customer))->first();
    }

    public function getCards (Customer $customer)
    {
        if (!$customer->readyForBilling()) {
            return [];
        }

        $cards = $customer->subscription()
            ->getStripeCustomer()
            ->sources
            ->all()['data'];
        
        return $cards;
    }

    public function getCardByLast4 (Customer $customer, $last4)
    {
        if (!$customer->readyForBilling()) {
            return false;
        }

        $cards = $customer->subscription()
            ->getStripeCustomer()
            ->sources
            ->all()['data'];

        return collect($cards)
            ->where('last4', $last4)
            ->first();
    }

    public function addCardToStripe (Customer $customer, $token)
    {
        if (!$customer->isStriped()) {
            return $this->prepareForBilling ($customer, $token);
        }

        $card = $customer->subscription()
            ->getStripeCustomer()
            ->sources
            ->create(['source' => $token]);

        return $card;
    }
}

