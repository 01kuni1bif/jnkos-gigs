<?php

namespace Tests\Unit;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_have_many_listings()
    {
        $user = User::factory()->create();
        $listing1 = Listing::factory()->create(['user_id' => $user->id]);
        $listing2 = Listing::factory()->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->listings);
        $this->assertTrue($user->listings->contains($listing1));
        $this->assertTrue($user->listings->contains($listing2));
    }

    /** @test */
    public function it_can_access_the_listings_relationship()
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Listing::class, $user->listings->first());
    }
}
