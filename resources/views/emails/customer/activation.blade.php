@extends('layouts.email')

@section('title', $subject)

@section('body')

<p>Hi {{ $name }}</p>

<p>Thanks for creating your Passport to Prana account!  You're one step closer to finding your perfect yoga studio.</p>

<p>Please click <a href="{{ $activation_url }}" alt="Activation Link">here</a> to activate your account.</p>

@stop
