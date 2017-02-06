<?php

namespace App\Jobs;

use DB;

use Carbon;

use App\Jobs\Job;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Studios\StudioRepository;
use App\Orders\OrderRepository;
use App\Users\UserRepository;
use App\Customers\CustomerRepository;

class ImportRetailJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $import;

    protected $studios;

    protected $orders;

    protected $users;

    protected $customers;

    protected $retailers;

    protected $item;

    /**
     * Create a new job instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct (\Impulse\Pivot\Retailer $import)
    // public function __construct ()
    {
        $this->import = $import;
        // $this->retailers = \Impulse\Pivot\Retailer::all();
    }

    /**
     * Execute the job.
     *
     * @param  Mailer  $mailer
     * @return void
     */
    public function handle (
        StudioRepository $studios,
        OrderRepository $orders,
        UserRepository $users,
        CustomerRepository $customers
    ) {
        $this->studios = $studios;
        $this->orders = $orders;
        $this->users = $users;
        $this->customers = $customers;

        $error = false;
        $item = null;

        try {

            // \DB::transaction(function ()
            // {
                return $this->import($this->import);

                // foreach ($this->retailers as $retailer) {

                //     $this->item = $retailer;
                //     $item = $retailer;

                //     $this->import($retailer);

                // }
            // });


        } catch (\Dingo\Api\Exception\ResourceException $e) {

            if ($error) throw $e;

            $error = $e->getMessage();
            $validation = json_encode($e->errors()->toArray());
            $record = json_encode($item->toArray());

            $this->writeLog ([$error, $validation, $record], 'retail_validation');

            throw $e;

        } catch (\ErrorException $e) {

            if ($error) throw $e;

            $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
            $record = json_encode($item->toArray());

            $this->writeLog ([$error, $record], 'retail_errors');

            throw $e;

        } catch (\Exception $e) {

            if ($error) throw $e;

            $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
            $record = json_encode($item->toArray());

            $this->writeLog ([$error, $record], 'retail_errors');

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed ()
    {
        $this->writeLog(json_encode($this->item), 'retail_jobs');
    }

    public function import ($item)
    {
        if (strtolower($item->retail_name) == 'delete') {
            return true;
        }

        $retailer = \App\Studios\Studio::where('retail_only', true)
            ->where(function ($q) use ($item)
            {
                $q->whereRaw('name ilike E\'%'.addslashes($item->retail_name).'%\'')
                    ->orWhere('name', $item->retail_name);
            })
            ->first();

        if (!$retailer) {

            $user = $this->makeUser ();

            $retailer = $this->makeRetailer ($item, $user);

            $this->users->ownStudio ($user, $retailer);
        
        }

        return $order = $this->makeOrder ($item, $retailer);
    }

    public function makeUser ()
    {
        $user = \App\Users\User::create();
        $user->customer()->save(new \App\Customers\Customer);
        return $user;

        $uid = DB::table('users')
            ->insertGetId([
                'created_at'    =>  Carbon::now(),
                'updated_at'    =>  Carbon::now(),
            ]);

        $cid = DB::table('customers')
            ->insert([
                'user_id'       =>  $uid,
                'created_at'    =>  Carbon::now(),
                'updated_at'    =>  Carbon::now(),
            ]);

        return \App\Users\User::find($uid);
    }

    public function makeRetailer ($item, $user)
    {
        if (filter_var($item->retail_website, FILTER_VALIDATE_URL) === false) {
            $website = null;
        } else {
            $website = $item->retail_website;
        }

        $area_id = \App\Tags\Tag::where('name', $item->city->city_name)->first()->id;

        $rparams = [
            'owner_id'      =>  $user->id,
            'area_id'       =>  $area_id,
            'name'          =>  $item->retail_name,
            'phone'         =>  $item->retail_phone,
            'email'         =>  filter_var($item->retail_email, FILTER_VALIDATE_EMAIL) 
                ? $item->retail_email : 'info@passporttoprana.com',
            'website'       =>  $website,
            'retail'        =>  false,
            'retail_only'   =>  true,
            'created_at'    =>  Carbon::now(),
            'updated_at'    =>  Carbon::now(),
        ];

        // $retailer = $this->studios->create($rparams);

        // $this->studios->save($retailer);

        DB::table('studios')->insert($rparams);

        return \App\Studios\Studio::find($user->id);
    }

    public function makeOrder ($item, $retailer)
    {
        $oparams = [
            'customer_id'   =>  $retailer->owner_id,
            'subtotal'      =>  0,
            'shipping'      =>  0,
            'taxrate'       =>  0,
            'taxes'         =>  0,
            'total'         =>  0,
            'source'        =>  'legacy',
            'status'        =>  'complete',
        ];

        $order = $this->orders->create ($oparams);

        try {

            $this->orders->save ($order);

        } catch (\Exception $e) {

            dd($retailer);

        }

        $city = \App\Tags\Tag::parents()->where('name', $item->city->city_name)->first();

        $item->passports
            ->each(function ($passport) use (&$order, &$retailer, $item, $city)
            {
                $mail = new \App\Mailrooms\Mailroom([
                    'city_id'   =>  $city->id,
                    'price_id'  =>  $retailer->area->price->id,
                    'number'    =>  $passport->pp_number,
                    'status'    =>  'complete'
                ]);

                $order->mail()->save($mail);
                
                try {
                
                    \App\Passports\Passport::whereNumber($passport->pp_number)
                        ->withTrashed()
                        ->firstOrFail()
                        ->fill(['city_id' => $city->id])
                        ->save();
                
                } catch (\Exception $e) {
                    \App\Passports\Passport::create([
                        'number' => $passport->pp_number,
                        'city_id' => $city->id,
                    ]);
                }
            });

        return $order;
    }
}
