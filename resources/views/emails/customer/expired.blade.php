@extends('layouts.email')

@section('title', $subject)

@section('body')

<p>Hi {{ $name }},</p>

<p>Your Passport to Prana membership has expired.</p>

<p>Still looking for that perfect yoga studio? Try out all our participating studios again by renewing your membership for another year.</p>

<p>Simply log into your account and click on the "My Card" tab.</p>

@stop
