<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     * Create indexes for frequently used queries.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('member', 'idx_users_member');
            $table->index('admin', 'idx_users_admin');
            $table->index('room_id', 'idx_users_room_id');
            $table->index(['member', 'room_id'], 'idx_users_member_room');
        });

        Schema::table('anecdotes', function (Blueprint $table) {
            $table->index('valid', 'idx_anecdotes_valid');
            $table->index('delete', 'idx_anecdotes_delete');
            $table->index(['valid', 'delete', 'created_at'], 'idx_anecdotes_valid_delete_date');
            $table->index(['room_id', 'valid'], 'idx_anecdotes_room_valid');
        });

        Schema::table('anecdotes_warn', function (Blueprint $table) {
            $table->index('anecdote_id', 'idx_anecdotes_warns_anecdote');
            $table->unique(['user_id', 'anecdote_id'], 'idx_anecdotes_warns_unique');
        });

        Schema::table('challenge_proofs', function (Blueprint $table) {
            $table->index('valid', 'idx_challenge_proofs_valid');
            $table->index('delete', 'idx_challenge_proofs_delete');
            $table->index(['challenge_id', 'room_id'], 'idx_challenge_proofs_challenge_room');
            $table->index(['valid', 'delete'], 'idx_challenge_proofs_valid_delete');
            $table->index(['room_id', 'valid'], 'idx_challenge_proofs_room_valid');
        });

        Schema::table('monoprut', function (Blueprint $table) {
            $table->index('retrieved', 'idx_monoprut_retrieved');
            $table->index(['receiver_room_id', 'retrieved'], 'idx_monoprut_receiver_retrieved');
            $table->index('giver_room_id', 'idx_monoprut_giver_room');
            $table->index('created_at', 'idx_monoprut_created_at');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index('type', 'idx_notifications_type');
            $table->index('display', 'idx_notifications_display');
            $table->index('general', 'idx_notifications_general');
            $table->index(['display', 'type', 'created_at'], 'idx_notifications_display_type_date');
            $table->index('sender_id', 'idx_notifications_sender');
        });

        Schema::table('user_notifications', function (Blueprint $table) {
            $table->index('read', 'idx_user_notifications_read');
            $table->unique(['user_id', 'notification_id'], 'idx_user_notifications_user_notif');
            $table->index(['user_id', 'read'], 'idx_user_notifications_user_read');
        });

        Schema::table('skinder_likes', function (Blueprint $table) {
            $table->index('room_liker_id', 'idx_skinder_likes_liker');
            $table->index('room_liked_id', 'idx_skinder_likes_liked');
            $table->unique(['room_liker_id', 'room_liked_id'], 'idx_skinder_likes_unique');
        });

        Schema::table('performance_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'session_date'], 'idx_performance_user_date');
            $table->index(['user_id', 'created_at'], 'idx_performance_user_created');
        });

        Schema::table('permanences', function (Blueprint $table) {
            $table->index('responsible_user_id', 'idx_permanences_responsible');
            $table->index('status', 'idx_permanences_status');
            $table->index(['start_datetime', 'end_datetime'], 'idx_permanences_period');
            $table->index(['responsible_user_id', 'start_datetime'], 'idx_permanences_resp_start');
        });

        Schema::table('room_tours', function (Blueprint $table) {
            $table->index('tour_date', 'idx_room_tours_date');
            $table->index('is_active', 'idx_room_tours_active');
            $table->index(['tour_date', 'is_active'], 'idx_room_tours_date_active');
        });

        Schema::table('tour_binomes', function (Blueprint $table) {
            $table->index('room_tour_id', 'idx_tour_binomes_tour');
            $table->index('member_1_id', 'idx_tour_binomes_member1');
            $table->index('member_2_id', 'idx_tour_binomes_member2');
        });

        Schema::table('room_tour_visits', function (Blueprint $table) {
            $table->index('tour_binome_id', 'idx_room_tour_visits_binome');
            $table->index('room_id', 'idx_room_tour_visits_room');
            $table->index('visited', 'idx_room_tour_visits_visited');
            $table->index(['tour_binome_id', 'visit_order'], 'idx_room_tour_visits_binome_order');
            $table->index(['tour_binome_id', 'visited'], 'idx_room_tour_visits_binome_visited');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->index('date', 'idx_activities_date');
            $table->index(['date', 'startTime'], 'idx_activities_date_start');
        });

        Schema::table('push_tokens', function (Blueprint $table) {
            $table->index('user_id', 'idx_push_tokens_user');
            $table->index(['user_id', 'token'], 'idx_push_tokens_user_token');
        });

        Schema::table('transports', function (Blueprint $table) {
            $table->index('type', 'idx_transports_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_member');
            $table->dropIndex('idx_users_admin');
            $table->dropIndex('idx_users_room_id');
            $table->dropIndex('idx_users_member_room');
        });

        Schema::table('anecdotes', function (Blueprint $table) {
            $table->dropIndex('idx_anecdotes_valid');
            $table->dropIndex('idx_anecdotes_delete');
            $table->dropIndex('idx_anecdotes_valid_delete_date');
            $table->dropIndex('idx_anecdotes_room_valid');
        });

        Schema::table('anecdotes_warns', function (Blueprint $table) {
            $table->dropIndex('idx_anecdotes_warns_anecdote');
            $table->dropUnique('idx_anecdotes_warns_unique');
        });

        Schema::table('challenge_proofs', function (Blueprint $table) {
            $table->dropIndex('idx_challenge_proofs_valid');
            $table->dropIndex('idx_challenge_proofs_delete');
            $table->dropIndex('idx_challenge_proofs_challenge_room');
            $table->dropIndex('idx_challenge_proofs_valid_delete');
            $table->dropIndex('idx_challenge_proofs_room_valid');
        });

        Schema::table('monoprut', function (Blueprint $table) {
            $table->dropIndex('idx_monoprut_retrieved');
            $table->dropIndex('idx_monoprut_receiver_retrieved');
            $table->dropIndex('idx_monoprut_giver_room');
            $table->dropIndex('idx_monoprut_created_at');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_type');
            $table->dropIndex('idx_notifications_display');
            $table->dropIndex('idx_notifications_general');
            $table->dropIndex('idx_notifications_display_type_date');
            $table->dropIndex('idx_notifications_sender');
        });

        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropIndex('idx_user_notifications_read');
            $table->dropUnique('idx_user_notifications_user_notif');
            $table->dropIndex('idx_user_notifications_user_read');
        });

        Schema::table('skinder_likes', function (Blueprint $table) {
            $table->dropIndex('idx_skinder_likes_liker');
            $table->dropIndex('idx_skinder_likes_liked');
            $table->dropUnique('idx_skinder_likes_unique');
        });

        Schema::table('performance_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_performance_user_date');
            $table->dropIndex('idx_performance_user_created');
        });

        Schema::table('permanences', function (Blueprint $table) {
            $table->dropIndex('idx_permanences_responsible');
            $table->dropIndex('idx_permanences_status');
            $table->dropIndex('idx_permanences_period');
            $table->dropIndex('idx_permanences_resp_start');
        });

        Schema::table('room_tours', function (Blueprint $table) {
            $table->dropIndex('idx_room_tours_date');
            $table->dropIndex('idx_room_tours_active');
            $table->dropIndex('idx_room_tours_date_active');
        });

        Schema::table('tour_binomes', function (Blueprint $table) {
            $table->dropIndex('idx_tour_binomes_tour');
            $table->dropIndex('idx_tour_binomes_member1');
            $table->dropIndex('idx_tour_binomes_member2');
        });

        Schema::table('room_tour_visits', function (Blueprint $table) {
            $table->dropIndex('idx_room_tour_visits_binome');
            $table->dropIndex('idx_room_tour_visits_room');
            $table->dropIndex('idx_room_tour_visits_visited');
            $table->dropIndex('idx_room_tour_visits_binome_order');
            $table->dropIndex('idx_room_tour_visits_binome_visited');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex('idx_activities_date');
            $table->dropIndex('idx_activities_date_start');
        });

        Schema::table('push_tokens', function (Blueprint $table) {
            $table->dropIndex('idx_push_tokens_user');
            $table->dropIndex('idx_push_tokens_user_token');
        });

        Schema::table('transports', function (Blueprint $table) {
            $table->dropIndex('idx_transports_type');
        });
    }
};
