@extends('layouts.default')

@section('title', 'Authentification raté')

@section('content')
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="max-w-md w-full bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold text-center text-green-600">Authentification raté...</h1>
            <p class="mt-4 text-center text-gray-600">
                Veuillez réessayer.
            </p>
            <p class="mt-4 text-center text-gray-600">
                L'erreur vient probablement de l'adresse mail que vous avez utilisé
            </p>
        </div>
    </div>
@endsection