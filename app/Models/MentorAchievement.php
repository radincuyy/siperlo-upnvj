<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $mentor_id
 * @property string $competition_name
 * @property string|null $student_name
 * @property string|null $result
 * @property int|null $year
 */
class MentorAchievement extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'mentor_id',
        'competition_name',
        'student_name',
        'result',
        'year',
    ];

    /**
     * @return BelongsTo<Mentor, $this>
     */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Mentor::class);
    }
}
