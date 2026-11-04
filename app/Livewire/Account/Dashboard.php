<?php

namespace App\Livewire\Account;

use App\Enums\PropertyStatus;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.account')]
#[Title('لوحة حسابي')]
class Dashboard extends Component
{
    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $user->loadMissing(['governorate', 'city', 'brokerProfile', 'companyProfile']);

        return view('livewire.account.dashboard', [
            'user' => $user,
            'propertiesCount' => $user->properties()->count(),
            'publishedCount' => $user->properties()->where('status', PropertyStatus::Published)->count(),
            'latestProperties' => $user->properties()
                ->with(['coverImage', 'city', 'governorate', 'type'])
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }
}
