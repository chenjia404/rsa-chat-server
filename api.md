业务流程
===
1. 先创建的一个rsa证书。
2. 使用login请求登录，其中传入rsa的公钥、房间号、昵称，会返回reply_say。
3. 登录成功后，使用say请求发言，参数msg，表示发言内容。

接口列表
===
login接口(rsa验证类型)

请求参数：

* type：login

* room_id：房间号，一般为整型数字。

* client_name：用户昵称，16位长度内的字符串

* rsa_public_key：rsa公钥，格式如下：
```
-----BEGIN PUBLIC KEY----- 
MIIBITANBgkqhkiG9w0BAQEFAAOCAQ4AMIIBCQKCAQBRTvI1wg6I7tpOuMgdnyfc
AgMBAAa=
-----END PUBLIC KEY-----
```
返回参数：

* type：reply_login 或者 error

* client_name：client_name字段是当前用户的实际昵称，因为用户昵称重复的时候，服务器会自动加随机数

* client_id：唯一id，用于私聊时的指定id。

* created_at：服务器当前时间。

* client_list：当前聊天室用户列表，数据类型是一个数组，格式client_id：client_name。

如果登录成功，会返回reply_login类型响应，如果失败会返回error。


发言接口

请求参数：

* msg：发言内容

返回参数：

* type：say

* from_client_id：用户唯一id，如果是当前登录用户，则是自己的发送的。

* user：一个数组，主要用昵称和头像字段。

* to_client_id：默认是全部(all)，如果是私聊，就会是当前用户的client_id。

* content：聊天内容。

* created_at：服务器当前时间。

如果成功调用，返回say响应，要注意的是，其它用户发言也是该相应。

消息格式
======

消息格式为json,每种消息都有一个type字段区分类型。


通用返回结构:
```
{
    "code":0,
    "msg":"成功登录",
    "data":{
        "type":"reply_login",
        "language":"zh-cn",
        "username":"chenjia404"
    }
}
```

聊天内容样例:
```
{
    "code":0,
    "msg":"成功登录",
    "data":
    {
        "type":"say",
        "msg_id":"423423452353252354",
        "username":"chenjia404",
        "content":"Hello, World!",
        "created_at":1508157282,
        "img":"https://tva3.sinaimg.cn/crop.19.12.155.155.180/659c6c35gw1f3swxjt6ooj2050050q30.jpg"
        "attachment":
        {
            "name":"rar.zip",
            "link":"https://cn.vuejs.org/v2/guide/class-and-style.html"
        }
    } 
}
```
code 字段为0则表示成功,非零 code 表示错误,每一个类型的错误使用唯一的错误码。

msg 字段为提示信息，需要对用户优化，可以直接提示给用户，支持多语言。

data 字段为实际返回字段，根据不同数据做不同的处理。

错误码
===
101 同一聊天室昵称不能重复

102 聊天室房间id不能为空

103 rsa登录验证失败

104 该昵称已经被占用

201 发言速度过快