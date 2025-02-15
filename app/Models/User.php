<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $dates   = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password'          => 'hashed',
            // 'document' => 'array',
        ];
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

// Accessor in User model
    public function getImageAttribute($image)
    {
        $defaultImage = 'default_user.png';
        return asset('uploads/profile_images/' . ($image ?? $defaultImage));
    }
    public function getDocumentAttribute($document)
    {
        $documents = json_decode($document, true);
        if (is_array($documents)) {
            return array_map(function ($doc) {
                return asset('uploads/documents/' . $doc);
            }, $documents);
        }
    }
    //connection with services
    public function services()
    {
        return $this->hasMany(Services::class, 'provider_id');
    }

}
