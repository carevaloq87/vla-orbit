@component('mail::message')

<div style="text-align: right">Ref. ID: {{ $args['RefNo'] }}</div>

Hello,

You contacted {{ $args['sendingServiceProvider']['ServiceProviderName'] }} on {{ date("d-m-Y") }} with a question about {{$args['LegalMatter']}}.
They have referred you to another service for more help:

  * __Service Provider__: {{ $args['ServiceProviderName'] }}
  * __Service Name__: {{ $args['ServiceName'] }}
  * __Service Type__: {{ $args['ServiceTypeName'] }}
@if( $args['Location'] != "#" )
  * __Address__: <a href="http://maps.google.com/?q={{ $args['Location'] }}">{{ $args['Location'] }}</a>
@endif
@if( $args['Phone'] != "#" )
  * __Phone Number__: {{ $args['Phone'] }}
@endif
  * <a href="{{ $args['URL'] }}">Website</a>
@if( $args['OpenningHrs'] != "#" )
  * __Opening Hours__: {{ $args['OpenningHrs'] }}
@endif
  * __Service Details__: <div>{!! $args['Description'] !!}</div>

**Once you make contact the service will assess whether they can help you or not.**

*This email was sent by {{ strtoupper(config('app.name')) }} on behalf of {!! $args['sendingServiceProvider']['ServiceProviderName'] !!}. Please do not reply to this email.*

@endcomponent