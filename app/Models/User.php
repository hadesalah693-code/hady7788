<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\VerificationStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'is_admin',
    'account_type',
    'status',
    'phone',
    'whatsapp',
    'bio',
    'avatar_path',
    'governorate_id',
    'city_id',
    'last_login_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'account_type' => AccountType::class,
            'status' => AccountStatus::class,
            'last_login_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin && ! $this->isSuspended();
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isBroker(): bool
    {
        return $this->account_type === AccountType::Broker;
    }

    public function isCompany(): bool
    {
        return $this->account_type === AccountType::Company;
    }

    public function isRegularUser(): bool
    {
        return ($this->account_type ?? AccountType::User) === AccountType::User;
    }

    public function isSuspended(): bool
    {
        return $this->status === AccountStatus::Suspended;
    }

    public function profileVerificationStatus(): ?VerificationStatus
    {
        if ($this->isBroker()) {
            return $this->brokerProfile?->verification_status;
        }

        if ($this->isCompany()) {
            return $this->companyProfile?->verification_status;
        }

        return null;
    }

    /**
     * A regular user is always verified. Brokers and companies are verified only
     * after an administrator approves their professional profile.
     */
    public function isVerifiedAccount(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isRegularUser()) {
            return true;
        }

        if ($this->status !== AccountStatus::Active) {
            return false;
        }

        return $this->profileVerificationStatus() === VerificationStatus::Verified;
    }

    public function canPublishProperties(): bool
    {
        return $this->isAdmin()
            || (($this->isBroker() || $this->isCompany()) && $this->isVerifiedAccount());
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function brokerProfile(): HasOne
    {
        return $this->hasOne(BrokerProfile::class);
    }

    public function companyProfile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
