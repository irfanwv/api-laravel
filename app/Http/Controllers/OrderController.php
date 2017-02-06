<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Event;

use App\Http\Requests;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\Orders\FulfillmentRequest;
use App\Http\Requests\Orders\CancelRequest;

use App\Http\Controllers\Controller;

use App\Orders\OrderRepository;
use App\Customers\CustomerRepository;
use App\Mailrooms\MailroomRepository;

use App\Events\OrderWasCreated;
use App\Events\OrderWasFulfilled;

use App\Exceptions\PaymentException;

class OrderController extends Controller
{
    protected $orders;

    protected $mailroom;

    /**
     * Inject repositories
     *
     * @return Response
     */
    public function __construct (OrderRepository $orders, MailroomRepository $mailroom)
    {
        $this->orders = $orders;
        $this->mailroom = $mailroom;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store (CheckoutRequest $request, CustomerRepository $customers)
    {
        $cart = $request->get('cart');
        // grab either the token or the source, only one should be set
        $token = $request->get('token');
        $source = $request->get('source');

        // a lot of things happen in this method
        ini_set('xdebug.max_nesting_level', 200);

        // check their coupon first
        if ($coupon = $request->get('code')) {
            // if it's no good
            $coupon = $this->orders->isCoupon ($coupon); // throws 422 if not
        }   // no sense doing any more

        DB::beginTransaction();

        try {

            /* decide which user this order is for */
            $user = auth()->user();

            // if this is an admin
            if ($user->isAdmin() && $uid = $request->route('uid')) {
                // find the user this was intended for
                $user = $customers->find ($uid)->user;
            }

            // if they don't have a customer record
            if (!$user->customer) {
                // make it
                $customer = $customers->makeCustomer ($user);
            } else {
                $customer = $user->customer;
            }

            // if they supplied a token
            if (isset($token)) {
                // add it to stripe
                $source = $customers->addCardToStripe ($customer, $token)
                    ->id; // this will prepare them for billing if they haven't been already
            
            // and if they didn't
            } else if (!isset($source)) { // they need to supply which source to use
                throw new ResourceException ('You must supply new card details or select an old card.');
            }

            // create the order info
            $order = $this->orders->createFromCart ($customer, $cart, $coupon);

            // charge for it
            $this->orders->saveAndCharge ($order, $source);

            // fulfills renews/extends, and adds the rest to the mailroom
            $this->mailroom->processCartForOrder ($order, $cart);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        // let everyone know it went well
        event(new OrderWasCreated($order, $cart));

        // client doesn't need to know anything but that it was good.
        return $this->response->noContent();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
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
        $items = $request->get('items');

        $order = $this->orders->find($id);

        DB::transaction(function () use ($order, $items)
        {
            $this->orders->fulfill($order, $items);

            Event::fire(new OrderWasFulfilled($order));
        });

        return $this->response->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy (CancelRequest $request, $id, OrderRepository $orders)
    {
        $order = $orders->find($id);

        $orders->cancel($order);

        return $this->response->noContent();
    }

    public function shipping (Request $request, $oid, OrderRepository $orders)
    {
        $order = $orders->find($oid);
        
        $location = $this->api
            ->be(auth()->user())
            ->with($request->input())
            ->put('customers/' . $order->customer->user_id . '/shipping');

        $orders->changeShipping ($order, $location);

        return $this->response->item ($location, $location->getTransformer());
    }

    public function verify (Request $request, OrderRepository $orders)
    {
        if (!$request->get('code'))
            return $this->response->error('You must supply a coupon code to check.', 422);

        $coupon = $orders->isCoupon($request->get('code'));

        return $this->response->item($coupon, new \App\Stripe\CouponTransformer);
    }
}
