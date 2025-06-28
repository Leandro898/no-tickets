@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
  <div class="max-w-md w-full bg-white shadow-xl rounded-xl p-8">
    <h2 class="text-2xl font-extrabold text-gray-900 mb-6 text-center">
      {{ __('Reset Password') }}
    </h2>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
      @csrf

      <!-- Password Reset Token -->
      <input type="hidden" name="token" value="{{ $request->route('token') }}">

      <!-- Email Address -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">
          {{ __('Email') }}
        </label>
        <input
          id="email"
          name="email"
          type="email"
          value="{{ old('email', $request->email) }}"
          required
          autofocus
          autocomplete="username"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                 focus:outline-none focus:ring-purple-500 focus:border-purple-500"
        >
        @error('email')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <!-- New Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">
          {{ __('Password') }}
        </label>
        <input
          id="password"
          name="password"
          type="password"
          required
          autocomplete="new-password"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                 focus:outline-none focus:ring-purple-500 focus:border-purple-500"
        >
        @error('password')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <!-- Confirm Password -->
      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
          {{ __('Confirm Password') }}
        </label>
        <input
          id="password_confirmation"
          name="password_confirmation"
          type="password"
          required
          autocomplete="new-password"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                 focus:outline-none focus:ring-purple-500 focus:border-purple-500"
        >
        @error('password_confirmation')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <!-- Submit -->
      <div>
        <button
          type="submit"
          class="w-full flex justify-center py-2 px-4 bg-purple-600 hover:bg-purple-700 
                 text-white font-semibold rounded-md transition focus:outline-none focus:ring-2 
                 focus:ring-offset-2 focus:ring-purple-500"
        >
          {{ __('Reset Password') }}
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
