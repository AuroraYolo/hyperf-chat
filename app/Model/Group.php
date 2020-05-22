<?php

declare (strict_types=1);
namespace App\Model;

use Hyperf\DbConnection\Model\Model;
/**
 * @property int $id
 * @property int $uid
 * @property string $group_name
 * @property string $avatar
 * @property int $size
 * @property string $introduction
 * @property int $validation
 * @property string $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Group extends Model
{
    const VALIDATION_NOT = 0;
    const VALIDATION_NEED = 1;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'group';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'uid', 'group_name', 'avatar', 'size', 'introduction', 'validation', 'deleted_at', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'uid' => 'integer', 'size' => 'integer', 'validation' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
