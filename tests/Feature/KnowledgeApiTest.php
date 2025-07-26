<?php

use App\Models\Knowledge;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can create knowledge entry', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/knowledge', [
            'title' => 'How to use Laravel Sanctum',
            'content' => 'Laravel Sanctum provides authentication for SPAs and mobile apps...',
            'type' => 'note',
            'is_public' => false,
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'content',
                'type',
                'is_public',
                'created_at',
                'user',
            ],
        ]);

    expect(Knowledge::count())->toBe(1);
});

it('can list user knowledge entries', function () {
    Knowledge::factory()->count(3)->create(['user_id' => $this->user->id]);
    Knowledge::factory()->count(2)->create(); // Other user's knowledge

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/knowledge');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can view specific knowledge entry', function () {
    $knowledge = Knowledge::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/knowledge/{$knowledge->id}");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $knowledge->id,
                'title' => $knowledge->title,
            ],
        ]);
});

it('can update knowledge entry', function () {
    $knowledge = Knowledge::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/knowledge/{$knowledge->id}", [
            'title' => 'Updated Title',
            'content' => $knowledge->content,
        ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'title' => 'Updated Title',
            ],
        ]);
});

it('can delete knowledge entry', function () {
    $knowledge = Knowledge::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/knowledge/{$knowledge->id}");

    $response->assertOk()
        ->assertJson(['message' => 'Knowledge deleted successfully']);

    expect(Knowledge::count())->toBe(0);
});

it('cannot access other users knowledge', function () {
    $otherUser = User::factory()->create();
    $knowledge = Knowledge::factory()->create(['user_id' => $otherUser->id, 'is_public' => false]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/knowledge/{$knowledge->id}");

    $response->assertForbidden();
});

it('can access public knowledge from other users', function () {
    $otherUser = User::factory()->create();
    $knowledge = Knowledge::factory()->create(['user_id' => $otherUser->id, 'is_public' => true]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/knowledge/{$knowledge->id}");

    $response->assertOk();
});

it('can attach tags to knowledge', function () {
    $tag = Tag::factory()->create();

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/knowledge', [
            'title' => 'Tagged Knowledge',
            'content' => 'Some content with tags',
            'tag_ids' => [$tag->id],
        ]);

    $response->assertCreated();

    $knowledge = Knowledge::first();
    expect($knowledge->tags)->toHaveCount(1);
    expect($knowledge->tags->first()->id)->toBe($tag->id);
});

it('can search knowledge', function () {
    Knowledge::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Laravel Testing Guide',
        'content' => 'How to test Laravel applications with Pest',
    ]);

    Knowledge::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Vue.js Components',
        'content' => 'Building reactive components',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/search/knowledge?q=Laravel');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});
