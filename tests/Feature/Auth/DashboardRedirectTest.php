<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('customers land on the shop and admins on the admin home', function () {
    $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertRedirect(route('cake-studio.index'));

    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('admin.home'));
});
