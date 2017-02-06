@extends('layouts.email')

@section('title', $subject)

@section('body')

<p>Hi {{ $name }},</p>

<p>Your card will expire on <strong>{{ $expiry }}</strong></p>

<p>Did the year fly by and you still need a bit more time to use your card?  You can now extend your expiry date by one, three, or six months.</p>

<p>Adding time to your card does not clear your attendance. It allows you to continue to use your card for the remaining time plus the months added to your expiry date.</p>

<p>Use our ADD TIME feature on our site under the MY CARD tab.</p>

@stop
