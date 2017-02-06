<?php

namespace App\Console\Commands;

use Storage;
use Carbon\Carbon;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;

use App\Users\User;
use App\Tags\Tag;
use App\Prices\Price;

use Illuminate\Foundation\Bus\DispatchesJobs;

class LegacyImport extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'p2p:importQueue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import studios from the old site.';

    /**
     * A collection to hold the cities as we make them.
     *
     * @var string
     */
    protected $tags;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle ()
    {
        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Firing city/studios/retailers jobs.');

        // $this->tags = collect();

        // $this->importCities();

        // $this->importStudios();

        // $this->importRetailers();

        $this->work2();

        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Done.');
    }

    public function work2 ()
    {
        $orders = app('App\Orders\OrderRepository');
        $customers = app('App\Customers\CustomerRepository');

        $studios = \Impulse\Pivot\Studio::with('passports')
            ->has('passports')
            ->get();

        $bar = $this->output->createProgressBar($studios->count());

        $studios->each(function ($studio) use ($orders, $customers, $bar)
        {
            $new = \App\Studios\Studio::where('name', 'ilike', '%'.$studio->studio_name.'%')->withTrashed()->firstOrFail();

            if (!$new->owner->customer)
                $customer = $customers->makeCustomer($new->owner);

            $ppcount = $new->passports->count();
            
            $area = $new->area()->withTrashed()->first();
            $parent = $area->parent()->withTrashed()->first();

            if ($parent) $price = $parent->price;
            else $price = $area->price;

            $order = $orders->create([
                'customer_id'   =>  $new->owner->id,
                'subtotal'      =>  $subtotal = $ppcount * $price->bulk_price,
                'shipping'      =>  $shipping = \App\Shipping::bulkRate($ppcount, strtolower($area->country)),
                'taxrate'       =>  $taxrate = \App\Tax::get($area->province),
                'taxes'         =>  $taxes = $subtotal * $taxrate,
                'total'         =>  $subtotal + $shipping + $taxes,
                'source'        =>  'legacy',
                'status'        =>  'complete',
            ]);

            $orders->save($order);

            $new->passports
                ->each (function ($passport) use ($order, $price)
                {
                    $order->mail()
                        ->save(new \App\Mailrooms\Mailroom([
                            'city_id' => $passport->city_id,
                            'price_id' => $price->id,
                            'number' => $passport->number,
                            'status' => 'complete',
                            'type' => 'wholesale'
                        ]));
                });

            $bar->advance();
        });

        $bar->finish();

        dd('ding');
    }

    public function work ()
    {
        $repo = app('\App\Passports\PassportRepository');
        $trans = \Impulse\Pivot\Trans::all();
        
        $bar = $this->output->createProgressBar($trans->count());

        $missing = 0;
        $conflicts = 0;

        $trans->each (function ($t) use ($bar, &$missing, &$conflicts, $repo)
        {

            try {
                $new = $repo->findByNumber($t->pp_number);
                $city = \App\Tags\Tag::where('name', $t->city->city_name)->first();

                if ($new->city && $new->city->name != $t->city->city_name) {
                    
                    $message = ($new->isActive() ? 'Active' : 'Inactive') . ' Passport # ' . $t->pp_number;

                    $message .= ', ' . $new->city->name . ' would change to ' . $t->city->city_name;

                    // \Slack::to('@adam')->send($message);
                    
                    $conflicts++;

                    return;
                }

                if (!$new->city && $city) {
                    $new->city_id = $city->id;
                    $new->save();
                }

            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {


                $passport = $repo->create([
                    'number' => $t->pp_number,
                    'city_id' => isset($city) ? $city->id : null
                ]);
                
                $repo->save($passport);

                $missing++;
            }

            $bar->advance();
        });

        $bar->finish();

        $this->info('');
        $this->info($missing. ' missing');
        $this->info($conflicts. ' conflicts');
    }

    public function write ($text, $log)
    {
        if (Storage::exists($log.'.log')) {
            Storage::append($log.'.log', $text);
        } else {
            Storage::put($log.'.log', $text);
        }
    }

    public function importCities ()
    {
        $cities = \Impulse\Pivot\City::all();
        $subcities = \Impulse\Pivot\SubCity::all();
        
        $cities->each(function ($city)
        {
            $parent = Tag::create([
                'name'  =>  $city->city_name,
                'type'  =>  'App\Studios\Studio'
            ]);

            $price = Price::create([
                'area_id'       =>  $parent->id,
                'unit_price'    =>  ($v = $city->retail) ? $v->rp_value : 30.00,
                'bulk_price'    =>  ($v = $city->wholesale) ? $v->wp_value : 15.00,
            ]);

            $this->tags->push($parent);
        });

        $subcities->each(function ($subcity)
        {
            $parent = $this->tags
                ->filter(function ($t) use ($subcity)
                    { return $t->name == $subcity->city->city_name; })
                ->first();

            $child = Tag::create([
                'parent_id'     =>  $parent->id,
                'name'          =>  $subcity->sc_name,
                'type'          =>  'App\Studios\Studio',
            ]);            
        });
    }

    public function importRetailers ()
    {
        // $this->dispatch((new \App\Jobs\ImportRetailJob())->onQueue('imports'));

        $retails = \Impulse\Pivot\Retailer::all();

        $bar = $this->output->createProgressBar($retails->count());

        $retails->each(function ($retail) use ($bar)
        {
            $this->dispatch((new \App\Jobs\ImportRetailJob($retail))->onQueue('imports'));

            $bar->advance();
        });

        $bar->finish();
    }

    public function importStudios ()
    {
        $studios = \Impulse\Pivot\Studio::all();
        
        $studios->each(function ($studio)
        {
            $this->dispatch((new \App\Jobs\ImportStudioJob($studio))->onQueue('imports'));
        });

        $this->dispatch((new \App\Jobs\ImportPassportsJob())->onQueue('imports'));
    }
}
