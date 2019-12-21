<?php

namespace Tests\Feature;

use App\Facades\Preferences;
use App\Module;
use App\OperatingSystem;
use App\Resource;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatingSystemScopeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function users_preferring_windows_only_see_windows_and_ANY_resources()
    {
        $courseType = Arr::random([Resource::VIDEO_TYPE, Resource::COURSE_TYPE]);
        $windowsResource = factory(Resource::class)->create(['os' => OperatingSystem::WINDOWS, 'type' => $courseType]);
        $macResource = factory(Resource::class)->create(['os' => OperatingSystem::MACOS, 'type' => $courseType]);
        $anyResource = factory(Resource::class)->create(['os' => OperatingSystem::ANY, 'type' => $courseType]);

        $module = factory(Module::class)->create();
        $module->resources()->saveMany([$windowsResource, $macResource, $anyResource]);

        $this->be($user = factory(User::class)->create());
        Preferences::set(['operating-system' => OperatingSystem::WINDOWS, 'resource-language' => 'all']);

        $response = $this->get('/en/modules/' . $module->slug);
        $response->assertSee($windowsResource->name);
        $response->assertSee($anyResource->name);
        $response->assertDontSee($macResource->name);
    }
}
