<?php

namespace Wotz\TranslatableRoutes\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Wotz\TranslatableRoutes\Providers\TranslatableRoutesServiceProvider;
use Wotz\TranslatableRoutes\Tests\Http\Kernel;
use Wotz\TranslatableRoutes\Tests\TestModels\TestPage;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();
        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [
            TranslatableRoutesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app->useLangPath(__DIR__ . '/lang');
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('created_at');
            $table->datetime('updated_at');
            $table->json('name');
            $table->json('slug');
        });

        TestPage::create([
            'name' => [
                'nl' => '[NL] Page name',
                'fr-BE' => '[FR] Page name',
                'en-GB' => '[EN] Page name',
            ],
            'slug' => [
                'nl' => 'nl-slug',
                'fr-BE' => 'fr-slug',
                'en-GB' => 'en-slug',
            ],
        ]);
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', Kernel::class);
    }
}
