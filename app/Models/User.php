<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string|null $telegram_nickname
 * @property string|null $instagram_nickname
 * @property string $phone
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property \Illuminate\Support\Carbon|null $club_entry_date
 * @property string|null $occupation_description
 * @property \Illuminate\Support\Carbon|null $phone_verified_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $approve_verified_at
 * @property int $active
 * @property string $password
 * @property string|null $profile_photo_path
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property City $cities
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Session[] $sessions
 * @property-read \Illuminate\Database\Eloquent\Collection|Car[] $cars
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasRoles;


    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'telegram_nickname',
        'instagram_nickname',
        'phone',
        'email',
        'birth_date',
        'club_entry_date',
        'occupation_description',
        'password',
        'profile_photo_path',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    // Если вам нужно преобразовать дату в формат Carbon:
    protected $dates = [
        'birth_date',
        'club_entry_date',
        'phone_verified_at',
        'email_verified_at',
        'approve_verified_at',
        'created_at',
        'updated_at'
    ];

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
        ];
    }

    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class);
    }


    public function setPassword(string $password): void
    {
        $this->password = Hash::make($password);
    }

    public function setPhone(string $phone): void
    {
        $this->phone = formatPhoneNumber($phone);
    }
}
