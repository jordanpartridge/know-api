<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;

class ActivateUserCommand extends Command
{
    protected $signature = 'user:activate';
    protected $description = 'Activate a user account';

    public function handle(): void
    {
        $totalInactive = User::withInactive()->whereNull('activated_at')->count();

        if ($totalInactive === 0) {
            info('No inactive users found.');

            return;
        }

        // Show recent registrations (last 10)
        $recentUsers = User::withInactive()
            ->whereNull('activated_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        info("📊 {$totalInactive} inactive users total");
        info('🕐 Recent registrations:');

        foreach ($recentUsers as $user) {
            $createdAt = $user->created_at?->diffForHumans() ?? 'Unknown time';
            $this->line("   • {$user->name} ({$user->email}) - {$createdAt}");
        }

        $this->newLine();

        // Choose selection method
        $method = select(
            label: 'How would you like to select a user?',
            options: [
                'recent' => 'Select from recent registrations',
                'search' => 'Search by name or email',
                'delete' => '🗑️  Delete fake/spam registrations',
            ]
        );

        if ($method === 'recent') {
            $options = $recentUsers->mapWithKeys(fn ($user) => [
                $user->id => "{$user->name} ({$user->email})",
            ])->toArray();

            $userId = select(
                label: 'Select user to activate:',
                options: $options
            );

            $user = $recentUsers->find($userId);
            if (! $user) {
                $this->error('User not found.');

                return;
            }
        } elseif ($method === 'search') {
            $userId = search(
                label: 'Search for user to activate',
                placeholder: 'Type name or email...',
                options: fn (string $value) => strlen($value) > 1
                    ? User::withInactive()
                        ->whereNull('activated_at')
                        ->where(function ($query) use ($value) {
                            $query->where('name', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%");
                        })
                        ->limit(8)
                        ->get()
                        ->mapWithKeys(fn ($user) => [
                            $user->id => "{$user->name} ({$user->email})",
                        ])
                        ->toArray()
                    : []
            );

            if (! $userId) {
                info('No user selected.');

                return;
            }

            $user = User::withInactive()->find($userId);
            if (! $user) {
                $this->error('User not found.');

                return;
            }
        } else {
            // Delete mode
            $this->warn('⚠️  DELETE MODE: This will permanently remove users');

            $deleteMethod = select(
                label: 'Select users to delete:',
                options: [
                    'recent' => 'Choose from recent registrations',
                    'search' => 'Search by name or email',
                ]
            );

            if ($deleteMethod === 'recent') {
                $options = $recentUsers->mapWithKeys(fn ($user) => [
                    $user->id => "{$user->name} ({$user->email})",
                ])->toArray();

                $userId = select(
                    label: 'Select user to DELETE:',
                    options: $options
                );

                $user = $recentUsers->find($userId);
                if (! $user) {
                    $this->error('User not found.');

                    return;
                }
            } else {
                $userId = search(
                    label: 'Search for user to DELETE',
                    placeholder: 'Type name or email...',
                    options: fn (string $value) => strlen($value) > 1
                        ? User::withInactive()
                            ->whereNull('activated_at')
                            ->where(function ($query) use ($value) {
                                $query->where('name', 'like', "%{$value}%")
                                    ->orWhere('email', 'like', "%{$value}%");
                            })
                            ->limit(8)
                            ->get()
                            ->mapWithKeys(fn ($user) => [
                                $user->id => "{$user->name} ({$user->email})",
                            ])
                            ->toArray()
                        : []
                );

                if (! $userId) {
                    info('No user selected.');

                    return;
                }

                $user = User::withInactive()->find($userId);
                if (! $user) {
                    $this->error('User not found.');

                    return;
                }
            }

            $confirmed = confirm(
                label: "⚠️  PERMANENTLY DELETE user: {$user->name} ({$user->email})?",
                default: false
            );

            if (! $confirmed) {
                info('User deletion cancelled.');

                return;
            }

            $user->delete();

            $this->error("🗑️  User {$user->name} ({$user->email}) has been permanently deleted!");

            return;
        }

        $confirmed = confirm(
            label: "Activate user: {$user->name} ({$user->email})?",
            default: true
        );

        if (! $confirmed) {
            info('User activation cancelled.');

            return;
        }

        $user->activate();

        $this->info("✅ User {$user->name} ({$user->email}) has been activated successfully!");
    }
}
