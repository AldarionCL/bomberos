<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasProfilePhoto;
use Couchbase\Role;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasProfilePhoto;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'idRole',
        'profile_photo_path'
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

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id', 'idUsuario');
    }

    public function role(){
        return $this->hasOne(UserRole::class, 'id', 'idRole');
    }

    public function isRole($role):bool
    {
        if(Auth::user()->role) {
            if (is_array($role)) {
                foreach ($role as $k => $r) {
                    if (Auth::user()->role->Rol == $r) {
                        return true;
                    }
                }
                return false;
            } else {
                return Auth::user()->role->Rol == $role;
            }
        } else return false;
    }
    public function isCargo($role):bool
    {
        if(Auth::user()->persona->cargo->Cargo) {
            if (is_array($role)) {
                foreach ($role as $k => $r) {
                    if (Auth::user()->persona->cargo->Cargo == $r) {
                        return true;
                    }
                }
                return false;
            } else {
                return Auth::user()->persona->cargo->Cargo == $role;
            }
        } else return false;
    }


    public function canAccessPanel(Panel $admin): bool
    {
//        return Auth::user()->isRole('Administrador');
        return true;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }

}
