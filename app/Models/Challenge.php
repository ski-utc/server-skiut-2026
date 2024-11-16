<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Challenge extends Model
{
    /** @use HasFactory<\Database\Factories\ChallengeFactory> */
    use HasFactory;

    // UUID handling configuration
    protected $keyType = 'string'; // Set UUID type as string
    public $incrementing = false;  // Disable auto-incrementing of the primary key
    
    protected $table = 'challenges';  // Table name
    protected $fillable = ['title', 'nbPoints'];  // Fields to allow mass assignment

    // Define the one-to-many relationship with ChallengeProof
    public function challengeProofs()
    {
        return $this->hasMany(ChallengeProof::class);
    }

    /**
     * Boot the model to assign a UUID when creating a new record.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically generate UUID when creating the model
        static::creating(function ($model) {
            $model->id = (string) Str::uuid(); // Generate a UUID as string
        });
    }
}
