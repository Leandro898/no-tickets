@extends('layouts.app')

@section('content')
  <div
  id="seat-checkout"
  data-slug="{{ $evento->slug }}"
  data-purchase-route="{{ url("/api/eventos/{$evento->slug}/asientos/purchase") }}"

  class="flex flex-col min-h-screen"
></div>

@endsection
