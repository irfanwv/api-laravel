@extends('layouts.email')

@section('title', $title)

@section('body')
<div class="comment">
  <p>Hi {{ $name }}!</p>
</div>
<div class="comment">
  <p>Just a friendly reminder from Passport to Prana about an upcoming special occasion on your calendar.</p>
  <p>Order your card today so it gets to you (or the receiver) in time for the special day. Please allow for up to 10 days for delivery.</p>
</div>
<div class="comment">
  <ul>
    Why Passport to Prana is the perfect gift:
    <li>It’s affordable. For only $30, the receiver has access to the best studios in your city for a full year.</li>
    <li>It’s convenient. You can choose to mail the card directly to the receiver or to your doorstep.</li>
    <li>It’s flexible. The receiver has a full year to use the membership from the date they choose to activate the card.</li>
    <li>It’s great to give the gift of yoga!</li>
  </ul>
</div>
@stop
