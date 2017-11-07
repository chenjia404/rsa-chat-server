// 连接服务端
function connect() {
    // 创建websocket
    ws = new WebSocket("ws://" + document.domain + ":7272");
    // 当socket连接打开时，输入用户名
    ws.onopen = onopen;
    // 当有消息时根据消息类型显示不同信息
    ws.onmessage = onmessage;
    ws.onclose = function () {
        console.log("连接关闭，定时重连");
        connect();
    };
    ws.onerror = function () {
        console.log("出现错误");
    };
}

// 连接建立时发送登录信息
function onopen() {
    if (!name) {
        show_prompt();
    }
    // 登录
    var login_data = '{"type":"login","client_name":"' + name.replace(/"/g, '\\"') + '","room_id":"' + room_id + '"}';
    console.log("websocket握手成功，发送登录数据:" + login_data);
    ws.send(login_data);
}
// 服务端发来消息时
function onmessage(e) {
    console.log(e.data);
    var data = JSON.parse(e.data);
    switch (data["data"]['type']) {
        // 服务端ping客户端
        case 'ping':
            ws.send('{"type":"pong"}');
            break;
        // 登录 更新用户列表
        case 'login':
            if (data["data"]['client_list']) {
                client_list = data["data"]['client_list'];
            }
            else {
                client_list[data["data"]['client_id']] = data["data"]['client_name'];
            }
            flush_client_list();
            console.log(data["data"]['client_name'] + "登录成功");
            break;
        // 发言
        case 'say':
            vm.chat_msgs.push(data["data"]);
            break;
        // 用户退出 更新用户列表
        case 'logout':
            delete client_list[data["data"]['from_client_id']];
            flush_client_list();
            break;
        //错误处理
        case "error":
            alert(data['msg']);
            //昵称不能重复
            if (data['code'] === 101) {
                delete name;
                name = null;
                onopen()
            }

    }
}



// 输入姓名
function show_prompt() {
    name = prompt('输入你的名字：', '');
    if (!name || name == 'null') {
        name = '游客';
    }
}

// 提交对话
function onSubmit() {
    var input = document.getElementById("textarea");
    var to_client_id = $("#client_list option:selected").attr("value");
    var to_client_name = $("#client_list option:selected").text();
    ws.send('{"type":"say","to_client_id":"' + to_client_id + '","to_client_name":"' + to_client_name + '","content":"' + input.value.replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r') + '"}');
    input.value = "";
    input.focus();
}

// 刷新用户列表框
function flush_client_list() {
    var userlist_window = $("#userlist");
    var client_list_slelect = $("#client_list");
    userlist_window.empty();
    client_list_slelect.empty();
    userlist_window.append('<h4>在线用户</h4><ul>');
    client_list_slelect.append('<option value="all" id="cli_all">所有人</option>');
    for (var p in client_list) {
        userlist_window.append('<li id="' + p + '">' + client_list[p] + '</li>');
        client_list_slelect.append('<option value="' + p + '">' + client_list[p] + '</option>');
    }
    $("#client_list").val(select_client_id);
    userlist_window.append('</ul>');
}


$(function () {
    select_client_id = 'all';
    $("#client_list").change(function () {
        select_client_id = $("#client_list option:selected").attr("value");
    });

    vm = new Vue({
        el: '#main',
        data: {
            chat_msgs: [],
            user_list:[]
        },
        methods :{
            format_content:function (content) {
                //解析新浪微博图片
                content = content.replace(/(http|https):\/\/[\w]+.sinaimg.cn[\S]+(jpg|png|gif)/gi, function (img) {
                        return "<a target='_blank' href='" + img + "'>" + "<img src='" + img + "'>" + "</a>";
                    }
                );

                //解析url
                content = content.replace(/(http|https):\/\/[\S]+/gi, function (url) {
                        if (url.indexOf(".sinaimg.cn/") < 0)
                            return "<a target='_blank' href='" + url + "'>" + url + "</a>";
                        else
                            return url;
                    }
                );
                content = $("<div>" + content + "</div>").parseEmotion().html();
                return content;
            }
        }
    });
});

