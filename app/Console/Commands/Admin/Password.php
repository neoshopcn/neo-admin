<?php

namespace App\Console\Commands\Admin;

use App\Models\User;
use Illuminate\Console\Command;

class Password extends Command
{
    protected $signature = 'admin:reset-password';

    protected $description = 'Reset the super administrator (admin) password with a randomly generated one (8–12 chars: digits, upper & lower case letters).';

    public function handle(): int
    {
        $user = User::query()->where('username', 'admin')->first();
        if ($user === null) {
            $this->components->error('User [admin] was not found.');

            return self::FAILURE;
        }

        $plain = $this->generateRandomPassword();

        $user->password = $plain;
        $user->save();

        $this->components->info('Password for [admin] has been reset.');
        $this->line('New password: <fg=green;options=bold>'.$plain.'</>');
        $this->warn('Save this password now; it will not be shown again.');

        return self::SUCCESS;
    }

    /**
     * 8–12 位，至少各含：数字、小写、大写字母。
     */
    private function generateRandomPassword(): string
    {
        $length = random_int(8, 12);
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $pool = $lower.$upper.$digits;

        $chars = [
            $lower[random_int(0, 25)],
            $upper[random_int(0, 25)],
            $digits[random_int(0, 9)],
        ];
        for ($i = count($chars); $i < $length; $i++) {
            $chars[] = $pool[random_int(0, strlen($pool) - 1)];
        }

        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }

        return implode('', $chars);
    }
}
