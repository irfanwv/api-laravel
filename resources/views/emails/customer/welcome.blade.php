@extends('layouts.email')

@section('title', $subject)

@section('body')

<p>Hi {{ $name }}</p>

<p>Thanks for creating your Passport to Prana account!  You're one step closer to finding your perfect yoga studio.</p>

<div class="comment">
	Your login information is: <br>
	Username : {{ $email }} <br>
	Password : As you created it.
</div>
@stop
