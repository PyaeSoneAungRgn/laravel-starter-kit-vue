<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[AsCommand(name: 'rbac:superadmin', description: 'Create a superadmin user or promote an existing one')]
#[Signature('rbac:superadmin
        {--user= : ID or email address of an existing user to promote}
        {--name= : Name of the superadmin to create}
        {--email= : Email address of the superadmin to create or promote}
        {--password= : Password of the superadmin to create}')]
class CreateSuperAdminCommand extends Command
{
    public function handle(): int
    {
        if (! config('rbac.enabled')) {
            $this->components->warn('RBAC is disabled (RBAC_ENABLED=false). The superadmin flag will have no effect until it is enabled.');
        }

        $user = $this->resolveUser();

        if (! $user instanceof User) {
            return self::FAILURE;
        }

        if ($user->isSuperAdmin()) {
            $this->components->info(sprintf('%s is already a superadmin.', $user->email));

            return self::SUCCESS;
        }

        $user->forceFill(['is_superadmin' => true])->save();

        $this->components->info(sprintf('%s is now a superadmin and bypasses every authorization check.', $user->email));

        return self::SUCCESS;
    }

    private function resolveUser(): ?User
    {
        $identifier = $this->option('user');

        if (filled($identifier)) {
            $user = User::query()
                ->where('id', $identifier)
                ->orWhere('email', $identifier)
                ->first();

            if (! $user instanceof User) {
                $this->components->error(sprintf('No user matches "%s".', $identifier));

                return null;
            }

            return $user;
        }

        $email = $this->option('email');

        if (blank($email) && $this->input->isInteractive()) {
            $email = text(
                label: 'Email address',
                required: true,
                validate: fn (string $value): ?string => filter_var($value, FILTER_VALIDATE_EMAIL) === false
                    ? 'Enter a valid email address.'
                    : null,
            );
        }

        if (blank($email)) {
            $this->components->error('Provide --email or --user to create or promote a superadmin.');

            return null;
        }

        $email = (string) $email;

        $existing = User::query()->where('email', $email)->first();

        if ($existing instanceof User) {
            return $existing;
        }

        $name = $this->option('name');

        if (blank($name) && $this->input->isInteractive()) {
            $name = text(
                label: 'Name',
                default: Str::headline(Str::before($email, '@')),
                required: true,
            );
        }

        $name = (string) ($name ?: Str::headline(Str::before($email, '@')));

        $password = $this->option('password');
        $passwordWasGenerated = false;

        if (blank($password)) {
            if ($this->input->isInteractive()) {
                $password = password(label: 'Password', required: true);
                $confirmation = password(label: 'Confirm password', required: true);

                if ($password !== $confirmation) {
                    $this->components->error('The password confirmation does not match.');

                    return null;
                }
            } else {
                $password = Str::password(16);
                $passwordWasGenerated = true;
            }
        }

        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', 'string', Password::defaults()]],
        );

        if ($validator->fails()) {
            $this->components->error((string) $validator->errors()->first('password'));

            return null;
        }

        $user = new User;
        $user->fill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->forceFill(['email_verified_at' => now()]);
        $user->save();

        if ($passwordWasGenerated) {
            $this->components->info(sprintf('Generated password for %s: %s', $email, $password));
        }

        return $user;
    }
}
