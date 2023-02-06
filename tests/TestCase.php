<?php

namespace Codedor\TranslatableRoutes\Tests;

use Codedor\TranslatableRoutes\Providers\TranslatableRoutesServiceProvider;
use Codedor\TranslatableRoutes\Tests\TestModels\TestPage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

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
     * @param \Illuminate\Foundation\Application $app
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
}
