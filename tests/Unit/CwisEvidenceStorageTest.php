<?php

namespace Tests\Unit;

use App\Support\CwisEvidence;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class CwisEvidenceStorageTest extends TestCase
{
    private function upload($result): UploadedFile
    {
        $file = Mockery::mock(UploadedFile::class);
        $expectation = $file->shouldReceive('store')->once()->with('cwis/equity-documents', 'public');
        if ($result instanceof \Throwable) {
            $expectation->andThrow($result);
        } else {
            $expectation->andReturn($result);
        }
        return $file;
    }

    public function test_success_returns_the_saved_path_without_a_warning(): void
    {
        Log::shouldReceive('warning')->never();
        $path = 'cwis/equity-documents/saved.pdf';
        $this->assertSame($path, CwisEvidence::store($this->upload($path),
            'cwis/equity-documents', 'public', 'evidence.EQ_6_1'));
    }

    public function test_false_write_becomes_a_field_error_and_is_logged(): void
    {
        Log::shouldReceive('warning')->once()->with('Unable to store CWIS evidence document.', [
            'disk' => 'public', 'directory' => 'cwis/equity-documents', 'reason' => 'Storage driver returned false.',
        ]);
        try {
            CwisEvidence::store($this->upload(false), 'cwis/equity-documents', 'public', 'evidence.EQ_6_1');
            $this->fail('A failed write must not produce a saved document path.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('evidence.EQ_6_1', $exception->errors());
            $this->assertStringContainsString('server storage', $exception->errors()['evidence.EQ_6_1'][0]);
        }
    }

    public function test_thrown_filesystem_failure_becomes_a_field_error(): void
    {
        Log::shouldReceive('warning')->once();
        $exception = new class('Upload volume is read-only.') extends \RuntimeException implements \League\Flysystem\FilesystemException {};
        $this->expectException(ValidationException::class);
        CwisEvidence::store($this->upload($exception), 'cwis/equity-documents', 'public', 'evidence.EQ_6_1');
    }

    public function test_failed_upload_returns_json_validation_errors_instead_of_http_500(): void
    {
        Log::shouldReceive('warning')->once();
        $file = $this->upload(false);
        Route::post('/_test/cwis-evidence-storage', function () use ($file) {
            CwisEvidence::store($file, 'cwis/equity-documents', 'public', 'evidence.EQ_6_1');
        });
        $this->postJson('/_test/cwis-evidence-storage')->assertStatus(422)
            ->assertJsonValidationErrors('evidence.EQ_6_1')
            ->assertJsonMissing(['exception' => 'RuntimeException']);
    }

    public function test_unrelated_programming_errors_are_not_hidden_as_storage_validation(): void
    {
        Log::shouldReceive('warning')->never();
        $this->expectException(\LogicException::class);
        CwisEvidence::store($this->upload(new \LogicException('Unexpected application error.')),
            'cwis/equity-documents', 'public', 'evidence.EQ_6_1');
    }
}
