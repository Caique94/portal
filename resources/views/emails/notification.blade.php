@component('mail::message')
# {{ $notification->title }}

{{ $notification->message }}

@if($notification->data && isset($notification->data['cliente']))
**Cliente:** {{ $notification->data['cliente'] }}
@endif

@if($notification->data && isset($notification->data['valor']))
**Valor:** R$ {{ number_format($notification->data['valor'], 2, ',', '.') }}
@endif

@if($notification->action_url)
@component('mail::button', ['url' => $notification->action_url])
Ver Detalhes
@endcomponent
@endif

---

**Tipo de Notificação:** {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
**Data:** {{ $notification->created_at->format('d/m/Y H:i') }}

Obrigado,
Portal Personalitec
@endcomponent
