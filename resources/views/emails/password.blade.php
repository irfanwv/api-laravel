@extends('layouts.email')

@section('title', 'Your Password Reset')

@section('body')

<p>
	Click here to reset your password:
	
	<a href="http://{{ env('FRONT_DOMAIN') }}/password/reset?email={{ $user->email }}&token={{ $token }}" 
		alt="Password Reset">
		https://{{ env('FRONT_DOMAIN') }}/password/reset
	</a>

</p>

@stop

