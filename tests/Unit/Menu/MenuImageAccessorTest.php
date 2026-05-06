<?php

namespace Tests\Unit\Menu;

use App\Models\Category;
use App\Models\Menu;
use App\Services\MenuImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuImageAccessorTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_url_returns_null_when_image_is_null(): void
    {
        $menu = $this->createMenu('menu-no-image');
        
        $this->assertNull($menu->image);
        $this->assertNull($menu->image_url);
    }

    public function test_image_url_returns_full_absolute_url_when_image_is_set(): void
    {
        Storage::fake('public');
        
        $menu = $this->createMenu('menu-with-image', 'menus/test-image.webp');
        
        // Storage::url() should return absolute URL like: http://localhost/storage/menus/test-image.webp
        $expectedUrl = Storage::disk('public')->url('menus/test-image.webp');
        
        $this->assertNotNull($menu->image_url);
        $this->assertStringContainsString('menus/test-image.webp', $menu->image_url);
        $this->assertStringStartsWith('http', $menu->image_url);
        $this->assertSame($expectedUrl, $menu->image_url);
    }

    public function test_deleting_menu_with_image_removes_file_from_storage(): void
    {
        Storage::fake('public');
        
        $menuImageService = app(MenuImageService::class);
        
        // Create a fake image file
        $file = UploadedFile::fake()->image('test-menu.webp', 100, 100);
        $imagePath = $menuImageService->convertAndStore($file);
        
        $this->assertTrue(Storage::disk('public')->exists($imagePath));
        
        $menu = $this->createMenu('menu-delete-test', $imagePath);
        
        // Delete the menu
        $menu->delete();
        
        // Assert the image file was deleted
        $this->assertFalse(Storage::disk('public')->exists($imagePath));
    }

    public function test_deleting_menu_without_image_does_not_cause_error(): void
    {
        $menu = $this->createMenu('menu-delete-no-image', null);
        
        // Should not throw an exception
        $menu->delete();
        
        // Menu should be deleted successfully
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    public function test_image_url_is_appended_to_serialization(): void
    {
        Storage::fake('public');
        
        $menu = $this->createMenu('menu-serialization', 'menus/serial.webp');
        
        $array = $menu->toArray();
        
        $this->assertArrayHasKey('image_url', $array);
        $this->assertNotNull($array['image_url']);
    }

    private function createMenu(string $slug, ?string $image = null): Menu
    {
        $category = Category::create([
            'name' => 'Kategori ' . $slug,
            'slug' => 'kategori-' . $slug,
            'is_active' => true,
        ]);

        return Menu::create([
            'category_id' => $category->id,
            'name' => 'Menu ' . $slug,
            'slug' => $slug,
            'description' => null,
            'price' => 15000,
            'cashback' => 0,
            'image' => $image,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);
    }
}
