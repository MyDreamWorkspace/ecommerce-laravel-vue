<?php

namespace App\Models;

use App\Notifications\SendVerifyWithQueueNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser, HasName, HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'status',
        'email',
        'email_verified_at',
        'phone',
        'password',
        'provider',
        'provider_id',
        'provider_token',
    ];

    protected $appends = ['avatar'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new SendVerifyWithQueueNotification());
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function socials(): HasMany
    {
        return $this->hasMany(UserSocial::class);
    }

    public function orderRecipients(): HasMany
    {
        return $this->hasMany(OrderRecipient::class);
    }

    public function canAccessFilament(): bool
    {
        return str_ends_with($this->email, '@store.com') && $this->hasVerifiedEmail();
    }

    public function getFilamentName(): string
    {
        return "$this->first_name $this->last_name";
    }

    public function avatar(): Attribute
    {
        return Attribute::get(fn($value) => $this->getFirstMediaUrl('avatars'));
    }

    /**
     * @throws FileCannotBeAdded|FileDoesNotExist|FileIsTooBig
     */
    public function addAvatarMedia(string $url, string $collectionName = 'avatars', string $diskName = 'public')
    {
        $this->clearMediaCollection($collectionName)
            ->addMediaFromUrl($url)
            ->sanitizingFileName(fn($fileName) => strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName)))
            ->toMediaCollection($collectionName, $diskName);
    }
}
