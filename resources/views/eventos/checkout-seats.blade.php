@extends('layouts.app')

@section('content')
  <h1>CHECKOUT-SEATS (slug={{ $evento->slug }})</h1>
  <div
  id="seat-checkout"
  data-slug="{{ $evento->slug }}"
  data-purchase-route="{{ route('eventos.checkout-seats', $evento->slug) }}"
></div>

@endsection
