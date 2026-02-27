<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;

class AuthLoginDomainIssueTest extends TestCase
{
    public function test_central_login()
    {
        $response = $this->post('http://fcta.gov.local/login', [
            'email' => 'superadmin@fcta.gov.local',
            'password' => 'password',
        ]);

        $errors = session('errors');
        if ($errors) {
            echo "Central Login Errors: " . json_encode($errors->getBag('default')->getMessages()) . "\n";
        } else {
            echo "Central Login Success (Redirect to: " . $response->headers->get('Location') . ")\n";
        }
    }

    public function test_tenant_login()
    {
        $tenant = Tenant::first();
        if ($tenant) {
            $domain = $tenant->domains->first()->domain;

            // Try to log in with a central user on a tenant domain
            $response = $this->post('http://' . $domain . '/login', [
                'email' => 'superadmin@fcta.gov.local',
                'password' => 'password',
            ]);

            $errors = session('errors');
            if ($errors) {
                echo "Tenant Login Errors (Central User): " . json_encode($errors->getBag('default')->getMessages()) . "\n";
            } else {
                echo "Tenant Login Success!\n";
            }
        } else {
            echo "No tenants found.\n";
        }
    }
}
