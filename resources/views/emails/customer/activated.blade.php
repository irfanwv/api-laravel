@extends('layouts.email')

@section('title', $subject)

@section('body')

<p>Your card has been activated and is ready to go!</p>

<p>You now have 1 year to explore the great yoga studios in your city.</p>

<p>Membership Expiry Date: <strong>{{ $expiry }}</strong>.</p>

<p>We're excited to have you join our community!</p>

<p>We love seeing our community in action.  Like our Facebook page and share your yoga pictures to be entered in our fun sweepstakes and seasonal contests to win cool yoga swag.</p>

<p>Happy yoga journey!</p>

@stop
