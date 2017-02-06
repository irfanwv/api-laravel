<?php

namespace App\Jobs;

use Carbon\Carbon;
use DB;

use App\Jobs\Job;
use App\Tags\Tag;
use App\Users\User;
use App\Studios\Studio;
use App\Passports\Passport;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportPassportJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $import;

    /**
     * Create a new job instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct (\Impulse\Pivot\Passport $import)
    {
        $this->import = $import;
    }

    /**
     * Execute the job.
     *
     * @param  Mailer  $mailer
     * @return void
     */
    public function handle()
    {
        $error = false;

        try {

            $passport = $this->importPassport($this->import);
            
            $this->importHistory($this->import, $passport);

        } catch (\Dingo\Api\Exception\ResourceException $e) {
            
            if ($error) throw $e;

            $error = $e->getMessage();
            $validation = json_encode($e->errors()->toArray());
            $record = json_encode($this->import->toArray());

            $this->writeLog ([$error, $validation, $record], 'passport_validation');

            throw $e;

        } catch (\Illuminate\Database\QueryException $e) {

            if ($error) throw $e;

            $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
            $record = json_encode($this->import->toArray());
            
            $write = [$error, $record];

            if ($this->import->link) array_push($write, $this->import->link->toArray());

            $this->writeLog ($write, 'passport_queries');

            throw $e;

        } catch (\ErrorException $e) {

            if ($error) throw $e;

            $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
            $record = json_encode($this->import->toArray());

            $this->writeLog ([$error, $record], 'passport_errors');

            throw $e;
        } catch (\Exception $e) {

            if ($error) throw $e;

            $error = $e->getMessage();
            $record = json_encode($this->import->toArray());

            $this->writeLog ([$error, $record], 'passport_studio_owners');

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
        $this->writeLog(json_encode($this->import), 'passport_jobs');
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
                    'first_name'    =>  trim($old->link->pl_fname) ? trim($old->link->pl_fname) : 'Unknown',
                    'last_name'     =>  trim($old->link->pl_lname) ? trim($old->link->pl_lname) : 'Unknown',
                    'phone'         =>  'Unknown',
                    'password'      =>  trim($old->link->pl_password) ? trim($old->link->pl_password) : 'pilates1',
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
}
