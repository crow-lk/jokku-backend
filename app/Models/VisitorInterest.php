<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorInterest extends Model
{
    /** @use HasFactory<\Database\Factories\VisitorInterestFactory> */
    use HasFactory;

    public const TYPE_CUSTOMER_IDEA = 'customer_idea';

    public const TYPE_INVESTOR = 'investor';

    public const TYPE_PARTNERSHIP = 'partnership';

    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_QUALIFIED = 'qualified';

    public const STATUS_ARCHIVED = 'archived';

    public const SOURCE_WEB = 'web';

    public const SOURCE_CALL_CENTER = 'call_center';

    public const SOURCE_ADMIN = 'admin';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'interest_type',
        'status',
        'source',
        'name',
        'email',
        'phone',
        'company',
        'role',
        'location',
        'investment_range',
        'partnership_area',
        'message',
        'created_by_user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_by_user_id' => 'integer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_CUSTOMER_IDEA => 'Customer idea',
            self::TYPE_INVESTOR => 'Investor',
            self::TYPE_PARTNERSHIP => 'Partnership',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_QUALIFIED => 'Qualified',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_WEB => 'Web',
            self::SOURCE_CALL_CENTER => 'Call center',
            self::SOURCE_ADMIN => 'Admin',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
