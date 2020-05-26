## 感谢sl-im作者提供的demo,自己就是想写一套im,用hyperf重写.
# [sl-im](https://github.com/gaobinzhan/sl-im) 
# [hyperf-im](https://github.com/inocturne/hyperf-chat)
<p align="center">
    <a href="https://github.com/inocturne/hyperf-chat" target="_blank">
        <img src="https://static.jayjay.cn/1496800949298.jpg"/>
    </a>
</p>

[![Php Version](https://img.shields.io/badge/php-%3E=7.2-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=4.4.16-brightgreen.svg?maxAge=2592000)](https://github.com/swoole/swoole-src)
[![sl-im License](https://img.shields.io/github/license/hyperf/hyperf.svg?maxAge=2592000)](https://github.com/inocturne/hyperf-chat/blob/master/LICENSE)


## 简介
 
[hyperf-im](https://im.jayjay.cn) 是基于 [Hyperf](https://hyperf.io) 微服务协程框架和 [Layim](https://www.layui.com/layim/) 网页聊天系统 所开发出来的聊天室。

## 体验地址

[hyperf-im](https://im.jayjay.cn) https://im.jayjay.cn

## 功能

- 登录注册（Http）
- 单点登录（Websocket）
- 私聊（Websocket）
- 群聊（Websocket）
- 在线人数（Websocket）
- 获取未读消息（Websocket）
- 好友在线状态（Websocket）
- 好友 查找 添加 同意 拒绝（Http+Websocket）
- 群 创建 查找 添加 同意 拒绝（Http+Websocket）
- 聊天记录存储
- 心跳检测
- 消息重发
- 断线重连
- 发送图片及文件

## Requirement

- [PHP 7.2+](https://github.com/php/php-src/releases)
- [Swoole 4.4.16+](https://github.com/swoole/swoole-src/releases)
- [Composer](https://getcomposer.org/)
- [Hyperf >= 1.1.x](https://github.com/hyperf/hyperf/releases)



## 部署方式

### Composer

```bash
composer update
```

### env配置

`vim .env`

```bash
WS_URL=wss://im.jayjay.cn/im
STORAGE_IMG_URL=
STORAGE_FILE_URL=
```
### nginx配置

```bash
server{
    listen 80;
    server_name im.jayjay.cn;
    return 301 https://$server_name$request_uri;
}

server{
    listen 443 ssl;
    root /data/wwwroot/;
    add_header Strict-Transport-Security "max-age=31536000";
    server_name im.jayjay.cn;
    access_log /data/wwwlog/im.jayjay.cn.access.log;
    error_log /data/wwwlog/im.jayjay.cn.error.log;
    client_max_body_size 100m;
    ssl_certificate /etc/nginx/ssl/full_chain.pem;
    ssl_certificate_key /etc/nginx/ssl/private.key;
    ssl_session_timeout 5m;
    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:HIGH:!aNULL:!MD5:!RC4:!DHE;
    location / {
        proxy_pass http://127.0.0.1:9501;
        proxy_set_header Host $host:$server_port;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Real-PORT $remote_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
   
    location /im {
        proxy_pass http://127.0.0.1:9502;
        proxy_http_version 1.1;
        proxy_read_timeout   3600s;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
   
    location ~ .*\.(js|ico|css|ttf|woff|woff2|png|jpg|jpeg|svg|gif|htm)$ {
        root /data/wwwroot/IM/public;
    }
}
```

### Start

- 挂起

```bash
php bin/hyperf.php start
```


## TODO

1.完善整体项目
2.加入webrtc(视频聊天)


## 联系方式

- WeChat：naicha_1994
- QQ：847050412

## License

[LICENSE](LICENSE)
