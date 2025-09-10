<x-app-layout>
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg mx-auto text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">¡Registro Exitoso!</h2>
        <p class="text-gray-600 mb-2">Tu invitación para el evento **{{ session('evento_nombre') }}** ha sido creada.</p>
        <p class="text-gray-600">Revisa tu email. Hemos enviado todos los detalles a tu correo.</p>
        <a href="/" class="mt-6 inline-block py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-700 hover:bg-purple-800 focus:outline-none">Volver a la página principal</a>
    </div>
</x-app-layout>