<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Space extends Model
{
    use HasFactory;

    protected $table = 'spaces';

    protected $fillable = [
        'governance_type', 'category', 'subcategory', 'featured'
    ];

    protected $casts = [
        'featured' => 'boolean',
    ];

    public const GOVERNANCE_HOSTED = 'hosted';
    public const GOVERNANCE_SELF_MANAGED = 'self-managed';

    public const GOVERNANCE_TYPES = [
        self::GOVERNANCE_HOSTED => 'Hosted Coliving Space',
        self::GOVERNANCE_SELF_MANAGED => 'Self-Managed Coliving Group',
    ];

    // Main categories (MANDATORY - exactly one per space)
    public const CATEGORIES = [
        'managed-coliving' => 'Managed Coliving',
        'self-managed' => 'Self-managed Space',
        'shared-housing' => 'Shared Housing',
        'student-housing' => 'Student Housing',
        'digital-nomad' => 'Digital Nomad Coliving',
        'cohousing' => 'Cohousing / Communal Living',
        'senior-coliving' => 'Senior Coliving',
        'short-term' => 'Short-Term Coliving',
    ];

    // Subcategories by main category (OPTIONAL)
    public const SUBCATEGORIES = [
        'shared-housing' => [
            'flat-apartment' => 'Flat / Apartment',
            'house-villa' => 'House / Villa',
            'private-room' => 'Private Room',
            'shared-room' => 'Shared Room',
        ],
        'student-housing' => [
            'university-residence' => 'University Residence',
            'private-student-housing' => 'Private Student Housing',
            'exchange-erasmus' => 'Exchange / Erasmus',
            'researcher-phd' => 'Researcher / PhD',
        ],
        'digital-nomad' => [
            'work-first' => 'Work-first',
            'community-first' => 'Community-first',
            'builder-house' => 'Builder house',
            'travel-first' => 'Travel-first',
        ],
        'managed-coliving' => [
            'private-rooms' => 'Private rooms',
            'studios-micro-units' => 'Studios / micro-units',
            'mixed-units' => 'Mixed units',
            'serviced-apartments' => 'Serviced apartments',
            'corporate-housing' => 'Corporate Housing',
            'subscription-membership' => 'Subscription / membership model',
        ],
        'cohousing' => [
            'cohousing-formal' => 'Cohousing (Formal)',
            'commune-informal' => 'Commune (Informal)',
            'eco-community' => 'Eco Community',
            'intentional-community' => 'Intentional Community',
        ],
        'senior-coliving' => [
            'independent-living' => 'Independent Living',
            'assisted-living' => 'Assisted Living',
            'intergenerational' => 'Intergenerational',
            '55-community' => '55+ Community',
        ],
        'short-term' => [
            'seasonal' => 'Seasonal',
            'retreat-based' => 'Retreat-Based',
            'pop-up' => 'Pop-Up',
            'project-based' => 'Project-Based',
        ],
        'self-managed' => [
            'popup-space' => 'Popup Space',
            'temporary-community' => 'Temporary Community',
            'resident-led-house' => 'Resident-led House',
            'fixed-dates' => 'Fixed Dates',
        ],
    ];

    public static function findByUidOrUsername(?string $identifier): ?self
    {
        if ($identifier === null || $identifier === '') {
            return null;
        }

        $space = static::where('uid', $identifier)->first();
        if ($space) {
            return $space;
        }

        return static::where('username', $identifier)->first();
    }

    public function photos()
    {
        return $this->hasMany(Photo::class, 'space_uid', 'uid');
    }

    public function orderedPhotos()
    {
        return Photo::query()
            ->where('space_uid', $this->uid)
            ->orderByRaw('CASE WHEN pos > 0 THEN pos ELSE 999999 END')
            ->orderBy('id')
            ->get(['id', 'filename', 'pos']);
    }

    public function ratings()
    {
        return $this->hasMany(SpaceRating::class);
    }

    public function getCategoryLabelAttribute()
    {
        return self::CATEGORIES[$this->category] ?? null;
    }

    public function getSubcategoryLabelAttribute()
    {
        if (!$this->category || !$this->subcategory) {
            return null;
        }
        return self::SUBCATEGORIES[$this->category][$this->subcategory] ?? null;
    }
}
