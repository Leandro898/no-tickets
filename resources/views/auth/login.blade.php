{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <a href="{{ url('/') }}">
                <x-application-logo class="w-16 h-16 fill-current text-gray-500" />
            </a>
        </x-slot>

        {{-- ERRORES MANUALES --}}
        @if ($errors->any())
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div>
                <x-label for="email" value="Email" />
                <x-input id="email"
                         class="block mt-1 w-full"
                         type="email"
                         name="email"
                         :value="old('email')"
                         required
                         autofocus />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-label for="password" value="Password" />
                <x-input id="password"
                         class="block mt-1 w-full"
                         type="password"
                         name="password"
                         required
                         autocomplete="current-password" />
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me"
                           type="checkbox"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           name="remember">
                    <span class="ml-2 text-sm text-gray-600">
                        Remember me
                    </span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900"
                       href="{{ route('password.request') }}">
                        Forgot your password?
                    </a>
                @endif

                <x-button class="ml-4">
                    Log in
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
