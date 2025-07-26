<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\ChallengeProof;
use App\Models\Anecdote;
use App\Models\Notification;
use App\Models\PushToken;
use App\Models\Challenge;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private function getToken($isAdmin = false)
    {
        $user = User::factory()->create(['admin' => $isAdmin]);
        $payload = [
            'key' => $user->id,
            'exp' => time() + 3600,
        ];
        $privateKey = Config::get('services.crypt.private');
        return JWT::encode($payload, $privateKey, 'RS256');
    }

    public function test_get_admin_authenticated_as_admin()
    {
        $token = $this->getToken(true);
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/admin');
        $response->assertExactJson(['success' => true, 'message' => 'Vous êtes admin.']);
    }

    public function test_get_admin_authenticated_as_non_admin()
    {
        $token = $this->getToken(false);
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/admin');
        $response->assertExactJson(['success' => false, 'message' => 'Vous n\'êtes pas admin.']);
    }

    // Tests pour getAdminChallenges
    public function test_get_admin_challenges_as_admin()
    {
        $token = $this->getToken(true);
        
        // Créer les données nécessaires avec les relations
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $challenge = Challenge::factory()->create();
        
        ChallengeProof::factory()->create([
            'delete' => false, 
            'valid' => false,
            'user_id' => $user->id,
            'room_id' => $room->id,
            'challenge_id' => $challenge->id
        ]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getAdminChallenges');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'valid', 'delete']
                     ]
                 ]);
    }

    public function test_get_admin_challenges_as_non_admin()
    {
        $token = $this->getToken(false);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getAdminChallenges');
        
        $response->assertStatus(403);
    }

    public function test_get_admin_challenges_with_filter_pending()
    {
        $token = $this->getToken(true);
        
        // Créer les données nécessaires avec les relations
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $challenge1 = Challenge::factory()->create();
        $challenge2 = Challenge::factory()->create();
        
        // Create a pending challenge (valid = false)
        ChallengeProof::factory()->create([
            'delete' => false, 
            'valid' => false, // This should be in the filtered results
            'user_id' => $user->id,
            'room_id' => $room->id,
            'challenge_id' => $challenge1->id
        ]);
        
        // Create a valid challenge (valid = true)
        ChallengeProof::factory()->create([
            'delete' => false, 
            'valid' => true, // This should NOT be in the filtered results
            'user_id' => $user->id,
            'room_id' => $room->id,
            'challenge_id' => $challenge2->id
        ]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getAdminChallenges?filter=pending');
        
        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Ensure we have at least one result
        $this->assertNotEmpty($data, 'No pending challenges found in the response');
        
        // Check that all returned challenges have valid = false (pending)
        foreach ($data as $challenge) {
            $this->assertEquals(false, $challenge['valid'], 'Found a non-pending challenge in pending filter results');
        }
    }

    // Tests pour getChallengeDetails
    public function test_get_challenge_details_as_admin()
    {
        $token = $this->getToken(true);
        
        // Créer les données nécessaires avec les relations
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $challenge = Challenge::factory()->create();
        
        $challengeProof = ChallengeProof::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'challenge_id' => $challenge->id
        ]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/getChallengeDetails/{$challengeProof->id}");
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id'],
                     'imagePath'
                 ]);
    }

    public function test_get_challenge_details_as_non_admin()
    {
        $token = $this->getToken(false);
        
        // Créer les données nécessaires avec les relations
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $challenge = Challenge::factory()->create();
        
        $challengeProof = ChallengeProof::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'challenge_id' => $challenge->id
        ]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/getChallengeDetails/{$challengeProof->id}");
        
        $response->assertStatus(403);
    }

    // Tests pour updateChallengeStatus
    public function test_update_challenge_status_as_admin()
    {
        $token = $this->getToken(true);
        
        // Créer les données nécessaires avec les relations
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $challenge = Challenge::factory()->create();
        
        $challengeProof = ChallengeProof::factory()->create([
            'valid' => false, 
            'delete' => false,
            'user_id' => $user->id,
            'room_id' => $room->id,
            'challenge_id' => $challenge->id
        ]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson("/api/updateChallengeStatus/{$challengeProof->id}/1/0");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Challenge validé avec succès'
                 ]);
        
        $this->assertDatabaseHas('challenge_proofs', [
            'id' => $challengeProof->id,
            'valid' => true,
            'delete' => false
        ]);
    }

    public function test_update_challenge_status_as_non_admin()
    {
        $token = $this->getToken(false);
        
        // Créer les données nécessaires avec les relations
        $user = User::factory()->create();
        $room = Room::factory()->create();
        $challenge = Challenge::factory()->create();
        
        $challengeProof = ChallengeProof::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'challenge_id' => $challenge->id
        ]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson("/api/updateChallengeStatus/{$challengeProof->id}/1/0");
        
        $response->assertStatus(403);
    }

    // Tests pour getAdminAnecdotes
    public function test_get_admin_anecdotes_as_admin()
    {
        $token = $this->getToken(true);
        
        // Créer un utilisateur pour l'anecdote
        $user = User::factory()->create();
        
        Anecdote::factory()->create([
            'delete' => false,
            'user_id' => $user->id
        ]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getAdminAnecdotes');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'valid', 'delete']
                     ]
                 ]);
    }

    public function test_get_admin_anecdotes_as_non_admin()
    {
        $token = $this->getToken(false);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getAdminAnecdotes');
        
        $response->assertStatus(403);
    }

    // Tests pour getAnecdoteDetails
    public function test_get_anecdote_details_as_admin()
    {
        $token = $this->getToken(true);
        
        // Créer un utilisateur pour l'anecdote
        $user = User::factory()->create();
        
        $anecdote = Anecdote::factory()->create([
            'user_id' => $user->id
        ]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/getAnecdoteDetails/{$anecdote->id}");
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id'],
                     'nbLikes',
                     'nbWarns'
                 ]);
    }

    public function test_get_anecdote_details_as_non_admin()
    {
        $token = $this->getToken(false);
        
        // Créer un utilisateur pour l'anecdote
        $user = User::factory()->create();
        
        $anecdote = Anecdote::factory()->create([
            'user_id' => $user->id
        ]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/getAnecdoteDetails/{$anecdote->id}");
        
        $response->assertStatus(403);
    }

    // Tests pour updateAnecdoteStatus
    public function test_update_anecdote_status_as_admin()
    {
        $token = $this->getToken(true);
        
        // Créer un utilisateur pour l'anecdote
        $user = User::factory()->create();
        
        $anecdote = Anecdote::factory()->create([
            'valid' => false,
            'user_id' => $user->id
        ]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson("/api/updateAnecdoteStatus/{$anecdote->id}/1");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Anecdote validée avec succès.'
                 ]);
        
        $this->assertDatabaseHas('anecdotes', [
            'id' => $anecdote->id,
            'valid' => true
        ]);
    }

    public function test_update_anecdote_status_as_non_admin()
    {
        $token = $this->getToken(false);
        
        // Créer un utilisateur pour l'anecdote
        $user = User::factory()->create();
        
        $anecdote = Anecdote::factory()->create([
            'user_id' => $user->id
        ]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson("/api/updateAnecdoteStatus/{$anecdote->id}/1");
        
        $response->assertStatus(403);
    }

    // Tests pour getAdminNotifications
    public function test_get_admin_notifications_as_admin()
    {
        $token = $this->getToken(true);
        Notification::factory()->create();
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getAdminNotifications');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'title', 'description']
                     ]
                 ]);
    }

    public function test_get_admin_notifications_as_non_admin()
    {
        $token = $this->getToken(false);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getAdminNotifications');
        
        $response->assertStatus(403);
    }

    // Tests pour getNotificationDetails
    public function test_get_notification_details_as_admin()
    {
        $token = $this->getToken(true);
        $notification = Notification::factory()->create();
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/getNotificationDetails/{$notification->id}");
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id']
                 ]);
    }

    public function test_get_notification_details_as_non_admin()
    {
        $token = $this->getToken(false);
        $notification = Notification::factory()->create();
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/getNotificationDetails/{$notification->id}");
        
        $response->assertStatus(403);
    }

    // Tests pour deleteNotification
    public function test_delete_notification_as_admin()
    {
        $token = $this->getToken(true);
        $notification = Notification::factory()->create(['delete' => false]);
        
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson("/api/deleteNotification/{$notification->id}/1");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Notification supprimée avec succès.'
                 ]);
        
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'delete' => true
        ]);
    }

    public function test_delete_notification_as_non_admin()
    {
        $token = $this->getToken(false);
        $notification = Notification::factory()->create();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson("/api/deleteNotification/{$notification->id}/1");
        
        $response->assertStatus(403);
    }

    // Tests pour sendNotificationToAll
    public function test_send_notification_to_all_as_admin()
    {
        $token = $this->getToken(true);
        
        // Créer un token push valide (format Expo)
        PushToken::factory()->create(['token' => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]']);
        
        // Mock du service ExpoPushService pour éviter l'erreur de token invalide
        $this->mock(\App\Services\ExpoPushService::class, function ($mock) {
            $mock->shouldReceive('sendNotification')
                 ->once()
                 ->andReturn(true);
        });
        
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson('/api/sendNotification', [
                             'titre' => 'Test Title',
                             'texte' => 'Test Body'
                         ]);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Notification envoyée à tous les utilisateurs !'
                 ]);
        
        $this->assertDatabaseHas('notifications', [
            'title' => 'Test Title',
            'description' => 'Test Body',
            'general' => true
        ]);
    }

    public function test_send_notification_to_all_as_non_admin()
    {
        $token = $this->getToken(false);
        
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson('/api/sendNotification', [
                             'titre' => 'Test Title',
                             'texte' => 'Test Body'
                         ]);
        
        $response->assertStatus(403);
    }

    // Tests pour sendIndividualNotification
    public function test_send_individual_notification_as_admin()
    {
        $token = $this->getToken(true);
        $user = User::factory()->create();
                
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson("/api/sendIndividualNotification/{$user->id}", [
                             'title' => 'Individual Test',
                             'texte' => 'Individual Body'
                         ]);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Notification sent to user.'
                 ]);
    }

    public function test_send_individual_notification_as_non_admin()
    {
        $token = $this->getToken(false);
        $user = User::factory()->create();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson("/api/sendIndividualNotification/{$user->id}", [
                             'title' => 'Individual Test',
                             'texte' => 'Individual Body'
                         ]);
        
        $response->assertStatus(403);
    }

    // Tests d'erreur pour les cas edge
    public function test_get_challenge_details_not_found()
    {
        $token = $this->getToken(true);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getChallengeDetails/999999');
        
        $response->assertStatus(500)
                 ->assertJson(['success' => false]);
    }

    public function test_get_anecdote_details_not_found()
    {
        $token = $this->getToken(true);
        
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/getAnecdoteDetails/999999');
        
        $response->assertStatus(500)
                 ->assertJson(['success' => false]);
    }
}