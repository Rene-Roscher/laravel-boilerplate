<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;

/**
 * @mixin IdeHelperBaseModel
 */
abstract class BaseModel extends Model
{
    use HasShortflakePrimary;

    public $incrementing = false;
    protected $keyType = 'int';

    /**
     * Fields that are not mass assignable.
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

}
