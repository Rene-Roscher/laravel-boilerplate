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
}
