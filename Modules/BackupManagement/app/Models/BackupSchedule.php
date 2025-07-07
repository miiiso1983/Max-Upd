<?php

namespace Modules\BackupManagement\app\Models;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Cron\CronExpression;

class BackupSchedule extends Model
{
    use HasFactory;

    protected $connection = 'mysql'; // Use landlord database

    protected $fillable = [
        'tenant_id',
        'name',
        'backup_type',
        'frequency',
        'cron_expression',
        'preferred_time',
        'days_of_week',
        'day_of_month',
        'is_active',
        'retention_days',
        'max_backups',
        'compress_backup',
        'encrypt_backup',
        'notification_settings',
        'backup_options',
        'last_run_at',
        'next_run_at',
        'successful_runs',
        'failed_runs',
        'last_error',
        'created_by',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'notification_settings' => 'array',
        'backup_options' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'is_active' => 'boolean',
        'compress_backup' => 'boolean',
        'encrypt_backup' => 'boolean',
        'preferred_time' => 'datetime:H:i:s',
    ];

    // Frequency constants
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_CUSTOM = 'custom';

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function backups(): HasMany
    {
        return $this->hasMany(TenantBackup::class, 'tenant_id', 'tenant_id')
                    ->where('trigger_type', TenantBackup::TRIGGER_SCHEDULED);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->where('is_active', true)
                    ->where('next_run_at', '<=', now());
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Methods
     */
    public function calculateNextRun()
    {
        if (!$this->is_active) {
            $this->next_run_at = null;
            return;
        }

        $now = now();
        
        switch ($this->frequency) {
            case self::FREQUENCY_DAILY:
                $this->next_run_at = $now->copy()
                    ->setTimeFromTimeString($this->preferred_time)
                    ->addDay();
                break;

            case self::FREQUENCY_WEEKLY:
                $this->next_run_at = $this->calculateNextWeeklyRun($now);
                break;

            case self::FREQUENCY_MONTHLY:
                $this->next_run_at = $this->calculateNextMonthlyRun($now);
                break;

            case self::FREQUENCY_CUSTOM:
                if ($this->cron_expression) {
                    $cron = new CronExpression($this->cron_expression);
                    $this->next_run_at = $cron->getNextRunDate($now);
                }
                break;
        }

        $this->save();
    }

    private function calculateNextWeeklyRun($now)
    {
        if (!$this->days_of_week || empty($this->days_of_week)) {
            // Default to same day next week
            return $now->copy()
                ->setTimeFromTimeString($this->preferred_time)
                ->addWeek();
        }

        $daysOfWeek = $this->days_of_week;
        sort($daysOfWeek);

        $currentDayOfWeek = $now->dayOfWeek;
        $targetTime = $now->copy()->setTimeFromTimeString($this->preferred_time);

        // Find next scheduled day
        foreach ($daysOfWeek as $day) {
            if ($day > $currentDayOfWeek || ($day == $currentDayOfWeek && $targetTime->isFuture())) {
                return $now->copy()
                    ->startOfWeek()
                    ->addDays($day)
                    ->setTimeFromTimeString($this->preferred_time);
            }
        }

        // If no day found this week, use first day of next week
        return $now->copy()
            ->startOfWeek()
            ->addWeek()
            ->addDays($daysOfWeek[0])
            ->setTimeFromTimeString($this->preferred_time);
    }

    private function calculateNextMonthlyRun($now)
    {
        $dayOfMonth = $this->day_of_month ?? $now->day;
        $targetTime = $now->copy()->setTimeFromTimeString($this->preferred_time);

        $nextRun = $now->copy()
            ->startOfMonth()
            ->addDays($dayOfMonth - 1)
            ->setTimeFromTimeString($this->preferred_time);

        // If the target day has passed this month or is today but time has passed
        if ($nextRun->isPast()) {
            $nextRun->addMonth();
            
            // Handle months with fewer days
            while ($nextRun->day != $dayOfMonth && $nextRun->day < $dayOfMonth) {
                $nextRun->addMonth()->startOfMonth()->addDays($dayOfMonth - 1);
            }
        }

        return $nextRun;
    }

    public function isDue()
    {
        return $this->is_active && 
               $this->next_run_at && 
               $this->next_run_at->isPast();
    }

    public function markAsRun($success = true, $error = null)
    {
        if ($success) {
            $this->successful_runs++;
            $this->last_error = null;
        } else {
            $this->failed_runs++;
            $this->last_error = $error;
        }

        $this->last_run_at = now();
        $this->calculateNextRun();
    }

    public function getSuccessRateAttribute()
    {
        $totalRuns = $this->successful_runs + $this->failed_runs;
        
        if ($totalRuns === 0) {
            return 0;
        }

        return round(($this->successful_runs / $totalRuns) * 100, 2);
    }

    public function getFrequencyLabelAttribute()
    {
        return match($this->frequency) {
            self::FREQUENCY_DAILY => 'Daily',
            self::FREQUENCY_WEEKLY => 'Weekly',
            self::FREQUENCY_MONTHLY => 'Monthly',
            self::FREQUENCY_CUSTOM => 'Custom',
            default => 'Unknown',
        };
    }

    public function getNextRunHumanAttribute()
    {
        if (!$this->next_run_at) {
            return 'Not scheduled';
        }

        return $this->next_run_at->diffForHumans();
    }

    public function getLastRunHumanAttribute()
    {
        if (!$this->last_run_at) {
            return 'Never';
        }

        return $this->last_run_at->diffForHumans();
    }

    /**
     * Static methods
     */
    public static function getDueSchedules()
    {
        return static::due()->get();
    }

    public static function createDefaultSchedule($tenantId, $createdBy = null)
    {
        return static::create([
            'tenant_id' => $tenantId,
            'name' => 'Daily Backup',
            'backup_type' => TenantBackup::TYPE_FULL,
            'frequency' => self::FREQUENCY_DAILY,
            'preferred_time' => '02:00:00',
            'is_active' => true,
            'retention_days' => 30,
            'max_backups' => 10,
            'compress_backup' => true,
            'encrypt_backup' => true,
            'notification_settings' => [
                'email_on_success' => false,
                'email_on_failure' => true,
                'recipients' => [],
            ],
            'created_by' => $createdBy,
        ]);
    }
}
