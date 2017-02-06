<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reminders\CreateRequest;
use App\Http\Requests\Reminders\UnsubscribeRequest;
use App\Reminders\ReminderRepository;

use Carbon;


class ReminderController extends Controller
{
    protected $reminders;

    public function __construct (ReminderRepository $reminders)
    {
        $this->reminders = $reminders;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Reminders\CreateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store (CreateRequest $request)
    {
        $params = $request->only(['first_name', 'last_name', 'email', 'note']);

        foreach ($request->get('dates') as $key => $value)
        {
            $this->reminders->createGiftReminder (array_merge($params, [
                'remind_at' => Carbon::parse ($value)->subWeeks(3), // send the reminder 3 weeks before the date they selected
            ]));
        }

        return $this->response->noContent();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show ($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update (Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy (UnsubscribeRequest $request, $email)
    {
        $this->reminders->unsubscribe ($email);

        return $this->response->noContent();
    }
}
