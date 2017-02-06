@extends('layouts.email')

@section('title', $subject)

@section('body')

<p>Hi {{ $name }},</p>

<p>So your card is activated and you’re ready to go?</p>

<h4>Here are a couple of tips to get the most out of your membership:</h4>
<ol>
	<li>Go to the studio schedule site for to see their schedule.</li>
	<li>Present your card upon arrival at the studio for one of their drop in classes.</li>
	<li>Enjoy your class!</li>
	<li>Mark off the class on your attendance card.</li>
</ol>

<h4>New to yoga? Here are some etiquette tips from the experts:</h4>
<ol>
	<li>Arrive at least 15 minutes before your scheduled class. This will give you some time to relax and settle in. Latecomers are disruptive to the teacher and fellow students. Yoga is always practiced bare foot. Remember to remove your shoes before entering the studio.</li>
	<li>Turn off all electronic devices. Cell phones and pagers should not be brought into the studio. </li>
	<li>Yoga studios are scent-free zones. Fragrances and essential oils can become intense in warm and closed areas where people may be engaged in deep breathing. Other students may also have sensitivities or allergies to fragrances.</li>
	<li>Respect your teacher and fellow students. Avoid loud conversations in the studio as other students use this time to relax. Let your teacher know of any injuries or conditions that may affect your practice.</li>
	<li>Don’t skip Savasana (relaxation at the end of each class). The final relaxation and meditation is often the best part of the class! If you must leave early, tell the teacher beforehand, place your mat by the door and sneak out quietly before savasana.</li>
	<li>Clean up your immediate area after class. Please put away all props you may have used before you leave the yoga room.</li>
</ol>

<h4>What to do if you misplace your card:</h4>
<p>Don’t fret! Just go to the LOST CARD tab under in your account and make sure to report your card misplace.</p>

<p>If you have any questions or comments, email us anytime at <a href="mailto:info@passporttoprana.com">info@passporttoprana.com</a>. We are always happy to hear from you!</p>

@stop
