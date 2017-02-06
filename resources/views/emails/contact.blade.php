@extends('layouts.email')

@section('title', $title)

@section('body')
    <h2>You've been contacted through the Passport to Prana website.</h2>

    <p> By  
	    @if(isset($name))
    	<em>{{ $name }}</em> at
    	@endif
    	<em>{{ $email }}</em>
    </p>

    @if(isset($subject))
    <p>Regarding: {{ $subject }}</p>
    @endif

    @if(isset($content))
    <p>{{ $content }}</p>
    
    <!-- this should only be for the maintenance form -->
    @elseif(isset($city) && isset($studio) && isset($number))
    <p>City: {{ $city }}</p>
    <p>Card: {{ $number }}</p>
    @endif
@stop
