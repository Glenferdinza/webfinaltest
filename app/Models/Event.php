<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    // Konstanta untuk organizer type
    const ORGANIZER_INDIVIDUAL = 'individual';
    const ORGANIZER_ORGANIZATION = 'organization';
    const ORGANIZER_COMPANY = 'company';
    const ORGANIZER_GOVERNMENT = 'government';
    const ORGANIZER_COMMUNITY = 'community';

    protected $fillable = [
        'title', 
        'description', 
        'start_date', 
        'end_date',
        'category', 
        'location', 
        'contact_email', 
        'contact_phone',
        'max_participants', 
        'capacity', 
        'registration_fee',
        'additional_info', 
        'organizer_type',
        'organizer_name', // Tambahan untuk nama organizer
        'image', 
        'image_url',
        'user_id', 
        'created_by', 
        'is_active', 
        'is_featured',
        'organizer'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_fee' => 'decimal:2',
        'max_participants' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean'
    ];

    // Static method untuk mendapatkan organizer types
    public static function getOrganizerTypes()
    {
        return [
            self::ORGANIZER_INDIVIDUAL => 'Individual',
            self::ORGANIZER_ORGANIZATION => 'Organisasi',
            self::ORGANIZER_COMPANY => 'Perusahaan',
            self::ORGANIZER_GOVERNMENT => 'Pemerintah',
            self::ORGANIZER_COMMUNITY => 'Komunitas'
        ];
    }

    // Relasi dengan user (creator)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi dengan user menggunakan created_by (untuk backward compatibility)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi dengan registrations
    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    // Relasi dengan category jika ada model EventCategory
    public function eventCategory()
    {
        return $this->belongsTo(EventCategory::class, 'category', 'name');
    }

    // Accessor untuk format harga
    public function getFormattedRegistrationFeeAttribute()
    {
        if (!$this->registration_fee || $this->registration_fee <= 0) {
            return 'Gratis';
        }
        return 'Rp ' . number_format($this->registration_fee, 0, ',', '.');
    }

    // Accessor untuk nama organizer yang diformat
    public function getFormattedOrganizerAttribute()
    {
        $organizerName = $this->organizer_name ?: $this->user->name ?? $this->creator->name ?? 'Unknown';
        $organizerType = $this->getFormattedOrganizerTypeAttribute();
        
        return $organizerName . ($organizerType ? " ({$organizerType})" : '');
    }

    // Accessor untuk organizer type yang diformat
    public function getFormattedOrganizerTypeAttribute()
    {
        $types = self::getOrganizerTypes();
        return $types[$this->organizer_type] ?? ucfirst($this->organizer_type);
    }

    // Accessor untuk URL gambar
    public function getImageUrlAttribute($value)
    {
        // Jika image_url sudah ada di database
        if ($value) {
            // Jika sudah full URL, return as is
            if (str_starts_with($value, 'http')) {
                return $value;
            }
            // Jika hanya path, tambahkan domain
            return asset($value);
        }
        
        // Jika ada field image, buat URL dari storage
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        // Default image jika tidak ada
        return asset('images/default-event.jpg');
    }

    // Accessor untuk status event
    public function getStatusAttribute()
    {
        $now = now();
        
        if ($this->end_date < $now) {
            return 'completed';
        }
        
        if ($this->start_date <= $now && $this->end_date >= $now) {
            return 'ongoing';
        }
        
        return 'upcoming';
    }

    // Accessor untuk mengetahui apakah event sudah penuh
    public function getIsFullAttribute()
    {
        if (!$this->max_participants) {
            return false;
        }
        
        return $this->registrations()->where('registration_status', 'confirmed')->count() >= $this->max_participants;
    }

    // Accessor untuk sisa slot
    public function getAvailableSlotsAttribute()
    {
        if (!$this->max_participants) {
            return null; // Unlimited
        }
        
        $registered = $this->registrations()->where('registration_status', 'confirmed')->count();
        return max(0, $this->max_participants - $registered);
    }

    // Method untuk mengecek apakah organizer adalah individual
    public function isIndividualOrganizer()
    {
        return $this->organizer_type === self::ORGANIZER_INDIVIDUAL;
    }

    // Method untuk mengecek apakah organizer adalah organisasi
    public function isOrganizationOrganizer()
    {
        return in_array($this->organizer_type, [
            self::ORGANIZER_ORGANIZATION,
            self::ORGANIZER_COMPANY,
            self::ORGANIZER_GOVERNMENT,
            self::ORGANIZER_COMMUNITY
        ]);
    }

    // Scope untuk event aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk event yang akan datang
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    // Scope untuk event featured
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Scope untuk event berdasarkan category
    public function scopeByCategory($query, $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }
        return $query;
    }

    // Scope untuk event berdasarkan organizer type
    public function scopeByOrganizerType($query, $organizerType)
    {
        if ($organizerType) {
            return $query->where('organizer_type', $organizerType);
        }
        return $query;
    }

    // Scope untuk event individual organizer
    public function scopeIndividualOrganizer($query)
    {
        return $query->where('organizer_type', self::ORGANIZER_INDIVIDUAL);
    }

    // Scope untuk event organization organizer
    public function scopeOrganizationOrganizer($query)
    {
        return $query->whereIn('organizer_type', [
            self::ORGANIZER_ORGANIZATION,
            self::ORGANIZER_COMPANY,
            self::ORGANIZER_GOVERNMENT,
            self::ORGANIZER_COMMUNITY
        ]);
    }

    // Scope untuk search
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('organizer_name', 'like', '%' . $search . '%');
            });
        }
        return $query;
    }

    // Method untuk mengecek apakah user sudah register
    public function isUserRegistered($userId)
    {
        if (!$userId) {
            return false;
        }
        
        return $this->registrations()
                   ->where('user_id', $userId)
                   ->where('registration_status', 'confirmed')
                   ->exists();
    }

    // Method untuk mengecek kepemilikan event
    public function isOwnedBy($userId)
    {
        return ($this->user_id && $this->user_id == $userId) || 
               ($this->created_by && $this->created_by == $userId);
    }

    // Method untuk validasi organizer type
    public function validateOrganizerType()
    {
        $validTypes = array_keys(self::getOrganizerTypes());
        return in_array($this->organizer_type, $validTypes);
    }

    // Boot method untuk set default organizer type
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (!$event->organizer_type) {
                $event->organizer_type = self::ORGANIZER_INDIVIDUAL;
            }
        });
    }
}