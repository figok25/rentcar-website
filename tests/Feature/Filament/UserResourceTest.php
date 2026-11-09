<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_can_create_admin_with_hashed_password(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Staf Baru', 'email' => 'staf@example.com', 'password' => 'rahasia123', 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'staf@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertTrue($user->is_active);
    }

    public function test_password_required_on_create_and_min_length(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'X', 'email' => 'x@example.com', 'password' => ''])
            ->call('create')
            ->assertHasFormErrors(['password' => 'required']);

        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'X', 'email' => 'x@example.com', 'password' => 'short'])
            ->call('create')
            ->assertHasFormErrors(['password' => 'min']);
    }

    public function test_blank_password_on_edit_keeps_old_password(): void
    {
        $user = User::factory()->create(['password' => 'passwordlama1']);
        $old = $user->password;

        Livewire::test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm(['name' => 'Nama Baru', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame($old, $user->password);
    }

    public function test_can_change_password_and_deactivate_other_admin(): void
    {
        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm(['password' => 'passwordbaru1', 'is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('passwordbaru1', $user->password));
        $this->assertFalse($user->is_active);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        Livewire::test(EditUser::class, ['record' => $this->admin->getKey()])
            ->assertFormFieldIsDisabled('is_active');
    }

    public function test_cannot_delete_self_but_can_delete_others(): void
    {
        $other = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('delete', $this->admin)
            ->assertTableActionVisible('delete', $other);
    }

    public function test_email_must_be_unique(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Dup', 'email' => $this->admin->email, 'password' => 'passwordbaru1'])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);
    }
}
