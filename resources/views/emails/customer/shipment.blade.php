@extends('layouts.email')

@section('title', $subject)

@section('body')
<p>Hi {{ $name }},</p>

<p>Just letting you know your card has been shipped and should get to you soon!</p>

<p>Shipping Address: <strong>{{ $address }}</strong>.</p>

<p>Once your card arrives, make sure to add it to your account on the My Cards page in your login area.  Remember, your card can be used for 1 year from date of activation.</p>
@stop
