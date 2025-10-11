@extends('layouts.default')

@section('title', 'Politique de Confidentialité et RGPD - Skiut')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Politique de Confidentialité et RGPD</h1>
                <p class="text-gray-600">Application Skiut - Voyage 2026</p>
                <p class="text-sm text-gray-500 mt-2">Dernière mise à jour : {{ date('d/m/Y') }}</p>
            </div>

            <!-- Introduction -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Introduction</h2>
                <p class="text-gray-700 leading-relaxed">
                    L'application Skiut est développée dans le cadre du voyage ski organisé par l'association étudiante PVDC-Ski'UTC. 
                    Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos données personnelles 
                    conformément au Règlement Général sur la Protection des Données (RGPD).
                </p>
            </div>

            <!-- Responsable du traitement -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Responsable du Traitement</h2>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-gray-700 mb-2"><strong>Responsable :</strong> PVDC-Ski'UTC</p>
                    <p class="text-gray-700 mb-2"><strong>Responsable informatique :</strong> Mathis Delmaere</p>
                    <p class="text-gray-700"><strong>Finalité :</strong> Organisation et animation du voyage</p>
                </div>
            </div>

            <!-- Données collectées -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Données Collectées</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-800 mb-2">Données d'identification</h3>
                        <ul class="text-gray-700 text-sm space-y-1">
                            <li>• Nom et prénom</li>
                            <li>• Adresse e-mail</li>
                            <li>• Numéro de chambre</li>
                            <li>• Informations de profil</li>
                        </ul>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-800 mb-2">Données d'utilisation</h3>
                        <ul class="text-gray-700 text-sm space-y-1">
                            <li>• Activités et défis</li>
                            <li>• Anecdotes partagées</li>
                            <li>• Interactions Skinder</li>
                            <li>• Performances de glisse</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Finalités -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Finalités du Traitement</h2>
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-gray-700 mb-3"><strong>Usage exclusif :</strong> Les données sont utilisées uniquement dans le cadre du voyage ski et pour :</p>
                    <ul class="text-gray-700 space-y-2">
                        <li>Organisation des activités et défis</li>
                        <li>Fonctionnalités sociales de l'application</li>
                        <li>Notifications liées au voyage</li>
                        <li>Classements et performances</li>
                    </ul>
                    <p class="text-red-600 font-semibold mt-4">Aucune utilisation commerciale ou externe</p>
                </div>
            </div>

            <!-- Hébergement -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Hébergement et Sécurité</h2>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-gray-800 mb-2">Localisation des données</h3>
                    <p class="text-gray-700 mb-3">Toutes vos données sont stockées exclusivement sur :</p>
                    <ul class="text-gray-700 space-y-1">
                        <li><strong>Serveurs du SIMDE</strong> (Service Informatique Multimédia Des Étudiants)</li>
                        <li><strong>Serveurs du responsable informatique</strong> (Mathis Delmaere)</li>
                    </ul>
                    <p class="text-gray-700 mt-3">
                        <strong>Sécurité :</strong> Les données sont protégées par des mesures de sécurité techniques et 
                        organisationnelles appropriées pour empêcher tout accès non autorisé.
                    </p>
                </div>
            </div>

            <!-- Durée de conservation -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Durée de Conservation</h2>
                <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-400">
                    <p class="text-gray-700 text-lg">
                        <strong>Suppression automatique :</strong> Toutes vos données seront supprimées 
                        <span class="text-red-600 font-bold">au maximum 1 mois après la fin de l'événement</span>.
                    </p>
                    <p class="text-gray-600 text-sm mt-2">
                        Cette suppression est automatique et définitive, sauf demande contraire de votre part.
                    </p>
                </div>
            </div>

            <!-- Vos droits -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Vos Droits RGPD</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-indigo-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-indigo-800 mb-2">Droits disponibles</h3>
                        <ul class="text-gray-700 space-y-1">
                            <li><strong>Export</strong> de vos données</li>
                            <li><strong>Anonymisation</strong> de votre profil</li>
                            <li><strong>Suppression</strong> complète</li>
                            <li><strong>Modification</strong> des informations</li>
                        </ul>
                    </div>
                    <div class="bg-indigo-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-indigo-800 mb-2">Comment exercer vos droits</h3>
                        <p class="text-gray-700 text-sm mb-2">Directement dans l'application :</p>
                        <ul class="text-gray-700 text-sm space-y-1">
                            <li>• Section "RGPD & Données"</li>
                            <li>• Boutons d'action instantanés</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Contact</h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-700 mb-2">
                        Pour toute question concernant cette politique de confidentialité ou l'exercice de vos droits :
                    </p>
                    <div class="mt-3">
                        <p class="text-gray-700"><strong>Responsable informatique :</strong> Mathis Delmaere</p>
                        <p class="text-gray-700"><strong>Par mail :</strong> skiutc@assos.utc.fr</p>
                    </div>
                </div>
            </div>

            <!-- Modifications -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Modifications</h2>
                <p class="text-gray-700">
                    Cette politique peut être mise à jour pour refléter les changements dans nos pratiques de traitement des données. 
                    Toute modification sera communiquée via l'application.
                </p>
            </div>

            <!-- Footer -->
            <div class="border-t pt-6 text-center">
                <p class="text-gray-600 text-sm">
                    En utilisant l'application Skiut, vous acceptez cette politique de confidentialité.
                </p>
                <p class="text-gray-500 text-xs mt-2">
                    Conforme au RGPD (Règlement UE 2016/679)
                </p>
            </div>

            <!-- Bouton retour -->
            <div class="text-center mt-8">
                <a href="/skiutc/" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    ← Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
