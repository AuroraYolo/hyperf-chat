<?php
declare(strict_types = 1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration as BaseMigration;

/**
 * Class GroupRelation
 */
class GroupRelationTable extends BaseMigration
{

    const TABLE = 'group_relation';

    /**
     * @return void
     */
    public function up() : void
    {
        Schema::create(self::TABLE, function (Blueprint $blueprint)
        {
            $blueprint->comment('群友关系');
            $blueprint->increments('group_relation_id')->comment('主键');
            $blueprint->integer('user_id', false, true, 11)->comment('用户id');
            $blueprint->integer('group_id', false, true, 11)->comment('群id');
            $blueprint->timestamps();
            //            $blueprint->tinyInteger('delete_flag', false, true, 1)->default(0)->comment('软删除 0正常 1删除');
            $blueprint->softDeletes()->comment('删除时间 为NULL未删除');
            $blueprint->index('user_id');
            $blueprint->engine  = 'Innodb';
            $blueprint->charset = 'utf8mb4';
        });
    }

    /**
     * @return void
     */
    public function down() : void
    {
        Schema::drop(self::TABLE);
    }
}
