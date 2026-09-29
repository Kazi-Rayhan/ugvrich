<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The filter box above the admin menu.
 *
 * It filters the menu in the browser by reading Filament's own markup, so what
 * is worth guarding here is that the box is rendered into the sidebar and that
 * the classes it filters on are still the ones Filament emits — those are what
 * a Filament upgrade could quietly change.
 */
class SidebarSearchTest extends TestCase
{
    use RefreshDatabase;

    private function sidebar(): string
    {
        return $this->actingAs(User::factory()->create())->get('/admin')->getContent();
    }

    public function test_the_filter_box_is_rendered_in_the_sidebar(): void
    {
        $html = $this->sidebar();

        $this->assertStringContainsString('rich-sidebar-search', $html);
        $this->assertStringContainsString('richSidebarSearch()', $html);

        // Inside the navigation, and above the entries it filters.
        $this->assertLessThan(
            strpos($html, 'fi-sidebar-item-label'),
            strpos($html, 'rich-sidebar-search'),
        );
    }

    public function test_the_markup_it_filters_on_is_still_there(): void
    {
        $html = $this->sidebar();

        foreach (['fi-sidebar ', 'fi-sidebar-group', 'fi-sidebar-item', 'fi-sidebar-item-label', 'fi-sidebar-item-btn'] as $hook) {
            $this->assertStringContainsString($hook, $html, "Filament no longer emits {$hook}; the sidebar filter selects on it.");
        }
    }
}
