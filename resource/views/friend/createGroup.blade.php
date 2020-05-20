<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <style type="text/css">
    .layui-find-list li img {
      position: absolute;
      left: 15px;
      top: 8px;
      width: 36px;
      height: 36px;
      border-radius: 100%;
    }

    .layui-find-list li {
      position: relative;
      height: 90px;;
      padding: 5px 15px 5px 60px;
      font-size: 0;
      cursor: pointer;
    }

    .layui-find-list li * {
      display: inline-block;
      vertical-align: top;
      font-size: 14px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .layui-find-list li span {
      margin-top: 4px;
      max-width: 155px;
    }

    .layui-find-list li p {
      display: block;
      line-height: 18px;
      font-size: 12px;
      color: #999;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .back {
      cursor: pointer;
    }

    .lay_page {
      position: fixed;
      bottom: 0;
      margin-left: -15px;
      margin-bottom: 20px;
      background: #fff;
      width: 100%;
    }

    .layui-laypage {
      width: 105px;
      margin: 0 auto;
      display: block
    }

    .pt30 {
      padding-top: 30px;
    }
  </style>
</head>
@include('chat/header', ['title' => '创建好友分组'])
<body>
<form class="layui-form layui-row" style="margin-top: 15px;" action="">
  <div class="layui-form-item layui-col-xs11">
    <label class="layui-form-label">分组名称</label>
    <div class="layui-input-block">
      <input type="text" name="friend_group_name" id="groupName" lay-verify="required" autocomplete="off"
             placeholder="请输入好友分组名称" class="layui-input">
    </div>
  </div>
  <div class="layui-form-item layui-col-xs10 layui-col-xs-offset1" style="padding-top: 30px;">
    <div class="layui-input-block">
      <a lay-submit lay-filter="createFriendGroup" class="layui-btn">确认创建</a>
      <button type="reset" class="layui-btn layui-btn-primary">重置</button>
    </div>
  </div>
</form>

</body>
</html>


<script type="module">
  import {friend_create_group} from '/chat/js/api.js';
  import {postRequest} from '/chat/js/request.js';
  import {addFriendGroup} from '/chat/js/panel.js';


  layui.use(['form', 'layer', 'jquery'], function () {
    var form = layui.form;
    form.on('submit(createFriendGroup)', function (data) {
      postRequest(friend_create_group, data.field, function (data) {
        addFriendGroup(data);
        setTimeout(function () {
          let index = parent.layer.getFrameIndex(window.name);
          parent.layer.close(index);
        }, 1000)
      });
      return false;
    })
  });
</script>
