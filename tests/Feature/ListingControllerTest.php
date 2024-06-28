<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ListingControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shows_all_listings()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('listings.index');
    }

   /** @test */
   public function it_shows_a_single_listing()
   {
       // Create a user (assuming listings belong to users)
       $user = User::factory()->create();

       // Create a listing
       $listing = Listing::factory()->create([
           'user_id' => $user->id, // Associate the listing with the user
       ]);

       // Act as the authenticated user
       $this->actingAs($user);

       // Perform GET request to show the listing
       $response = $this->get(route('listings.show', ['listing' => $listing]));

       // Assert response status and view
       $response->assertStatus(200);
       $response->assertViewIs('listings.show');
       $response->assertViewHas('listing', function ($viewListing) use ($listing) {
           return $viewListing->id === $listing->id;
       });
   }

    /** @test */
    public function it_shows_the_create_form()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/listings/create');

        $response->assertStatus(200);
        $response->assertViewIs('listings.create');
    }

    /** @test */
    public function it_stores_a_new_listing()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('public');
        $file = UploadedFile::fake()->image('logo.jpg');

        $formData = [
            'title' => 'Test Listing',
            'company' => 'Test Company',
            'location' => 'Test Location',
            'website' => 'https://test.com',
            'email' => 'test@example.com',
            'tags' => 'test,listing',
            'description' => 'Test description',
            'logo' => $file,
        ];

        $response = $this->post('/listings', $formData);

        $response->assertRedirect('/');
        $response->assertSessionHas('message', 'Listing created successfully!');

        $this->assertDatabaseHas('listings', [
            'title' => 'Test Listing',
            'company' => 'Test Company',
        ]);

        Storage::disk('public')->assertExists('logos/' . $file->hashName());
    }

    /** @test */
    public function it_shows_the_edit_form()
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get('/listings/' . $listing->id . '/edit');

        $response->assertStatus(200);
        $response->assertViewIs('listings.edit');
        $response->assertViewHas('listing', $listing);
    }

    /** @test */
    public function it_updates_a_listing()
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Storage::fake('public');
        $file = UploadedFile::fake()->image('logo.jpg');

        $formData = [
            'title' => 'Updated Listing',
            'company' => 'Updated Company',
            'location' => 'Updated Location',
            'website' => 'https://updated.com',
            'email' => 'updated@example.com',
            'tags' => 'updated,listing',
            'description' => 'Updated description',
            'logo' => $file,
        ];

        $response = $this->put('/listings/' . $listing->id, $formData);

        $response->assertRedirect('/');
        $response->assertSessionHas('message', 'Listing updated successfully!');

        $this->assertDatabaseHas('listings', [
            'title' => 'Updated Listing',
            'company' => 'Updated Company',
        ]);

        Storage::disk('public')->assertExists('logos/' . $file->hashName());
    }

    /** @test */
    public function it_deletes_a_listing()
    {
        // Create a user (assuming listings belong to users)
        $user = User::factory()->create();
        
        // Create a listing associated with the user
        $listing = Listing::factory()->create(['user_id' => $user->id]);

        // Authenticate as the user
        $this->actingAs($user);

        // Fake storage and upload a file
        Storage::fake('public');
        $file = UploadedFile::fake()->image('logo.jpg');
        $listing->update(['logo' => $file->store('logos', 'public')]);

        // Perform DELETE request to delete the listing
        $response = $this->delete(route('listings.destroy', $listing));

        // Assert response
        $response->assertRedirect('/');
        $response->assertSessionHas('message', 'Listing deleted successfully');

        // Assert deletion
        $this->assertDeleted($listing);
        
        // Assert file deletion from storage
        Storage::disk('public')->assertMissing('logos/' . $file->hashName());
    }

    /** @test */
    public function it_shows_manage_listings()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/listings/manage');

        $response->assertStatus(200);
        $response->assertViewIs('listings.manage');
    }
}
