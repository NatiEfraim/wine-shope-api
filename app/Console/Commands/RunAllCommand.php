<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run multiple Artisan commands in sequence';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->call('migrate:fresh', ['--seed' => true]);

        $this->call('passport:client', [
            '--personal' => true,
            '--name' => config('auth.token_name'),
            '--provider' => 'users',

        ]);
        $this->call('optimize:clear');
        $this->call('serve');
    }
}
