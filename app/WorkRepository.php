<?php

namespace App;

use App\Locations\LocationRepository;

class WorkRepository {
    
    protected $locations;

    public function __construct (
        LocationRepository $locations
    ) {
        $this->locations = $locations;
    }

    public function orders ()
    {
        \Impulse\Pivot\Studio::with(['passports'])
            ->has('passports')
            ->get()
            ->each(function ($studio)
            {
                $new = \App\Studios\Studio::where('name', 'like', "%$studio->studio_name%")
                    ->withTrashed()
                    ->first();

                if (!$new) { dd($new); }

                $user = $new->owner;

                // if they don't have a customer record
                if (!$user->customer) {
                    // make it
                    $customer = app('\App\Customers\CustomerRepository')->makeCustomer ($user);
                } else {
                    $customer = $user->customer;
                }

                $order = \App\Orders\Order::create([
                    'charge_id' => 'legacy',
                    'customer_id' => $user->id,
                    'shipping_id' => null,
                    'coupon' => null,
                    'currency' => 'cad',
                    'subtotal' => 0,
                    'shipping' => 0,
                    'discount' => 0,
                    'taxrate' => 0,
                    'taxes' => 0,
                    'total' => 0,
                    'source' => 'legacy',
                    'status' => 'complete',
                    'created_at' => '2000-01-01',
                ]);

                var_dump ($studio->passports->count() . ' passports for ' . $new->name);

                $studio->passports
                    ->each(function ($passport) use ($order)
                    {
                        $new = \App\Passports\Passport::where('number', $passport->pp_number)
                            ->withTrashed()
                            ->first();

                        if ($new) {
                            $mail = new \App\Mailrooms\Mailroom([
                                'price_id' => $new->city()->withTrashed()->first()->price->id,
                                'city_id' => $new->city()->withTrashed()->first()->id,
                                'number' => $passport->pp_number,
                                'type' => 'new',
                                'status' => 'complete',
                                'created_at' => '2000-01-01',
                            ]);

                            $order->mail()->save($mail);
                        } else {
                            var_dump('missing ' . $passport->pp_number);
                        }
                    });
            });

        dd('so far so good');
    }

    public function dogeo ($location)
    {
        try {
            
            return $this->locations->geoLocate($location->address1, $location->city, $location->province.', '.$location->country);

        } catch (\Exception $e) {
            
            if ($e->getMessage() == 'OVER_QUERY_LIMIT') {
                
                // \App\Notifier::notify ($e->getMessage().', waiting 1 second and trying again.')
                //     ->via ('slack');
                
                sleep(1);
                
                return $this->dogeo($location);

            } else {
                
                \App\Notifier::notify ($e->getMessage().': '.json_encode($location))
                    ->via ('slack');
                
                return false;
            }
        }
    }

    public function reGeoAll ()
    {
        \App\Notifier::notify ('Running the geocoder against every location record.')
            ->via ('slack');

        \App\Locations\Location::all()
            ->each(function ($location)
            {
                if ($geo = $this->dogeo($location)) {

                    $location->fill($geo);

                    $this->locations->save($location);
                }
            });

        \App\Notifier::notify ('Geocoding complete.')
            ->via ('slack');

        return true;
    }

    public function killDupePassports ()
    {
        $passports = \App\Passports\Passport::selectRaw('COUNT(*), number')
            ->whereNotIn('number', [178818, 197118, 220905])
            ->havingRaw('COUNT(*) > 1')
            ->groupBy('number')
            ->get();

        $passports->each(function ($pp)
        {
            $done = 0;
            $kept = 0;

            $win = \App\Passports\Passport::where('number', $pp->number)
                ->orderBy('activated_at')
                ->get();

            $win->each(function ($p) use (&$done, &$kept, $pp, $win)
            {
                // if it's active, that's the keeper
                if ($p->isActive()) {
                    $kept++;
                    $done++;
                    return;
                }

                // if it's configured
                if ($p->city_id) {
                    // and there's no active one
                    if ($kept === 0) {
                        $kept++;
                        $done++;
                        return;
                    }
                }

                if ($p->deleted_at) {
                    $kept++;
                    $done++;
                    return;
                }

                // if this is the last one
                if ($done == $pp->count - 1) {
                    // and we haven't kept any
                    if ($kept === 0) {
                        $kept++;
                        $done++;
                        return;
                    }
                }

                // if all that failed
                $p->forceDelete();
                $done++;
                return;
            });
        });

        return true;
    }
}
