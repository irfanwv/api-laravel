@extends('layouts.email')

@section('title', $subject)

@section('body')
<p>Hi {{ $name }}.</p>
<p>Someone has requested that we remind you of your username, it's <strong>{{ $email }}</strong></p>

<p>If you didn't make this request, please contact support.</p>
<p>Thanks</p>
@stop
