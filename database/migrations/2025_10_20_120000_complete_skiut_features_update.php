<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * Cette migration a été divisée en plusieurs migrations plus simples :
     * - 2025_10_21_100000_create_user_notifications_table.php
     * - 2025_10_21_100001_create_performance_sessions_table.php
     * - 2025_10_21_100002_create_permanences_table.php
     * - 2025_10_21_100003_create_room_tours_table.php
     * - 2025_10_21_100004_create_tour_binomes_table.php
     * - 2025_10_21_100005_create_room_tour_visits_table.php
     *
     * Les colonnes ont été intégrées directement dans les migrations de création des tables :
     * - users : colonne 'member' dans create_users_table.php
     * - notifications : colonnes ajoutées dans create_notifications_table.php
     * - challenge_proofs : colonne 'media_type' dans create_challenge_proofs_table.php
     * - users_performances : colonnes ajoutées dans create_users_performances_table.php
     * - rooms : colonnes ajoutées dans create_rooms_table.php
     */
    public function up(): void
    {
        // Migration vide - toutes les modifications ont été déplacées vers des migrations individuelles
    }

    public function down(): void
    {
        // Migration vide
    }
};
