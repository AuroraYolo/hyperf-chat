<!DOCTYPE html>
<html lang="en">
@include('chat/header', ['title' => '登录'])
<style>
    .father{
        width:1000px;
        height:auto;
        margin:0 auto;
    }

    .layui-input{
        width:300px;
    }

    .login-main{
        margin-top:230px;
        margin-left:350px;
        width:300px;
        height:400px; /* border:1px solid #e6e6e6; */
    }

    .layui-form{
        margin-top:20px;
    }

    .layui-input-inline{
        margin-top:30px;
    }

    button{
        width:300px;
    }
</style>
<body>

<div class="father">
    <div class="login-main">
        <p style="color:#009688;font-size:25px;text-align:center;">欢迎登录</p>
        <form class="layui-form">
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="email" required lay-verify="required|email" placeholder="请输入邮箱"
                       autocomplete="off"
                       class="layui-input">
            </div>
            <br>
            <div class="layui-input-inline">
                <input type="password" name="password" required lay-verify="required" placeholder="请输入密码" autocomplete="off"
                       class="layui-input">
            </div>
            <br>
            <div class="layui-input-inline login-btn">
                <button lay-submit lay-filter="login" class="layui-btn">登录</button>
            </div>
            <hr/>

            <p><a href="/index/register" class="fl">立即注册</a></p>
        </form>
    </div>
</div>

<script type="module">
  import {user_login, user_home} from '/chat/js/api.js';
  import {postRequest} from '/chat/js/request.js';

  layui.use(['form', 'layer', 'jquery'], function(){
    var form = layui.form;
    form.on('submit(login)', function(data){
      postRequest(user_login, data.field, function(data){
        setTimeout(function(){
          location.href = user_home;
        }, 1000);
      });
      return false;
    })
  });
</script>
</body>
</html>
