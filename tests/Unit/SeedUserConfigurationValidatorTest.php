<?php

namespace Tests\Unit;

use App\Exceptions\SeedUserConfigurationException;
use App\Services\Deployment\SeedUserConfigurationValidator;
use PHPUnit\Framework\TestCase;

class SeedUserConfigurationValidatorTest extends TestCase
{
    public function test_it_accepts_valid_configuration(): void
    {
        $accounts = ['account_one' => $this->validAccount()];

        $this->assertSame($accounts, (new SeedUserConfigurationValidator(8))->validateOrFail($accounts));
    }

    public function test_it_rejects_missing_values_without_disclosing_other_values(): void
    {
        $account = $this->validAccount();
        $account['password'] = null;
        $sensitiveMarker = $account['email'];

        try {
            (new SeedUserConfigurationValidator(8))->validateOrFail(['account_one' => $account]);
            $this->fail('Expected invalid configuration to be rejected.');
        } catch (SeedUserConfigurationException $exception) {
            $output = $exception->getMessage().' '.implode(' ', $exception->safeErrors());
            $this->assertStringContainsString('SEED_ACCOUNT_ONE_PASSWORD', $output);
            $this->assertStringNotContainsString($sensitiveMarker, $output);
        }
    }

    public function test_it_rejects_invalid_username_email_and_password(): void
    {
        $account = $this->validAccount();
        $account['username'] = 'invalid username';
        $account['email'] = 'invalid-email';
        $account['password'] = 'short';

        $this->expectException(SeedUserConfigurationException::class);
        (new SeedUserConfigurationValidator(8))->validateOrFail(['account_one' => $account]);
    }

    public function test_it_rejects_duplicate_usernames_and_emails_case_insensitively(): void
    {
        $first = $this->validAccount();
        $second = $this->validAccount();
        $second['username'] = strtoupper($first['username']);
        $second['email'] = strtoupper($first['email']);

        $this->expectException(SeedUserConfigurationException::class);
        (new SeedUserConfigurationValidator(8))->validateOrFail([
            'account_one' => $first,
            'account_two' => $second,
        ]);
    }

    /** @return array<string, mixed> */
    private function validAccount(): array
    {
        return [
            'name' => 'Deployment Account',
            'username' => 'deployment_'.bin2hex(random_bytes(4)),
            'email' => bin2hex(random_bytes(4)).'@example.test',
            'password' => 'Aa9!'.bin2hex(random_bytes(8)),
            'role' => 'Super Admin',
            'user_type' => '',
            'environment_variables' => [
                'name' => 'SEED_ACCOUNT_ONE_NAME',
                'username' => 'SEED_ACCOUNT_ONE_USERNAME',
                'email' => 'SEED_ACCOUNT_ONE_EMAIL',
                'password' => 'SEED_ACCOUNT_ONE_PASSWORD',
            ],
        ];
    }
}
