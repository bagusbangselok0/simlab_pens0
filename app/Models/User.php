<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravolt\Avatar\Facade as Avatar;

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
        'signature_status',
        'signature_rejection_note',
        'signature_verified_at',
        'signature_verified_by',
        'photo',
        'is_verified',
        'is_active',
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
            'signature_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
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
        if ($this->photo && file_exists(public_path('storage/photo_profile/' . $this->photo))) {
            return asset('storage/photo_profile/' . $this->photo);
        }

        $displayName = trim(implode(' ', array_filter([
            $this->gelar_depan,
            $this->nama_asli,
            $this->gelar_belakang,
        ])));

        $displayName = $displayName ?: ($this->email ?: 'User');

        return Avatar::create($displayName)
            ->setFont(public_path('fonts/Poppins-Medium.ttf'))
            ->toBase64();
    }

    // Accessor untuk signature path
    public function getSignatureUrlAttribute()
    {
        return $this->signature_path ? asset('storage/signatures/' . $this->signature_path) : asset('images/default/image-empty.png');
    }

    // Accessor label & badge status TTD
    public function getSignatureStatusLabelAttribute(): string
    {
        return match ($this->signature_status) {
            'pending' => 'Menunggu Persetujuan Admin',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Belum Upload Tanda Tangan',
        };
    }

    public function getSignatureStatusBadgeClassAttribute(): string
    {
        return match ($this->signature_status) {
            'pending' => 'badge bg-warning text-dark',
            'approved' => 'badge bg-success',
            'rejected' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'signature_verified_by');
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
