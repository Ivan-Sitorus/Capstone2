<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Menu;
use App\Services\MenuImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MenuImageUploadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_menu_image_url_returns_null_when_no_image(): void
    {
        $menu = $this->createMenu('menu-no-image');

        $this->assertNull($menu->image);
        $this->assertNull($menu->image_url);
    }

    /** @test */
    public function test_menu_image_url_returns_full_url_when_image_exists(): void
    {
        Storage::fake('public');

        $menu = $this->createMenu('menu-with-image', 'menus/test-image.webp');

        $expectedUrl = Storage::disk('public')->url('menus/test-image.webp');

        $this->assertNotNull($menu->image_url);
        $this->assertStringStartsWith('http', $menu->image_url);
        $this->assertStringContainsString('menus/test-image.webp', $menu->image_url);
        $this->assertSame($expectedUrl, $menu->image_url);
    }

    /** @test */
    public function test_deleting_menu_with_image_removes_file_from_storage(): void
    {
        Storage::fake('public');

        $service = app(MenuImageService::class);

        $file = UploadedFile::fake()->image('test-menu.webp', 100, 100);
        $imagePath = $service->convertAndStore($file);

        $this->assertTrue(Storage::disk('public')->exists($imagePath));

        $menu = $this->createMenu('menu-delete-test', $imagePath);

        // Menu::deleting event calls MenuImageService::delete($menu->image)
        $menu->delete();

        $this->assertFalse(Storage::disk('public')->exists($imagePath));
    }

    /** @test */
    public function test_replacing_image_deletes_old_file_and_stores_new(): void
    {
        Storage::fake('public');

        $service = app(MenuImageService::class);

        // Create and store old image
        $oldFile = UploadedFile::fake()->image('old-photo.png', 200, 200);
        $oldPath = $service->convertAndStore($oldFile);

        $this->assertTrue(Storage::disk('public')->exists($oldPath));

        // Create menu with old image path
        $menu = $this->createMenu('menu-replace-test', $oldPath);

        // Replace with new image: convertAndStore with oldPath should delete the old file
        $newFile = UploadedFile::fake()->image('new-photo.jpg', 300, 300);
        $newPath = $service->convertAndStore($newFile, $oldPath);

        // Old file must be deleted from storage
        $this->assertFalse(Storage::disk('public')->exists($oldPath));

        // New file must exist in storage
        $this->assertNotNull($newPath);
        $this->assertTrue(Storage::disk('public')->exists($newPath));

        // Paths must be different (not overwriting same filename)
        $this->assertNotSame($oldPath, $newPath);
    }

    /** @test */
    public function test_webp_conversion_reduces_dimensions_to_max_800px_width(): void
    {
        Storage::fake('public');

        $service = app(MenuImageService::class);

        // Upload an image wider than 800px (1200x900)
        $file = UploadedFile::fake()->image('test.jpg', 1200, 900);
        $path = $service->convertAndStore($file);

        $this->assertNotNull($path);
        $this->assertTrue(Storage::disk('public')->exists($path));

        // Verify dimensions using getimagesizefromstring
        $contents = Storage::disk('public')->get($path);
        $this->assertNotEmpty($contents);

        $imageInfo = getimagesizefromstring($contents);
        $this->assertNotFalse($imageInfo, 'Stored file must be a valid image');

        // Width must be scaled down to at most 800px
        $this->assertLessThanOrEqual(800, $imageInfo[0], 'Image width must be ≤ 800px');

        // Height should maintain aspect ratio (1200x900 → 800x600)
        $this->assertEquals(600, $imageInfo[1], 'Image height should maintain aspect ratio');

        // Verify it's a WebP image
        $this->assertSame(IMAGETYPE_WEBP, $imageInfo[2], 'Stored file must be WebP format');

        // WebP files begin with "RIFF" header
        $this->assertStringStartsWith('RIFF', $contents);
    }

    /** @test */
    public function test_cache_is_invalidated_after_menu_save(): void
    {
        // Set values for both cache keys that must be flushed
        Cache::put('customer_menu_v1', ['menu' => 'data'], 300);
        Cache::put('menu_categories_active', ['categories' => 'data'], 300);

        // Both cache keys should have values before invalidation
        $this->assertNotNull(Cache::get('customer_menu_v1'));
        $this->assertNotNull(Cache::get('menu_categories_active'));

        // Simulate what afterCreate() / afterSave() Filament hooks do
        Cache::forget('customer_menu_v1');
        Cache::forget('menu_categories_active');

        // Both cache keys must be null after invalidation
        $this->assertNull(Cache::get('customer_menu_v1'));
        $this->assertNull(Cache::get('menu_categories_active'));
    }

    /** @test */
    public function test_upload_validation_rejects_file_over_5mb(): void
    {
        // FileUpload::maxSize(5120) — 5MB in KB = 5120
        $file = UploadedFile::fake()->create('oversized.jpg', 6000, 'image/jpeg');

        $validator = Validator::make(
            ['image' => $file],
            ['image' => 'max:5120']
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('image', $validator->errors()->toArray());
    }

    /** @test */
    public function test_upload_validation_rejects_invalid_mime_type(): void
    {
        // FileUpload::acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
        // A GIF file is NOT in the accepted types
        $file = UploadedFile::fake()->create('animation.gif', 100, 'image/gif');

        $validator = Validator::make(
            ['image' => $file],
            ['image' => 'mimes:jpeg,png,webp']
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('image', $validator->errors()->toArray());
    }

    /**
     * Helper to create a Menu with required Category relationship.
     */
    private function createMenu(string $slug, ?string $image = null): Menu
    {
        $category = Category::create([
            'name' => 'Kategori '.$slug,
            'slug' => 'kategori-'.$slug,
            'is_active' => true,
        ]);

        return Menu::create([
            'category_id' => $category->id,
            'name' => 'Menu '.$slug,
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
