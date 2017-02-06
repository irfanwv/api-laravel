<?php

namespace App\Jobs;

use DB;

use App\Jobs\Job;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportStudioJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $import;

    /**
     * Create a new job instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct (\Impulse\Pivot\Studio $import)
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

            return $this->import();

        } catch (\Dingo\Api\Exception\ResourceException $e) {

            if ($error) throw $e;

            $error = $e->getMessage();
            $validation = json_encode($e->errors()->toArray());
            $record = json_encode($this->import->toArray());

            $this->writeLog ([$error, $validation, $record], 'studio_validation');

            throw $e;

        } catch (\ErrorException $e) {

            if ($error) throw $e;

            $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
            $record = json_encode($this->import->toArray());

            $this->writeLog ([$error, $record], 'studio_errors');

            throw $e;

        } catch (\Exception $e) {

            if ($error) throw $e;

            $error = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
            $record = json_encode($this->import->toArray());

            $this->writeLog ([$error, $record], 'studio_errors');

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
        $this->writeLog(json_encode($this->import), 'studio_jobs');
    }

    public function import ()
    {
        $tag = \App\Tags\Tag::where('name', $this->import->subcity->sc_name)->firstOrFail();
        
        $params = array_merge(['area_id' => $tag->id], $this->makeParams());
        
        $studio = app('Dingo\Api\Dispatcher')
            ->be(\App\Users\User::find(1))
            ->post('studios', $params);

        $studio->tags()->attach($tag);

        if ($this->import->passports()->where('pp_active', 0)->count()) {
            $studio->retail = true;
            $studio->save();
        }

        if ($this->import->studio_hide) {
            $studio->delete();
        }

        if ($this->import->profile) {
            DB::table('legacy')->insert([
                'user_id'   =>  $studio->owner_id,
                'login'     =>  $this->import->profile->sp_username,
                'password'  =>  bcrypt($this->import->profile->sp_password)
            ]);
        }

        return $studio;
    }

    public function makeParams ()
    {
        $email = trim(trim(trim($this->import->studio_email), ','), '.');

        return [
            'old_id'        =>  $this->import->studio_id,
            'first_name'    =>  ($this->import->studio_contact) ? $this->import->studio_contact : 'N\A',
            'last_name'     =>  'N\A',
            'email'         =>  str_replace('\t', '', trim($email)),
            'phone'         =>  $this->import->studio_tel,
            // 'password'      =>  $this->import->profile->password,

            'studio_name'   =>  $this->import->studio_name,
            'studio_email'  =>  $this->import->studio_email,
            'studio_phone'  =>  $this->import->studio_tel,
            'website'       =>  $this->import->studio_website,
            'description'   =>  null,
            
            'address1'  =>  $this->import->studio_address,
            // 'address2'  =>  $this->import->,
            'postal'    =>  $this->import->studio_postal,
            'city'      =>  $this->import->studio_city,
            'province'  =>  $this->import->studio_prov,
            'country'   =>  $this->import->studio_country,
            'lat'       =>  $this->import->lat,
            'lng'       =>  $this->import->lng,
        ];
    }
}