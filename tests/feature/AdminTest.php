<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;

final class AdminTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected $namespace = ['CodeIgniter\Shield', 'CodeIgniter\Settings'];
    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['auth', 'setting', 'form', 'url']);
        service('settings')->flush();
        service('cache')->clean();
    }

    private function user(string $group = 'user', string $name = 'tester'): User
    {
        $model = auth()->getProvider();
        $model->save(new User(['username' => $name, 'email' => $name . '@example.test', 'password' => 'Violet!River8Cloud#27', 'active' => 1]));
        $user = $model->findById($model->getInsertID());
        $user->syncGroups($group);
        return $user;
    }

    private function submit(string $path, array $data = [])
    {
        $data[csrf_token()] = csrf_hash();
        return $this->withSession()->post($path, $data);
    }

    public function testGuestIsRedirected(): void
    {
        $this->get('admin')->assertRedirectTo(url_to('login'));
    }

    public function testLoginAndPostLogout(): void
    {
        $this->user();
        $this->submit('login', ['email' => 'tester@example.test', 'password' => 'Violet!River8Cloud#27', 'remember' => '1'])->assertRedirectTo(site_url('admin'));
        $this->assertTrue(auth()->loggedIn());
        $this->get('admin')->assertStatus(200);
        $this->get('login')->assertRedirectTo(site_url('admin'));
        $this->submit('logout')->assertRedirectTo(url_to('login'));
        $this->assertFalse(auth()->loggedIn());
    }

    public function testWrongPasswordFails(): void
    {
        $this->user();
        $this->submit('login', ['email' => 'tester@example.test', 'password' => 'wrong-password'])->assertRedirectTo(url_to('login'));
        $this->assertFalse(auth()->loggedIn());
    }

    public function testDashboardAndAuthorization(): void
    {
        $this->actingAs($this->user());
        $this->get('admin')->assertSee('Welcome back, tester');
        $this->get('admin/users')->assertStatus(403);
        $this->get('admin/settings')->assertStatus(403);
        $this->submit('admin/users', ['username' => 'intruder'])->assertStatus(403);
    }

    public function testDeveloperSettingsAreReadOnly(): void
    {
        $this->actingAs($this->user('developer'));
        $this->get('admin/settings')->assertSee('read-only');
        $this->submit('admin/settings', ['siteName' => 'Tampered'])->assertStatus(403);
        $this->assertSame('CI Daisy', setting('App.siteName'));
    }

    public function testUserCrudAndSearch(): void
    {
        $this->actingAs($this->user('superadmin', 'owner'));
        $this->submit('admin/users', ['username' => 'newuser', 'email' => 'newuser@example.test', 'password' => 'Violet!River8Cloud#27', 'password_confirm' => 'Violet!River8Cloud#27', 'groups' => ['user', 'beta']])->assertRedirect();
        $created = auth()->getProvider()->findByCredentials(['email' => 'newuser@example.test']);
        $this->assertNotNull($created);
        $this->assertTrue($created->inGroup('user', 'beta'));
        $this->assertTrue($created->requiresPasswordReset());
        $this->get('admin/users?q=newuser%40example.test')->assertSee('newuser');
        $this->get('admin/users/' . $created->id)->assertStatus(200);
        $this->get('admin/users/' . $created->id . '/edit')->assertStatus(200);
        $this->submit('admin/users/' . $created->id, ['username' => 'renamed', 'email' => 'renamed@example.test', 'groups' => ['user']])->assertRedirect();
        $this->assertSame('renamed', auth()->getProvider()->findById($created->id)->username);
        $this->submit('admin/users/' . $created->id . '/status')->assertRedirect();
        $this->assertFalse((bool) auth()->getProvider()->findById($created->id)->active);
        $this->submit('admin/users/' . $created->id . '/status')->assertRedirect();
        $this->assertTrue((bool) auth()->getProvider()->findById($created->id)->active);
        $this->submit('admin/users/' . $created->id . '/delete')->assertRedirect();
        $this->assertNull(auth()->getProvider()->findById($created->id));
    }

    public function testAdminCannotEscalatePrivileges(): void
    {
        $owner = $this->user('superadmin', 'owner');
        $admin = $this->user('admin', 'manager');
        $ordinary = $this->user('user', 'ordinary');
        $this->actingAs($admin);
        $this->submit('admin/users/' . $owner->id, ['username' => 'hijacked', 'email' => 'bad@example.test', 'groups' => ['user']])->assertStatus(403);
        $this->submit('admin/users/' . $admin->id, ['groups' => ['superadmin']])->assertStatus(403);
        $this->submit('admin/users/' . $ordinary->id, ['username' => 'ordinary', 'email' => 'ordinary@example.test', 'groups' => ['superadmin']])->assertRedirect();
        $this->assertFalse(auth()->getProvider()->findById($ordinary->id)->inGroup('superadmin'));
    }

    public function testCannotDeleteOrDeactivateSelf(): void
    {
        $owner = $this->user('superadmin');
        $this->actingAs($owner);
        $this->submit('admin/users/' . $owner->id . '/status')->assertStatus(403);
        $this->submit('admin/users/' . $owner->id . '/delete')->assertStatus(403);
    }

    public function testInactiveAccountCannotUseExistingSession(): void
    {
        $user = $this->user();
        $user->deactivate();
        $this->actingAs(auth()->getProvider()->findById($user->id));
        $this->get('admin')->assertRedirectTo(url_to('login'));
        $this->assertFalse(auth()->loggedIn());
    }

    public function testSettingsPersistAndMaintenanceAllowsAdmins(): void
    {
        $this->actingAs($this->user('superadmin'));
        $this->submit('admin/settings', ['siteName' => 'Workspace test', 'siteDescription' => 'Testing', 'contactEmail' => 'contact@example.test', 'timezone' => 'Asia/Bangkok', 'dateFormat' => 'Y-m-d', 'maintenanceMode' => '1'])->assertRedirect();
        $freshSettings = new \CodeIgniter\Settings\Settings(config('Settings'));
        $this->assertSame('Workspace test', $freshSettings->get('App.siteName'));
        $this->get('admin')->assertStatus(200);
        auth()->logout();
        $this->withSession()->get('admin')->assertStatus(503);
        $this->get('login')->assertStatus(200);
    }

    public function testInvalidSettingsDoNotPersist(): void
    {
        $this->actingAs($this->user('superadmin'));
        $this->submit('admin/settings', ['siteName' => 'Invalid', 'siteDescription' => '', 'contactEmail' => 'contact@example.test', 'timezone' => 'Invalid/Zone', 'dateFormat' => 'Y-m-d'])->assertRedirect();
        $this->assertSame('CI Daisy', setting('App.siteName'));
    }

    public function testPasswordChangeVerifiesCurrentPassword(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $this->submit('admin/profile/password', ['current_password' => 'incorrect', 'password' => 'Cobalt!Mountain7Rain#9', 'password_confirm' => 'Cobalt!Mountain7Rain#9'])->assertRedirect();
        $this->assertTrue(service('passwords')->verify('Violet!River8Cloud#27', auth()->getProvider()->findById($user->id)->password_hash));
        $this->submit('admin/profile/password', ['current_password' => 'Violet!River8Cloud#27', 'password' => 'Cobalt!Mountain7Rain#9', 'password_confirm' => 'Cobalt!Mountain7Rain#9'])->assertRedirectTo(url_to('login'));
        $this->assertTrue(service('passwords')->verify('Cobalt!Mountain7Rain#9', auth()->getProvider()->findById($user->id)->password_hash));
        $this->assertFalse(auth()->loggedIn());
    }

    public function testStateChangeWithoutCsrfFails(): void
    {
        $this->actingAs($this->user('superadmin'));
        $this->expectException(\CodeIgniter\Security\Exceptions\SecurityException::class);
        $this->withSession()->post('admin/users', ['username' => 'forged']);
    }

    public function testRegistrationAndGetLogoutAreUnavailable(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('register');
    }

    public function testGetLogoutIsUnavailable(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('logout');
    }

    public function testDuplicateEmailsAreRejectedWithoutChangingAccount(): void
    {
        $this->user('user', 'existing');
        $other = $this->user('user', 'other');
        $this->actingAs($this->user('superadmin', 'owner'));
        $this->submit('admin/users/' . $other->id, ['username' => 'other', 'email' => 'existing@example.test', 'groups' => ['user']])->assertRedirect();
        $this->assertSame('other@example.test', auth()->getProvider()->findById($other->id)->email);
        $this->submit('admin/users', ['username' => 'duplicate', 'email' => 'existing@example.test', 'password' => 'Violet!River8Cloud#27', 'password_confirm' => 'Violet!River8Cloud#27', 'groups' => ['user']])->assertRedirect();
        $this->assertSame(3, auth()->getProvider()->countAllResults());
    }

    public function testMagicLinkRecoveryAndPasswordChange(): void
    {
        $user = $this->user();
        // Mock delivery only: exercise Shield token creation, verification, and single-use consumption.
        $email = $this->getMockBuilder(\CodeIgniter\Email\Email::class)->onlyMethods(['send'])->getMock();
        $email->method('send')->willReturn(true);
        \Config\Services::injectMock('email', $email);
        $this->submit('login/magic-link', ['email' => $user->email])->assertSee('Check your email');
        $identity = model(\CodeIgniter\Shield\Models\UserIdentityModel::class)->getIdentityByType($user, 'magic-link');
        $this->assertNotNull($identity);
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get('login/verify-magic-link?token=' . $identity->secret)->assertRedirectTo(site_url('admin/profile'));
        $this->assertTrue(auth()->loggedIn());
        $this->assertNull(model(\CodeIgniter\Shield\Models\UserIdentityModel::class)->getIdentityByType($user, 'magic-link'));
        $this->submit('admin/profile/password', ['password' => 'Cobalt!Mountain7Rain#9', 'password_confirm' => 'Cobalt!Mountain7Rain#9'])->assertRedirectTo(url_to('login'));
        $this->assertFalse(auth()->loggedIn());
        $this->assertNull(session()->getTempdata('magicLogin'));
        $this->assertTrue(service('passwords')->verify('Cobalt!Mountain7Rain#9', auth()->getProvider()->findById($user->id)->password_hash));
    }

    public function testRecoveryEmailFailureDoesNotForceReset(): void
    {
        $user = $this->user();
        $this->actingAs($this->user('superadmin', 'owner'));
        $email = $this->getMockBuilder(\CodeIgniter\Email\Email::class)->onlyMethods(['send'])->getMock();
        $email->method('send')->willReturn(false);
        \Config\Services::injectMock('email', $email);
        $this->submit('admin/users/' . $user->id . '/reset-password')->assertRedirect();
        $this->assertFalse($user->requiresPasswordReset());
    }

    public function testForcedPasswordChangeBlocksDashboard(): void
    {
        $user = $this->user();
        $user->forcePasswordReset();
        $this->actingAs($user);
        $this->get('admin')->assertRedirectTo(site_url('admin/profile'));
        $this->get('admin/profile')->assertStatus(200);
    }

    public function testFirstSuperadminCommand(): void
    {
        foreach (['username' => 'first.owner', 'email' => 'first@example.test', 'password' => 'Violet!River8Cloud#27'] as $key => $value) $_ENV['setup.' . $key] = $value;
        try {
            command('app:superadmin');
            $provider = config('Auth')->userProvider;
            $owner = (new $provider())->findByCredentials(['email' => 'first@example.test']);
            $this->assertNotNull($owner);
            $this->assertTrue($owner->inGroup('superadmin'));
            command('app:superadmin');
            $this->assertSame(1, auth()->getProvider()->countAllResults());
        } finally {
            foreach (['username', 'email', 'password'] as $key) unset($_ENV['setup.' . $key]);
        }
    }
}
