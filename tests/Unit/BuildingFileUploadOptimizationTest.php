<?php

namespace Tests\Unit;

use Tests\TestCase;

class BuildingFileUploadOptimizationTest extends TestCase
{
    public function test_file_fields_are_at_the_top_of_building_information(): void
    {
        $view = file_get_contents(
            resource_path('views/building-info/buildings/partial-form.blade.php')
        );

        $headingPosition = strpos($view, "__('Building Information')");
        $kmlPosition = strpos($view, "Form::label('geom'");
        $mainBuildingPosition = strpos($view, "Form::label('main_building'");

        $this->assertNotFalse($headingPosition);
        $this->assertNotFalse($kmlPosition);
        $this->assertNotFalse($mainBuildingPosition);
        $this->assertLessThan($kmlPosition, $headingPosition);
        $this->assertLessThan($mainBuildingPosition, $kmlPosition);
        $this->assertStringContainsString("'accept' => '.kml", $view);
        $this->assertStringContainsString("'accept' => '.jpg,.jpeg,image/jpeg'", $view);
    }

    public function test_shared_upload_script_validates_kml_and_compresses_images(): void
    {
        $script = file_get_contents(public_path('js/building-file-upload.js'));

        $this->assertStringContainsString('IMAGE_MAX_DIMENSION = 2560', $script);
        $this->assertStringContainsString('IMAGE_QUALITY = 0.88', $script);
        $this->assertStringContainsString("canvas.toBlob(resolve, 'image/jpeg'", $script);
        $this->assertStringContainsString("getElementsByTagNameNS('*', 'Polygon')", $script);
        $this->assertStringContainsString('KML_MAX_BYTES = 1 * 1024 * 1024', $script);
        $this->assertStringContainsString('IMAGE_MAX_BYTES = 5 * 1024 * 1024', $script);
    }

    public function test_house_images_use_short_lived_browser_caching(): void
    {
        $configuration = file_get_contents(public_path('.htaccess'));

        $this->assertStringContainsString('/storage/emptyings/houses/', $configuration);
        $this->assertStringContainsString(
            'Cache-Control "public, max-age=3600, must-revalidate"',
            $configuration
        );
    }
}
