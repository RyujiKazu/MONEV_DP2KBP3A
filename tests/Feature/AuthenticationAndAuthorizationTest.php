<?php

namespace Tests\Feature;

use App\Models\User;

class AuthenticationAndAuthorizationTest extends FeatureTestCase
{
    public function test_admin_can_log_in(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);

        $response = $this->post(route('login.submit'), [
            'username' => $admin->username,
            'password' => 'rahasia-test',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_pkk_can_log_in(): void
    {
        $pkk = $this->createUser(User::ROLE_PKK);

        $response = $this->post(route('login.submit'), [
            'username' => $pkk->username,
            'password' => 'rahasia-test',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($pkk);
    }

    public function test_pkk_is_sent_to_dashboard_instead_of_an_intended_admin_page(): void
    {
        $pkk = $this->createUser(User::ROLE_PKK);

        $this->get(route('admin.rekap-krs.index'))->assertRedirect(route('login'));

        $this->post(route('login.submit'), [
            'username' => $pkk->username,
            'password' => 'rahasia-test',
        ])->assertRedirect(route('dashboard.index'));
    }

    public function test_pkk_receives_forbidden_response_for_every_admin_module(): void
    {
        $pkk = $this->createUser(User::ROLE_PKK);

        foreach ([
            'admin.users.index',
            'admin.data-wilayah.index',
            'admin.rekap-krs.index',
            'admin.target-indikator.index',
        ] as $routeName) {
            $this->actingAs($pkk)
                ->get(route($routeName))
                ->assertForbidden();
        }
    }
}
