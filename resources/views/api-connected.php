@extends('layouts.default')

@section('title', 'Authentification réussie')

@section('content')
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="max-w-md w-full bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold text-center text-green-600">Authentification réussie !</h1>
            <p class="mt-4 text-center text-gray-600">
                Vous pouvez retourner dans l'app !
            </p>
            <div class="mt-6 text-center">
                <form id="logout-form" action="{{ route('auth.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <a href="#" class="text-blue-500 block hover:text-blue-700" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Se déconnecter
                </a>
            </div>
        </div>
    </div>
@endsection