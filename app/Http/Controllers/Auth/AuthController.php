<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use DB;
use Event;
use Log;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests\Users\LegacyConfigurationRequest;
use App\Http\Requests\Users\ActivationRequest;

use JWTAuth;
use JWTException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\ResourceException;

use App\Http\Controllers\Controller;
use App\Users\UserRepository;
use App\Users\User;

use App\Passports\PassportTransformer;
use App\Locations\LocationTransformer;

use App\Events\LegacyLoginConverted;

use App\Notifier;

class AuthController extends Controller
{
    protected $users;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct (UserRepository $users)
    {
        // $this->middleware();
        $this->users = $users;
    }

    /**
     * Turn credentials into tokens
     */
    public function authenticate (Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {

                // try a legacy login
                if (!$login = $this->legacy($request)) {
                    return $this->response->errorForbidden('Invalid Credentials.');
                }

                return $login;

            } else {

                $user = $this->users->findByEmail($request->input('email'));
                
                /* this could happen for studio accounts, where their old credentials are the same as their new ones. */
                if ($user->legacy->count()) {

                    $meta = []; 
            
                    if ($studio = $user->studio()->withTrashed()->first()) {
                        
                        $meta['studio'] = $studio->getTransformer()->transform($studio);
                        
                        $meta['studio']['location'] = $studio->location->getTransformer()->transform($studio->location);
                    }

                    if ($user->customer && $user->customer->passports) {
                        $meta['passports'] = $user->customer
                            ->passports
                            ->map(function ($pp)
                            {
                                return $pp->getTransformer()->transform($pp);
                            });
                    }
                    
                    return $this->response->array([
                        'token' =>  'legacy',
                        'data'  =>  $user->getTransformer()->transform($user),
                        'meta'  =>  $meta,
                    ]);
                }

                if ($user->deleted_at) {
                    return $this->response->errorForbidden('Your account has been deactivated.');
                }
                
                if (!$user->active) {
                    // // active flag has been de activated
                    // return $this->response->errorForbidden('Your account must be activated before continuing.');
                }

                $studio = $user->studio()->withTrashed()->first();
                
                if ($studio && $studio->deleted_at) {
                    return $this->response->errorForbidden ('Your account has been deactivated.');
                }
                   // header("Authorization: Bearer $token");
                    //echo $user;
                    $array = json_decode($user, true);
                    $_SESSION['activation_code']=$token;
                    
                  // echo $user = json_encode($array);
                  
                 return $this->response->item ($user, $user->getTransformer());
                    //->withHeader ('Authorization', 'Bearer ' . $token);
                    
            }

        } catch (JWTException $e) {
            // something went wrong
            return $this->response->errorBadRequest('Can\'t digest your token.');
        }
    }

    /* basically ripped it right out of the default midddleware */
    public function refresh (Request $request)
    {
        try {
        
            $token = app('Tymon\JWTAuth\JWTAuth')
                ->setRequest($request)
                ->parseToken()
                ->refresh();

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return $this->response->errorForbidden('Your session has expired.');
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->response->errorUnauthorized('Couldn\'t digest your authorization token.');
        }

        return $this->response->noContent()
            ->withHeader('Authorization', 'Bearer ' . $token); 
    }

    /**
     * Check if a failed authentication exists as a legacy login
     */
    public function legacy (Request $request)
    {
        $login = \App\Users\Legacy::where('login', $request->get('email'))->first();

        if (!$login) return false;

        $clear = $request->get('password');
        
        $hash = $login->password;

        if (!\Hash::check($clear, $hash))
            return false;

        $user = $login->user;
        $meta = []; 
        
        if ($studio = $user->studio) {
            
            $meta['studio'] = $studio->getTransformer()->transform($studio);
            
            $meta['studio']['location'] = $studio->location->getTransformer()->transform($studio->location);
        }

        if ($user->customer && $user->customer->passports) {
            $meta['passports'] = $user->customer
                ->passports
                ->map(function ($pp)
                {
                    return $pp->getTransformer()->transform($pp);
                });
        }

        return $this->response->array([
            'token' =>  'legacy',
            'data'  =>  $user->getTransformer()->transform($user),
            'meta'  =>  $meta,
        ]);
    }

    /**
     * Bring a legacy login into the current site.
     */
    public function configure (LegacyConfigurationRequest $request, $uid)
    {
        ini_set('xdebug.max_nesting_level', 200);

        $user = DB::transaction(function () use ($request, $uid)
        {
            $user = $this->users->find($uid);

            if ($profile = $request->get('profile')) {
                $user = $this->api
                    ->be ($user)
                    ->with ($profile)
                    ->put ('/users/'.$user->id);
            }

            if ($studio_params = $request->get('studio')) {
                $studio = $this->api
                    ->be ($user)
                    ->with ($studio_params)
                    ->put ('/studios/'.$user->id);
            }

            if ($location_params = $request->get('location')) {
                $location = $this->api
                    ->be ($user)
                    ->with ($location_params)
                    ->put ('/studios/'.$user->id.'/address');
            }

            $user->legacy()->delete();

            try {

                $this->users->activateByCode($user->activation_code, $request->get('password'));

            } catch (\Dingo\Api\Exception\ResourceException $e) {
                /* they're already activated, let it slide. */
            }

            return $user;
        });

        event(new LegacyLoginConverted ($user));

        return $this->response->item($user, $user->getTransformer());
    }

    /**
     * Activate a new account.
     */
    public function activate (ActivationRequest $request)
    {
        $user = $this->users->activateByCode($request->get('code'), $request->get('password'));

        Notifier::notify ($user->email . ' has activated their account.')
            ->via ('log')
            ->via ('slack');

        return $this->response->noContent();
    }

    /**
     * Query your own profile
     */
    public function profile (Request $request)
    {
        $user = auth()->user();
        //echo $request->include; exit;
        // dd($user);
         $_SESSION['activation_code']='token';
        return $this->response->item($user, $user->getTransformer());
    }
}
