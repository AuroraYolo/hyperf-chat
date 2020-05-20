<?php
declare(strict_types = 1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration as BaseMigration;

/**
 * Class FriendRelation
 */
class FriendRelationTable extends BaseMigration
{
    const TABLE = 'friend_relation';

    /**
     * @return void
     */
    public function up() : void
    {
        Schema::create(self::TABLE, function (Blueprint $blueprint)
        {
            $blueprint->comment('好友关系');
            $blueprint->increments('friend_relation_id')->comment('主键');
            $blueprint->integer('user_id', false, true, 11)->comment('用户id');
            $blueprint->integer('friend_id', false, true, 11)->comment('好友id');
            $blueprint->integer('friend_group_id', false, true, 11)->comment('好友所属分组id');
            $blueprint->timestamps();
            //            $blueprint->tinyInteger('delete_flag', false, true, 1)->default(0)->comment('软删除 0正常 1删除');
            $blueprint->softDeletes()->comment('删除时间 为NULL未删除');
            $blueprint->index('user_id');
            $blueprint->index('friend_id');
            $blueprint->index('friend_group_id');
            $blueprint->engine  = 'Innodb';
            $blueprint->charset = 'utf8mb4';
        });
    }

    /**
     * @return void
     */
    public function down() : void
    {
        Schema::dropIfExists(self::TABLE);
    }
}
