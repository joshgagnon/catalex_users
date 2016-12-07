<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    protected $runMigrations = true;
    protected $seeder = 'TestSeeder';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        if ($this->runMigrations) {
            $mirgrationOptions = !empty($this->seeder) ? ['--seeder' => $this->seeder] : [];
            $app['Illuminate\Contracts\Console\Kernel']->call('migrate:refresh', $mirgrationOptions);
        }

        return $app;
    }
}

