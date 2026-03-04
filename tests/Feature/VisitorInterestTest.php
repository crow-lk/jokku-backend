<?php

namespace Tests\Feature;

use App\Filament\Resources\VisitorInterests\Pages\CreateVisitorInterest;
use App\Models\User;
use App\Models\VisitorInterest;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VisitorInterestTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_visitor_interest_from_public_api(): void
    {
        $payload = [
            'interest_type' => VisitorInterest::TYPE_CUSTOMER_IDEA,
            'name' => 'Nimal Perera',
            'email' => 'nimal@example.com',
            'message' => 'Would love to see a prepaid savings feature.',
        ];

        $response = $this->postJson('/api/visitor-interests', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Nimal Perera')
            ->assertJsonPath('data.status', VisitorInterest::STATUS_NEW)
            ->assertJsonPath('data.source', VisitorInterest::SOURCE_WEB);

        $this->assertDatabaseHas('visitor_interests', [
            'name' => 'Nimal Perera',
            'email' => 'nimal@example.com',
            'interest_type' => VisitorInterest::TYPE_CUSTOMER_IDEA,
            'status' => VisitorInterest::STATUS_NEW,
            'source' => VisitorInterest::SOURCE_WEB,
        ]);
    }

    public function test_admin_can_create_visitor_interest_in_filament(): void
    {
        $user = User::factory()->create();

        Filament::setCurrentPanel('admin');

        Livewire::actingAs($user)
            ->test(CreateVisitorInterest::class)
            ->fillForm([
                'interest_type' => VisitorInterest::TYPE_INVESTOR,
                'name' => 'Sanduni Silva',
                'email' => 'sanduni@example.com',
                'message' => 'Interested in discussing a seed round.',
                'status' => VisitorInterest::STATUS_NEW,
                'source' => VisitorInterest::SOURCE_CALL_CENTER,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('visitor_interests', [
            'name' => 'Sanduni Silva',
            'email' => 'sanduni@example.com',
            'interest_type' => VisitorInterest::TYPE_INVESTOR,
            'status' => VisitorInterest::STATUS_NEW,
            'source' => VisitorInterest::SOURCE_CALL_CENTER,
            'created_by_user_id' => $user->id,
        ]);
    }
}
