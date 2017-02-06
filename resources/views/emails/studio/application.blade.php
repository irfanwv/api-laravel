@extends('layouts.email')

@section('title', $subject)

@section('body')
<div class="comment">
  <p>{{ $studio_name }} has applied to the program.</p>
</div>

<div class="comment">
  <p>Market: {{ $market }}</p>
  <p>Studio Name: {{ $studio_name }}</p>
  <p>Owner Name: {{ $first_name }} {{ $last_name }}</p>
  <p>Title: {{ $title }}</p>
  <p>Email : {{ $email }}</p>
  <p>Phone : {{ $phone }}</p>
  <p>Website : <a href="{{ $website }}">{{ $website }}</a></p>
  <p>Comments : {{ $comments }}</p>
</div>
@stop
