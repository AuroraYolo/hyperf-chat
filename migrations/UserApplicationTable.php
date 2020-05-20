<?php
declare(strict_types = 1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration as BaseMigration;

/**
 * Class UserApplication
 */
class UserApplicationTable extends BaseMigration
{
    const TABLE = 'user_application';

    /**
     * @return void
     */
    public function up() : void
    {
        Schema::create(self::TABLE, function (Blueprint $blueprint)
        {
            $blueprint->comment('用户申请表');
            $blueprint->increments('user_application_id')->comment('主键');
            $blueprint->integer('user_id', false, true, 11)->comment('申请方');
            $blueprint->integer('receiver_id', false, true, 11)->comment('接收方');
            $blueprint->integer('group_id', false, true, 11)->comment('好友分组id || 群id');
            $blueprint->enum('application_type', ['friend', 'group'])->comment('申请类型 好友 ｜ 群');
            $blueprint->tinyInteger('application_status', false, true, 1)->default(0)->comment('申请状态 0创建 1同意 2拒绝');
            $blueprint->string('application_reason', 255)->default('')->comment('申请原因');
            $blueprint->tinyInteger('read_state', false, true, 1)->default(0)->comment('读取状态 0 未读 1 已读');
            $blueprint->timestamps();
            //            $blueprint->tinyInteger('delete_flag', false, true, 1)->default(0)->comment('软删除 0正常 1删除');
            $blueprint->softDeletes()->comment('删除时间 为NULL未删除');
            $blueprint->index('user_id');
            $blueprint->index('receiver_id');
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
