<?php

declare (strict_types=1);
namespace App\Model;

use Hyperf\DbConnection\Model\Model;
/**
 * @property int $id 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property string $email 
 * @property string $password 
 * @property int $status 
 * @property string $sign 
 * @property string $avatar 
 * @property string $deleted_at 
 * @property string $username 
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'created_at', 'updated_at', 'email', 'password', 'status', 'sign', 'avatar', 'deleted_at', 'username'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'status' => 'integer'];
    const STATUS_ONLINE = 1;
    const STATUS_OFFLINE = 0;
    const STATUS_TEXT = ['hide', 'online'];
}