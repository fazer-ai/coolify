<?php

use App\Livewire\SettingsDropdown;
use App\Models\User;
use App\Services\ChangelogService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

it('renders the changelog modal above the desktop sidebar toggle', function () {
    $user = new User(['email' => 'test@example.com']);
    $user->id = 1;

    Auth::setUser($user);

    app()->instance(ChangelogService::class, new class extends ChangelogService
    {
        public function getEntriesForUser(User $user): Collection
        {
            return collect([
                (object) [
                    'tag_name' => 'v1.0.0',
                    'title' => 'Test Release',
                    'content' => 'Release notes',
                    'content_html' => '<p>Release notes</p>',
                    'published_at' => Carbon::parse('2026-05-01'),
                    'is_read' => false,
                ],
            ]);
        }

        public function getUnreadCountForUser(User $user): int
        {
            return 1;
        }
    });

    Livewire::test(SettingsDropdown::class, ['trigger' => 'changelog-sidebar'])
        ->call('openWhatsNewModal')
        ->assertSee('Changelog')
        ->assertSee('z-[60]', false)
        ->assertSee('closeWhatsNewModal', false);
});

it('stores the coolify version without a leading "v"', function () {
    // The dropdown renders the version as 'v'.config('constants.coolify.version').
    // If the stored version itself starts with "v" (e.g. a release tag injected
    // verbatim by the build pipeline), the UI ends up showing "vv4.1.2-...".
    expect(config('constants.coolify.version'))->not->toStartWith('v');
});

it('renders the current version with a single leading "v"', function () {
    config()->set('constants.coolify.version', '4.1.2-fazer-ai.6');

    expect((new SettingsDropdown)->getCurrentVersionProperty())->toBe('v4.1.2-fazer-ai.6');
});
