<?php

namespace Tests\Unit;

use App\Http\Requests\Fsm\EmptyingApiRequest;
use App\Http\Requests\Fsm\EmptyingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EmptyingFollowUpFixesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::statement("ATTACH DATABASE ':memory:' AS fsm");
        DB::statement('CREATE TABLE fsm.applications (id INTEGER, is_anf BOOLEAN, containment_id INTEGER)');
        DB::statement('CREATE TABLE fsm.emptyings (application_id INTEGER, volume_of_sludge REAL)');
        DB::table('fsm.applications')->insert(['id' => 1, 'is_anf' => false]);
    }

    private function webRules(): array
    {
        return EmptyingRequest::create('/', 'POST', [
            'application_id' => 1, 'trip_no' => 1, 'trip_count' => 1,
        ])->rules();
    }

    /** @dataProvider reasonCases */
    public function test_web_and_mobile_enforce_the_same_reason_choices(?string $reason, bool $accepted): void
    {
        foreach ([$this->webRules(), (new EmptyingApiRequest())->rules()] as $rules) {
            $this->assertSame($accepted, Validator::make(
                ['emptying_reason' => $reason],
                ['emptying_reason' => $rules['emptying_reason']]
            )->passes());
        }
    }

    public static function reasonCases(): array
    {
        return [
            ['Routine Cleaning', true], ['Overflow or Full', true],
            ['arbitrary reason', false], [null, false],
        ];
    }

    /** @dataProvider photoCases */
    public function test_larger_photos_are_accepted_with_a_twenty_megabyte_limit(int $sizeKb, bool $accepted): void
    {
        $rules = $this->webRules();
        foreach (['house_image', 'receipt_image'] as $field) {
            $image = UploadedFile::fake()->image('photo.jpg')->size($sizeKb);
            $this->assertSame($accepted, Validator::make(
                [$field => $image], [$field => $rules[$field]]
            )->passes());
        }
    }

    public static function photoCases(): array
    {
        return [[6144, true], [20480, true], [20481, false]];
    }
}
