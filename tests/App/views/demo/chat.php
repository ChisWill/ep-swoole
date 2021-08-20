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

    #login-area {
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

<div>
    <div id="input-area">
        <textarea id="input-box"></textarea>
    </div>

    <div id="send-area">
        <input type="button" id="send-btn" value="发送">
    </div>
</div>

<script>
    let websocket = new WebSocket('ws://<?= $host ?>:9501');
    class EpWebSocket {
        constructor(webSocket) {
            this.webSocket = webSocket;
            this.events = {};
        }

        onOpen(callback) {
            this.webSocket.onopen = callback;
        }

        onClose(callback) {
            this.webSocket.onclose = callback;
        }

        on(event, callback) {
            this.events[event] = callback;
        }

        emit(event, data) {
            this.webSocket.send(JSON.stringify([
                event,
                data
            ]));
        }

        run() {
            let self = this;
            this.webSocket.onmessage = function(event) {
                let response = JSON.parse(event.data);
                self.events[response[0]](response[1]);
            }
        }
    }
    let display = function(data, type = 'center') {
        let date = new Date;
        let msg = data + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();

        $msg = $("<div>").addClass(type).html(msg);
        $("#message-area .end").before($msg);
        $("#message-area .end")[0].scrollIntoView();
    }
    let ws = new EpWebSocket(websocket);
    ws.onOpen(function(evt) {
        display('Connected to WebSocket server.');
    });
    ws.on('msg', function(data) {
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
    ws.run();

    $("#login-btn").click(function() {
        let id = $("#login-id").val();
        if (!id) {
            alert('Require Id');
        }
        ws.emit('user/login', id);
    });
    $("#send-btn").click(function() {
        let text = $("#input-box").val();
        if (!text) {
            alert('Empty text');
        }
        display(text, 'right');
        ws.emit('chat/sendText', text);
    });
</script>