<?php

namespace App\Http\Controllers;

use Carbon;
use DB;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Http\Requests\ManagementRequest;

use App\Http\Requests\Passports\ConfigureRequest;
use App\Http\Requests\Passports\ManageRequest;
use App\Http\Requests\Passports\UndoRequest;
use App\Http\Requests\Passports\ReclaimRequest;
use App\Http\Requests\Passports\AbandonRequest;

use App\Passports\PassportRepository;
use App\Studios\StudioRepository;

use Dingo\Api\Exception\ResourceException;

class PassportController extends Controller
{
    protected $passports;

    public function __construct (PassportRepository $passports)
    {
        $this->passports = $passports;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function create (Request $request)
    {
        $numbers = $request->get('numbers');

        $exists = [];

        foreach ($numbers as $number) {

            if ($this->passports->exists($number)) {
                $exists[] = $number;
            } else {
                $passport = $this->passports->create(['number' => $number]);
                $this->passports->save($passport);
            }
        }

        return $this->response->array($exists);
    }

    /**
     * Activate a passport on the current users account.
     *
     * @param  Request  $request
     * @return Response
     */
    public function activate (Request $request, $ppnum)
    {
        $user = auth()->user();
        
        $pp = $this->passports->findByNumber ($ppnum);

        $this->passports->activate ($user->customer, $pp);

        return $this->response->item ($pp, $pp->getTransformer());
    }

    /**
     * Use a passport at a studio
     *
     * @param  Request  $request
     * @param  int  $sid
     * @param  int  $num
     * @return Response
     */
    public function update (Request $request, $sid, $num, StudioRepository $studios)
    {
        $passport = $this->passports->findByNumber ($num);
        
        $studio = $studios->find ($sid);

        $this->passports->useAtStudio ($passport, $studio);

        return $this->response->item ($studio, $studio->getTransformer());
    }

    /**
     * Reclaim a passport
     *
     * @param  ReclaimRequest  $request
     * @param  int  $number
     * @param  PassportRepository  $passports
     *
     * @return Response
     */
    public function destroy (ReclaimRequest $request, $number)
    {
        $passport = $this->passports->findByNumber ($number);

        $this->passports->reclaim ($passport);

        return $this->response->noContent();
    }

    /**
     * Reclaim a passport
     *
     * @param  AbandonRequest  $request
     * @param  int  $number
     * @param  PassportRepository  $passports
     *
     * @return Response
     */
    public function abandon (AbandonRequest $request, $number)
    {
        $passport = $this->passports->findByNumber ($number);

        $this->passports->abandon ($passport);

        return $this->response->noContent();
    }

    /**
     * Use a passport at a studio
     *
     * @param  Request  $request
     * @param  int  $sid
     * @param  int  $num
     * @return Response
     */
    public function undo (UndoRequest $request, $sid, $num, StudioRepository $studios)
    {
        $passport = $this->passports->findByNumber ($num);
        
        $studio = $studios->find ($sid);

        $this->passports->unUseAtStudio ($passport, $studio);

        return $this->response->item($studio, $studio->getTransformer());
    }


    /**
     * Configure a passport to be good in a city
     * Activate it for a given user, or a new one.
     *
     * @param  int  $id
     * @return Response
     */
    public function configure (ConfigureRequest $request, $num)
    {
        $pp = $this->passports->findByNumber($num);

        if ($pp->isAvailable()) {
            throw new ResourceException('That passport is already configured.');
        }

        $pp->city_id = $request->get('city_id');
        
        ini_set('xdebug.max_nesting_level', 200);
        
        $user = DB::transaction (function () use ($request, $pp)
        {
            $this->passports->save($pp);

            return $this->api
                ->with($request->all())
                ->post('auth/register');
        });

        return $this->response->item($user, $user->getTransformer());
    }

    public function manage (ManageRequest $request, $num)
    {
        $pp = $this->passports->findByNumber($num);

        $this->passports
            ->update($pp, [
                'expires_at' => Carbon::createFromTimestamp($request->get('expires_at')),
                'activated_at' => Carbon::createFromTimestamp($request->get('activated_at')),
            ]);

        return $this->response->noContent();
    }

    public function renew (ManageRequest $request, $num)
    {
        $pp = $this->passports->findByNumber ($num);

        $pp = $this->passports->renew ($pp);

        return $this->response->item ($pp, $pp->getTransformer());
    }
}
