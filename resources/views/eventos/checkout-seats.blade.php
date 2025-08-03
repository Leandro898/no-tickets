@extends('layouts.app')

@section('content')
  <div
  id="seat-checkout"
  data-slug="{{ $evento->slug }}"
  data-purchase-route="{{ route('eventos.checkout', $evento->slug) }}"
  class="flex flex-col min-h-screen"
></div>

@endsection
