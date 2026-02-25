<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->ask('Email', 'admin@gmail.com');

        $user = User::create([
            'name' => 'test',
            'email' => $email,
            'password' => $password = Str::random(8),
            'role' => 'admin',
        ]);

        $this->info("Login: {$user->email}.");
        $this->info("Password: {$password}.");
    }
}
