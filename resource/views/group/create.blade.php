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
@include('chat/header', ['title' => '创建群'])
<body>
<form class="layui-form layui-row" style="margin-top: 15px" action="">
  <div class="layui-form-item layui-col-xs11">
    <label class="layui-form-label">群名称</label>
    <div class="layui-input-block">
      <input type="text" name="group_name" lay-verify="required" autocomplete="off"
             placeholder="为你们的群取个给力的名字吧！" class="layui-input">
    </div>
  </div>
  <div class="layui-form-item layui-col-xs11">
    <label class="layui-form-label">群头像</label>
    <div class="layui-input-block">
      <input type="text" name="avatar" lay-verify="required|url" autocomplete="off"
             placeholder="输入url即可" class="layui-input">
    </div>
  </div>
  <div class="layui-form-item layui-col-xs11">
    <label class="layui-form-label">群简介</label>
    <div class="layui-input-block">
      <textarea name="introduction" required lay-verify="required" placeholder="简单介绍一下吧！"
                class="layui-textarea"></textarea>
    </div>
  </div>
  <div class="layui-form-item layui-col-xs11">
    <label class="layui-form-label">群规模</label>
    <div class="layui-input-block">
      <input type="radio" name="size" value="200" title="200人">
      <input type="radio" name="size" value="500" title="500人" checked>
      <input type="radio" name="size" value="1000" title="1000人">
    </div>
  </div>
  <div class="layui-form-item layui-col-xs11">
    <label class="layui-form-label">加群验证</label>
    <div class="layui-input-block">
      <input type="radio" name="validation" value="1" title="需要验证">
      <input type="radio" name="validation" value="0" title="无需验证" checked>
    </div>
  </div>
  <div class="layui-form-item layui-col-xs10 layui-col-xs-offset1" style="padding-top: 30px;">
    <div class="layui-input-block">
      <a lay-submit lay-filter="createGroup" class="layui-btn">确认创建</a>
      <button type="reset" class="layui-btn layui-btn-primary">重置</button>
    </div>
  </div>
</form>
<script type="module">
  import {group_create} from '/chat/js/api.js';
  import {postRequest} from '/chat/js/request.js';
  import {addGroup} from '/chat/js/panel.js';

  layui.use(['form', 'layer', 'jquery'], function () {
    var form = layui.form;
    form.on('submit(createGroup)', function (data) {
      postRequest(group_create, data.field, function (data) {
        addGroup(data);
        setTimeout(function () {
          let index = parent.layer.getFrameIndex(window.name);
          parent.layer.close(index);
        }, 1000)
      });
      return false;
    })
  });
</script>
</body>
</html>
