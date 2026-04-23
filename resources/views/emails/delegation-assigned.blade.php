@component('mail::message')
# {{ __('mail.delegation_assigned.heading') }}

{{ __('mail.delegation_assigned.body', [
    'delegator' => $delegation->delegator?->name ?? '—',
    'task' => $delegation->task?->title ?? '—',
]) }}

@component('mail::panel')
**{{ __('mail.delegation_assigned.goal') }}:** {{ $delegation->goal }}

**{{ __('mail.delegation_assigned.deadline') }}:** {{ $delegation->deadline?->toDayDateTimeString() ?? '—' }}

**{{ __('mail.delegation_assigned.scope') }}:** {{ ucfirst($delegation->decision_scope) }}

@if($delegation->resources)
**{{ __('mail.delegation_assigned.resources') }}:** {{ $delegation->resources }}
@endif
@endcomponent

@component('mail::button', ['url' => $acceptUrl, 'color' => 'success'])
{{ __('mail.delegation_assigned.accept') }}
@endcomponent

@component('mail::button', ['url' => $declineUrl, 'color' => 'error'])
{{ __('mail.delegation_assigned.decline') }}
@endcomponent

{{ __('mail.delegation_assigned.or_view') }} [{{ __('mail.delegation_assigned.inbox_link') }}]({{ $inboxUrl }}).

{{ __('mail.delegation_assigned.signoff') }},
{{ config('app.name') }}
@endcomponent
