<?php

declare (strict_types = 1);
namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int            $id
 * @property int            $uid
 * @property int            $receiver_id
 * @property int            $group_id
 * @property int            $application_type
 * @property int            $application_status
 * @property string         $application_reason
 * @property int            $read_state
 * @property string         $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserApplication extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_application';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'uid', 'receiver_id', 'group_id', 'application_type', 'application_status', 'application_reason', 'read_state', 'deleted_at', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'uid' => 'integer', 'receiver_id' => 'integer', 'group_id' => 'integer', 'application_type' => 'integer', 'application_status' => 'integer', 'read_state' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    const APPLICATION_STATUS_CREATE = 0;
    const APPLICATION_STATUS_ACCEPT = 1;
    const APPLICATION_STATUS_REFUSE = 2;

    const UN_READ = 0;
    const ALREADY_READ = 1;

    const APPLICATION_STATUS_TEXT = [
        '等待验证',
        '已同意',
        '已拒绝'
    ];

    const APPLICATION_CREATE_USER = 'create';
    const APPLICATION_RECEIVER_USER = 'receiver';
    const APPLICATION_SYSTEM = 'system';

    const APPLICATION_TYPE_FRIEND = 'friend';
    const APPLICATION_TYPE_GROUP = 'group';
}
