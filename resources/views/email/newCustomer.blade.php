@component('mail::message')
# Welcome to {{ config('app.name') }}

Dear {{ $customerName }}
Thank you for activating your account. I will like to welcome you to {{ config('app.name') }}.
You have just taken the first step on your path to wealth creation and you have chosen the right partner. Please find
your login credentials below.

Username: {{ $customerNo }}
Password: {{ $customerPassword }}

@component('mail::button', ['url' => $customerDashboardLink ])
Go to Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
