@extends('layouts.email')

@section('title', $subject)

@section('body')
<p>Hi {{ $name }}</p>
<p>Welcome to the new Passport to Prana!</p>
<p>Thanks for converting your login to out new system.</p>
<p>Going forward your login will be the following:</p>
<p>Username: <em>{{ $email }}</em>
<p>Password: as selected by you during the conversion process.</p>
<p>
  If you're having forgotten your password or are having difficulty logging in, please try resetting your password <a href="/password">here</a>.
  If the problem persists, please contact us at <a href="mailto:info@passporttoprana.com">info@passporttoprana.com</a>.
</p>
@stop
