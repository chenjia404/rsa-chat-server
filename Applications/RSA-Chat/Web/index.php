<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>rsa-chat聊天室</title>
    <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="/css/jquery-sinaEmotion-2.1.0.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">

    <script type="text/javascript" src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="/js/jquery-sinaEmotion-2.1.0.min.js"></script>
    <script src="https://cdn.bootcss.com/vue/2.4.4/vue.js"></script>
    <script src="/js/index.js"></script>
    <script type="text/javascript">
        // 开启flash的websocket debug
        WEB_SOCKET_DEBUG = true;
        var ws, name, client_list = {};
        var room_id = "<?php echo isset($_GET[ 'room_id' ]) ? $_GET[ 'room_id' ] : 1?>";
        $(function () {
            select_client_id = 'all';
            $("#client_list").change(function () {
                select_client_id = $("#client_list option:selected").attr("value");
            });
            $('.face').click(function (event) {
                $(this).sinaEmotion();
                event.stopPropagation();
            });
        });
    </script>
</head>
<body onload="connect();">
<div class="container" id="">
    <div class="row clearfix header">
        <header>
                <span style="font-size: 34px;">rsa-chat
</span>
            基于 websocket rsa aes 的聊天室。
        </header>
    </div>
    <div class="row clearfix" id="main">
        <div class="col-md-8 column" style="padding-left:0">
            <div class="caption" id="dialog">
                <div class="speech_item" v-for="msg in chat_msgs">
                    <img v-bind:src="msg.user.avatar" class="user_icon img-circle">
                    <span class="name">{{msg.user.name}}</span>
                    <span class="created_at pull-right">{{msg.created_at}}</span>
                    <div style="clear:both;"></div>
                    <p class="bg-info msg" v-html="format_content(msg.content)"></p>
                </div>
            </div>
            <form onsubmit="onSubmit(); return false;" style=" padding: 10px; ">
                <select style="margin-bottom:8px" id="client_list">
                    <option value="all">所有人</option>
                </select>
                <textarea class="textarea thumbnail" id="textarea"></textarea>
                <div class="say-btn">
                    <input type="button" class="btn btn-default face pull-left" value="表情"/>
                    <input type="submit" class="btn btn-default" value="发表"/>
                </div>
            </form>
            <div>
                <br><br>
            </div>
            <p class="cp">本项目基于<a href="http://www.workerman.net/workerman-chat" target="_blank">workerman-chat</a>、<a href="https://cn.vuejs.org/" target="_blank">Vue.js</a>、<a href="https://github.com/Lanfei/jQuery-Sina-Emotion" target="_blank">jQuery-Sina-Emotion</a>
            </p>
        </div>
        <div class="col-md-4 column" style="padding-right:0">
            <div class="caption" id="userlist"></div>
        </div>
    </div>
</div>
</body>
</html>
