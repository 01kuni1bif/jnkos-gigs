<?php

namespace Tests\Unit;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $listing->user);
        $this->assertEquals($user->id, $listing->user->id);
    }
    /** @test */
public function it_filters_listings_by_tag()
{
    Listing::factory()->create(['tags' => 'php,laravel']);
    Listing::factory()->create(['tags' => 'javascript,react']);

    $filteredListings = Listing::filter(['tag' => 'laravel'])->get();

    $this->assertCount(1, $filteredListings);
    $this->assertEquals('php,laravel', $filteredListings->first()->tags);
}

/** @test */
public function it_filters_listings_by_search()
{
    Listing::factory()->create(['title' => 'Laravel Developer', 'description' => 'Laravel job opportunity']);
    Listing::factory()->create(['title' => 'React Developer', 'description' => 'React job opportunity']);

    $filteredListings = Listing::filter(['search' => 'Laravel'])->get();

    $this->assertCount(1, $filteredListings);
    $this->assertEquals('Laravel Developer', $filteredListings->first()->title);
}

}
