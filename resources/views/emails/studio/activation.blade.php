@extends('layouts.email')

@section('title', $subject)

@section('body')
<p>Please activate your account by clicking <a href="{{ $activation_url }}">here</a>.</p>
<p>You'll have to create a password on this page to continue.</p>
@stop

