@component('mail::message')
    # Welcome to {{ config('app.name') }}

    Dear {{ $customerName }}
    You have been registered as an admin in {{ config('app.name') }}.
    Please find your login credentials below.

    Password: {{ $customerPassword }}

    @component('mail::button', ['url' => $customerDashboardLink ])
        Go to Dashboard
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
