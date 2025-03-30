@component('mail::message')
{{ __('organization.invited_title', ['name' => $invitation->organization->name]) }}

@if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::registration()))
{{ __('organization.no_account_instructions') }}

@component('mail::button', ['url' => route('register')])
{{ __('organization.create_account') }}
@endcomponent

{{ __('organization.has_account_instructions') }}

@else
{{ __('organization.accept_invitation_direct') }}
@endif

@component('mail::button', ['url' => $acceptUrl])
{{ __('organization.accept_invitation') }}
@endcomponent

{{ __('organization.unexpected_invitation') }}
@endcomponent
