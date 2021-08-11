<style type="text/css">
    #message-area {
        border: 1px solid red;
        width: 100%;
        height: 300px;
        margin: 10px 0;
        overflow-y: auto;
    }

    #message-area .left {
        text-align: left;
        margin-left: 10px;
    }

    #message-area .center {
        text-align: center;
        font-size: 12px;
        color: gray;
    }

    #message-area .right {
        text-align: right;
        margin-right: 10px;
    }

    #message-are .end {
        height: 0px;
        overflow: hidden;
    }

    #input-box {
        resize: none;
        width: 100%;
        height: 100px;
    }

    #send-area {
        margin: 10px 0;
        text-align: right;
    }

    #login-area,
    #hall-area {
        margin: 10px;
        text-align: center;
    }
</style>

<div id="message-area">
    <div class="center">Now</div>
    <div class="left">Target</div>
    <div class="right">Self</div>
    <div class="end"></div>
</div>

<div id="login-area">
    <input type="text" id="login-id" value="<?= $id ?>">
    <input type="button" id="login-btn" value="登录">
</div>

<div id="hall-area">
    <input type="text" id="hall-id">
    <input type="button" id="hall-btn" value="进入房间">
</div>

<div>
    <div id="input-area">
        <textarea id="input-box"></textarea>
    </div>

    <div id="send-area">
        <input type="button" id="send-btn" value="发送">
    </div>
</div>

<script>
    let events = {};
    let websocket = new WebSocket('ws://127.0.0.1:9501/ab?access-token=1');
    let display = function(data, type = 'center') {
        let date = new Date;
        let msg = data + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();

        $msg = $("<div>").addClass(type).html(msg);
        $("#message-area .end").before($msg);
        $("#message-area .end")[0].scrollIntoView();
    }

    websocket.emit = function(event, data = '') {
        this.send(JSON.stringify([
            event,
            data
        ]));
    }
    websocket.on = function(event, callback) {
        events[event] = callback;
    };

    websocket.onopen = function(evt) {
        display('Connected to WebSocket server.');
    };

    websocket.onclose = function(evt) {
        display('Connection is closed.');
    };

    websocket.onmessage = function(evt) {
        let response = JSON.parse(evt.data);
        let event = response[0];
        let data = response[1];
        events[event](data);
    };

    websocket.on('msg', function(data) {
        switch (data['type']) {
            case 'msg':
                if (data['target'] === 'self') {
                    display(data['data'], 'right');
                } else if (data['target'] === 'target') {
                    display(data['data'], 'left');
                } else {
                    display($data['data']);
                }
                break;
            case 'system':
                display(data['data']);
                break;
        }
    });

    websocket.on('error', function(data) {
        alert('error' + data);
    });

    $("#login-btn").click(function() {
        let id = $("#login-id").val();
        if (!id) {
            alert('Require Id');
            return;
        }
        websocket.emit('user/login', id);
    });
    $("#hall-btn").click(function() {
        let room = $("#hall-id").val();
        if (!room) {
            alert('Require Room name');
            return;
        }
        websocket.emit('user/room', room);
    });
    $("#send-btn").click(function() {
        let text = $("#input-box").val();
        if (!text) {
            alert('Empty text');
            return;
        }
        display(text, 'right');
        websocket.emit('chat/sendRoomText', {
            text: text,
            room: $("#hall-id").val()
        });
    });
</script>