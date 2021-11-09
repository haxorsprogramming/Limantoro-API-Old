@component('mail::message')

<hr style="widht:100px; color:black;"></hr>
<h1>Confirm Your Account</h1>
<hr style="widht:100px; color:black;"></hr>
<p>Welcome to Enerzy. Please verify your email address to confirm your account by clicking below:</p>


@component('mail::button', ['url' => config('app.main_domain')."/users/register/verify/".$email_token])
Verify Email
@endcomponent


Or copy this link:
<br>
{{config('app.main_domain')."/users/register/verify/".$email_token}}



@component('mail::button', ['url' => "/verifyemail/".$email_token])
Verify Email
@endcomponent


Or copy this link:
<br>
{{url('/verifyemail/'.$email_token)}}
<br>
Your Enerzy Team,
<br>
enerzy.id
@endcomponent
