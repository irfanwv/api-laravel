<?php

namespace App\Console\Commands;

use DB;
use Storage;
use Carbon\Carbon;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;

use App\Users\User;
use App\Tags\Tag;
use App\Prices\Price;

use App\Studios\StudioRepository;
use App\Orders\OrderRepository;
use App\Users\UserRepository;
use App\Customers\CustomerRepository;

class ImportOldStudios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'p2p:import';

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
    protected $studios;
    protected $retailers;


    protected $studioRepository;
    protected $orderRepository;
    protected $userRepository;
    protected $customerRepository;

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle (
        StudioRepository $studios,
        OrderRepository $orders,
        UserRepository $users,
        CustomerRepository $customers
    ) {
        $this->studioRepository = $studios;
        $this->orderRepository = $orders;
        $this->userRepository = $users;
        $this->customerRepository = $customers;

        $this->tags = Tag::all();
        $this->studios = collect();
        $this->retailers = collect();
        
        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Importing Cities');
        
        $this->importCities();

        $this->info('');
        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Importing Studios');
        
        $this->importStudios();

        $this->info('');
        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Importing Retailers');
        
        $this->importRetailers();

        $this->info('');
        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Importing Passports');

        $this->importPassports();

        $this->info('');
        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Done');
    }

    public function importCities ()
    {
        $cities = \Impulse\Pivot\City::all();
        $subcities = \Impulse\Pivot\SubCity::all();
        
        $bar = $this->output->createProgressBar(count($cities) + count($subcities));

        $cities->each(function ($city) use ($bar)
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

            $bar->advance();
        });

        $subcities->each(function ($subcity) use ($bar)
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

            $bar->advance();
        });

        $bar->finish();
    }

    public function importStudios ()
    {
        $import = function ($studio)
        {
            $makeParams = function ($studio)
            {
                $email = trim(trim(trim($studio->studio_email), ','), '.');

                return [
                    'old_id'        =>  $studio->studio_id,
                    'first_name'    =>  ($studio->studio_contact) ? $studio->studio_contact : 'N\A',
                    'last_name'     =>  'N\A',
                    'email'         =>  str_replace('\t', '', trim($email)),
                    'phone'         =>  $studio->studio_tel,
                    // 'password'      =>  $studio->profile->password,

                    'studio_name'   =>  $studio->studio_name,
                    'studio_email'  =>  $studio->studio_email,
                    'studio_phone'  =>  $studio->studio_tel,
                    'website'       =>  $studio->studio_website,
                    'description'   =>  null,
                    
                    'address1'  =>  $studio->studio_address,
                    // 'address2'  =>  $studio->,
                    'postal'    =>  $studio->studio_postal,
                    'city'      =>  $studio->studio_city,
                    'province'  =>  $studio->studio_prov,
                    'country'   =>  $studio->studio_country,
                    'lat'       =>  $studio->lat,
                    'lng'       =>  $studio->lng,
                ];
            };

            $tag = \App\Tags\Tag::where('name', $studio->subcity->sc_name)->firstOrFail();
            // $tag = $this->tags->where('name', $studio->subcity->sc_name)->first();

            $params = array_merge(['area_id' => $tag->id], $makeParams($studio));

            $new = app('Dingo\Api\Dispatcher')
                ->be(\App\Users\User::find(1))
                ->post('studios', $params);

            $new->tags()->attach($tag);

            if ($studio->passports()->where('pp_active', 0)->count()) {
                $new->retail = true;
                $new->save();
            }

            if ($studio->studio_hide) {
                $new->delete();
            }

            if ($studio->profile) {
                DB::table('legacy')->insert([
                    'user_id'   =>  $new->owner_id,
                    'login'     =>  $studio->profile->sp_username,
                    'password'  =>  bcrypt($studio->profile->sp_password)
                ]);
            }

            return $new;
        };

        $studios = \Impulse\Pivot\Studio::with([
                'subcity.city.retail',
                'subcity.city.wholesale'
            ])
            ->get();

        $bar = $this->output->createProgressBar(count($studios));

        $studios->each(function ($studio, $key) use ($bar, $import)
        {
            $error = false;
            
            try {

                $this->studios->push($import($studio));

            } catch (\Dingo\Api\Exception\ResourceException $e) {

                if ($error) throw $e;

                $error = $e->getMessage();
                $validation = json_encode($e->errors()->toArray());
                $record = json_encode($studio->toArray());

                $this->writeLog ([$error, $validation, $record], 'studio_validation');

                throw $e;

            } catch (\ErrorException $e) {

                if ($error) throw $e;

                $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
                $record = json_encode($studio->toArray());

                $this->writeLog ([$error, $record], 'studio_errors');

                throw $e;

            } catch (\Exception $e) {

                if ($error) throw $e;

                $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
                $record = json_encode($studio->toArray());

                $this->writeLog ([$error, $record], 'studio_errors');

                throw $e;
            }

            $bar->advance();
        });

        $bar->finish();
    }

    public function importRetailers ()
    {
        $import = function ($retailer)
        {
            $makeOrder = function ($retailer, $new)
            {
                $oparams = [
                    'customer_id'   =>  $new->owner_id,
                    'subtotal'      =>  0,
                    'shipping'      =>  0,
                    'taxrate'       =>  0,
                    'taxes'         =>  0,
                    'total'         =>  0,
                    'source'        =>  'legacy',
                    'status'        =>  'complete',
                ];

                $order = $this->orderRepository->create ($oparams);

                $this->orderRepository->save ($order);

                $retailer->passports
                    ->each(function ($passport) use (&$order, &$new, $retailer)
                    {
                        $city = \App\Tags\Tag::where('name', $retailer->city->city_name)->first();
                        
                        $mail = new \App\Mailrooms\Mailroom([
                            'city_id'   =>  $city->id,
                            'price_id'  =>  $new->area->price->id,
                            'number'    =>  $passport->pp_number,
                            'status'    =>  'complete'
                        ]);

                        $order->mail()->save($mail);
                    });

                return $order;
            };

            $makeUser = function ()
            {
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
            };

            $makeRetailer = function ($retailer, $user)
            {
                if (filter_var($retailer->retail_website, FILTER_VALIDATE_URL) === false) {
                    $website = null;
                } else {
                    $website = $retailer->retail_website;
                }

                $area_id = \App\Tags\Tag::where('name', $retailer->city->city_name)->first()->id;

                $rparams = [
                    'owner_id'      =>  $user->id,
                    'area_id'       =>  $area_id,
                    'name'          =>  $retailer->retail_name,
                    'phone'         =>  $retailer->retail_phone,
                    'email'         =>  $retailer->retail_email,
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
            };

            if (strtolower($retailer->retail_name) == 'delete') {
                return true;
            }

            $new = \App\Studios\Studio::whereRaw('name ilike E\''.addslashes($retailer->retail_name).'\'')
                ->orWhere('name', $retailer->retail_name)
                ->first();

            if (!$new) {

                $user = $makeUser ();

                $new = $makeRetailer ($retailer, $user);

                $this->userRepository->ownStudio ($user, $new);

            }

            $makeOrder ($retailer, $new);

            return $new;
        };

        $retailers = \Impulse\Pivot\Retailer::all();

        $bar = $this->output->createProgressBar(count($retailers));

        $retailers->each(function ($retailer, $key) use ($bar, $import)
        {
            $error = false;

            try {

                $this->retailers->push($import($retailer));

            } catch (\Dingo\Api\Exception\ResourceException $e) {

                if ($error) throw $e;

                $error = $e->getMessage();
                $validation = json_encode($e->errors()->toArray());
                $record = json_encode($retailer->toArray());

                $this->writeLog ([$error, $validation, $record], 'retail_validation');

                throw $e;

            } catch (\ErrorException $e) {

                if ($error) throw $e;

                $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
                $record = json_encode($retailer->toArray());

                $this->writeLog ([$error, $record], 'retail_errors');

                throw $e;

            } catch (\Exception $e) {

                if ($error) throw $e;

                $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
                $record = json_encode($retailer->toArray());

                $this->writeLog ([$error, $record], 'retail_errors');

                throw $e;
            }

            $bar->advance();
        });

        $bar->finish();
    }

    public function importPassports ()
    {
        $this->info('');
        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Gathering Passport Data');

        $passports = \Impulse\Pivot\Passport::with(['link.city', 'link.studios'])
            ->orderBy('pp_id', 'asc')
            ->get();

        $this->info('');
        $this->info('['.Carbon::now()->format('Y-m-d H:i:s').'] Beginning Import');

        $bar = $this->output->createProgressBar(count($passports));

        $passports->each(function ($passport) use ($bar)
        {
            $error = false;

            try {

                $new = $this->importPassport($passport);
                
                $this->importHistory($passport, $new);

            } catch (\Dingo\Api\Exception\ResourceException $e) {
                
                if ($error) throw $e;

                $error = $e->getMessage();
                $validation = json_encode($e->errors()->toArray());
                $record = json_encode($passport->toArray());

                $this->writeLog ([$error, $validation, $record], 'passport_validation');

                throw $e;

            } catch (\Illuminate\Database\QueryException $e) {

                if ($error) throw $e;

                $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
                $record = json_encode($passport->toArray());
                
                $write = [$error, $record];

                if ($passport->link) array_push($write, $passport->link->toArray());

                $this->writeLog ($write, 'passport_queries');

                throw $e;

            } catch (\ErrorException $e) {

                if ($error) throw $e;

                $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
                $record = json_encode($passport->toArray());

                $this->writeLog ([$error, $record], 'passport_errors');

                throw $e;
            } catch (\Exception $e) {

                if ($error) throw $e;

                $error = $e->getMessage();
                $record = json_encode($passport->toArray());

                $this->writeLog ([$error, $record], 'passport_studio_owners');

                throw $e;
            }
            $bar->advance();
        });

        $bar->finish();
    }

    public function importPassport ($old)
    {
        if ($link = $old->link) {

            $activated_at = Carbon::parse($old->link->pl_expiry_date)->subYear();
            $expires_at = Carbon::parse($old->link->pl_expiry_date);
            
            $ocity = $old->link->city;

            $city = Tag::where('name', $ocity->city_name)->first();

            $city_id = $city->id;

            $email = trim($old->link->pl_email, ' ,');

            $user = User::whereRaw('email ilike E\'%'.$email.'%\'')->first();

            if (!$user) {

                $personal = [
                    'first_name'    =>  trim($old->link->pl_fname),
                    'last_name'     =>  trim($old->link->pl_lname),
                    'phone'         =>  'Unknown',
                    'password'      =>  trim($old->link->pl_password),
                    'email'         =>  $email,
                ];
                
                $user = app('Dingo\Api\Dispatcher')
                    ->with($personal)
                    ->post('auth/register');
            } else {
                if ($user->isStudioOwner()) {
                    throw new \Exception ('This passport is held by a studio owner.');
                }
            }

            DB::table('legacy')->insert([
                'user_id'   =>  $user->id,
                'login'     =>  $old->pp_number,
                'password'  =>  bcrypt($old->link->pl_password),
            ]);
        }

        if ($old->studio) {
            $name = $old->studio->subcity->city->city_name;
            $city_id = Tag::where('name', $name)->first()->id;
        }

        if ($old->retail) {
            $name = $old->retail->city->city_name;
            $city_id = Tag::where('name', $name)->first()->id;
        }

        return Passport::create([
            'customer_id'   =>  (isset($old->link)) ? $user->id : null,
            'number'        =>  $old->pp_number,
            'city_id'       =>  (isset($city_id)) ? $city_id : null,
            'activated_at'  =>  (isset($activated_at)) ? $activated_at : null,
            'expires_at'    =>  (isset($expires_at)) ? $expires_at : null,
            'customer_id'   =>  (isset($user)) ? $user->id : null,
        ]);
    }

    public function importHistory ($old, $passport)
    {
        if (!$link = $old->link) return null;
        if (!count($link->studios)) return null;

        $log = [];

        array_push($log, 'old_id : ' . $old->pp_id.', new_id : ' . $passport->id);
        array_push($log, 'uses : ' . count($link->studios));
        
        $line = $link->studios
            ->each(function ($s) use ($log)
            {
                $line = [
                    'studio_id'   => $s->studio_id,
                    'studio_name' => $s->studio_name
                ];

                array_push($log, json_encode($line));
            });

        foreach ($link->studios as $old_studio) {
            $new = Studio::where('name', $old_studio->studio_name)->first();
            
            if ($new) {// once the rest of the studios don't fail, this should be always
                $passport->studios()->attach($new->owner_id, [ 
                    'created_at' => Carbon::now(),
                    'marked_by'  => $new->owner_id,
                ]);

                array_push($log, json_encode(['new_id' => $new->owner_id, 'name' => $new->name]));
            } else {
                array_push($log, 'missing studio');
            }
        }

        $this->writeLog($log, 'passport_history');
    }

    public function extract_email_address ($string)
    {
       $emails = array();
       $string = str_replace("\r\n", '', $string);
       $string = str_replace("\n", '', $string);

       foreach(preg_split('/ /', $string) as $token) {
            $email = filter_var($token, FILTER_VALIDATE_EMAIL);
            if ($email !== false) { 
                $emails[] = $email;
            }
        }
        return $emails;
    }

    public function writeLog ($text, $log)
    {
        $write = function ($t, $l)
        {
            if (Storage::exists($l.'.log')) {
                Storage::append($l.'.log', $t);
            } else {
                Storage::put($l.'.log', $t);
            }
        };
        
        if (is_array ($text)) {
            foreach ($text as $value) {
                $write ($value, $log);
            }
        } else {
            $write ($text, $log);
        }

        $write ('', $log);
    }
}
