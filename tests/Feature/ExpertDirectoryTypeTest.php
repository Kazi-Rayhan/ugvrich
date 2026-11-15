<?php

namespace Tests\Feature;

use App\Models\Expert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpertDirectoryTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_directories_separate_types_and_keep_search_and_visibility(): void
    {
        $expert = Expert::create(['name' => 'Unique Faculty', 'slug' => 'unique-faculty', 'is_active' => true]);
        $admin = Expert::create(['name' => 'Unique Administrator', 'slug' => 'unique-administrator', 'type' => 'administration', 'is_active' => true]);
        $hidden = Expert::create(['name' => 'Hidden Administrator', 'slug' => 'hidden-administrator', 'type' => 'administration', 'is_active' => false]);

        foreach (['', '/en'] as $prefix) {
            $this->get($prefix.'/experts')->assertOk()->assertViewHas('experts', fn ($rows) => $rows->contains($expert) && ! $rows->contains($admin));
            $this->get($prefix.'/administrations')->assertOk()->assertViewHas('experts', fn ($rows) => $rows->contains($admin) && ! $rows->contains($expert) && ! $rows->contains($hidden));
            $this->get($prefix.'/administrations?q=nomatch')->assertOk()->assertViewHas('experts', fn ($rows) => $rows->isEmpty());
        }

        $expert->update(['type' => 'administration']);
        $this->get('/experts')->assertViewHas('experts', fn ($rows) => ! $rows->contains($expert));
        $this->get('/administrations')->assertViewHas('experts', fn ($rows) => $rows->contains($expert));
        $this->get('/experts/'.$admin->slug)->assertOk();
        $this->get('/experts/'.$hidden->slug)->assertNotFound();
    }
}
