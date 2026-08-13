<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_asli',
        'email',
        'password',
        'gelar_depan',
        'gelar_belakang',
        'nip',
        'nrp',
        'jabatan_id',
        'role_id',
        'prodi_id',
        'no_hp',
        'signature_path',
        'photo',
        'last_login_at',
        'last_login_ip',
        'last_login_platform',
    ];

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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi ke Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Accessor untuk Nama Lengkap Formal
    public function getFullNameAttribute()
    {
        return "{$this->gelar_depan} {$this->nama_asli}" . ($this->gelar_belakang ? ", " . $this->gelar_belakang : "");
    }

    // Relasi ke Jabatan
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    // Accessor untuk photo path
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/photo_profile/' . $this->photo) : asset('images/photo_profile/default.png');
    }

    // Accessor untuk signature path
    public function getSignatureUrlAttribute()
    {
        return $this->signature_path ? asset('storage/signatures/' . $this->signature_path) : asset('images/default/image-empty.png');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function peminjaman()
    {
        return $this->hasMany(PeminjamanLab::class);
    }

    public function labManager()
    {
        return $this->hasMany(LabManager::class);
    }

    public function kajur()
    {
        return $this->hasMany(Jurusan::class);
    }

    public function kaprodi()
    {
        return $this->hasMany(Prodi::class);
    }
}
