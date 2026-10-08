<?php

namespace Tests\Feature;

use Tests\TestCase;

class ValidateSeedUserConfigurationCommandTest extends TestCase
{
    public function test_command_fails_when_configuration_is_missing(): void
    {
        config(['seed_users.accounts' => []]);

        $this->artisan('seed-users:validate-config')
            ->expectsOutput('Seed user configuration is missing or invalid.')
            ->assertExitCode(1);
    }

    public function test_command_fails_safely_when_configuration_is_invalid(): void
    {
        $account = $this->validAccount();
        $account['password'] = 'invalid';
        config(['seed_users.accounts' => ['deployment' => $account]]);

        $this->artisan('seed-users:validate-config')
            ->expectsOutput('Seed user configuration is missing or invalid.')
            ->doesntExpectOutput($account['password'])
            ->assertExitCode(1);
    }

    public function test_command_succeeds_non_interactively_for_valid_configuration(): void
    {
        config(['seed_users.accounts' => ['deployment' => $this->validAccount()]]);

        $this->artisan('seed-users:validate-config')
            ->expectsOutput('Seed user configuration is valid.')
            ->assertExitCode(0);
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
                'name' => 'SEED_TEST_NAME',
                'username' => 'SEED_TEST_USERNAME',
                'email' => 'SEED_TEST_EMAIL',
                'password' => 'SEED_TEST_PASSWORD',
            ],
        ];
    }
}
