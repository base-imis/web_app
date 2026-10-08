<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UsersTableSeeder;
use App\Exceptions\SeedUserConfigurationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsersTableSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'permission.table_names.roles' => 'roles',
            'permission.table_names.model_has_roles' => 'model_has_roles',
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::statement("ATTACH DATABASE ':memory:' AS auth");

        Schema::create('auth.users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('username');
            $table->string('email');
            $table->string('password');
            $table->string('user_type');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });
    }

    public function test_seeder_is_idempotent_and_does_not_reset_existing_password(): void
    {
        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $account = $this->validAccount();
        config(['seed_users.accounts' => ['deployment' => $account]]);

        $this->seed(UsersTableSeeder::class);
        $user = User::where('username', $account['username'])->firstOrFail();
        $this->assertTrue(Hash::check($account['password'], $user->password));
        $this->assertTrue($user->hasRole('Super Admin'));

        $replacementPassword = 'Bb8@'.bin2hex(random_bytes(8));
        $replacementHash = Hash::make($replacementPassword);
        $user->password = $replacementHash;
        $user->save();

        $this->seed(UsersTableSeeder::class);

        $this->assertSame(1, User::where('username', $account['username'])->count());
        $this->assertSame($replacementHash, $user->fresh()->password);
    }

    public function test_seeder_rejects_invalid_configuration_before_creating_a_user(): void
    {
        $account = $this->validAccount();
        $account['password'] = null;
        config(['seed_users.accounts' => ['deployment' => $account]]);

        try {
            $this->seed(UsersTableSeeder::class);
            $this->fail('Expected invalid configuration to stop the seeder.');
        } catch (SeedUserConfigurationException $exception) {
            $this->assertSame(0, User::count());
            $this->assertStringNotContainsString($account['email'], implode(' ', $exception->safeErrors()));
        }
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
