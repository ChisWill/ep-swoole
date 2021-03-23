<script src="https://lib.baomitu.com/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/3.1.3/socket.io.min.js"></script>

<h1>SocketIO</h1>

<div>
    <input type="text" id="text">

    <input type="button" id="button" value="提交">
</div>

<h3>消息区域</h3>
<div id="area">

</div>

<script>
    const socket = io("ws://127.0.0.1:9501", {
        transports: ['websocket']
    });

    var display = function(data, type = 2) {
        var date = new Date;
        var msg = data + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();

        if (type == 1) {
            msg = '发送：' + msg;
        } else {
            msg = '接收：' + msg;
        }

        $msg = $("<p>").html(msg);
        $("#area").append($msg);
    }

    $("#button").click(function() {
        var data = $("#text").val();
        display(data, 1);
        console.log(data);
        socket.emit('send', data);
    });

    socket.on("connect", function() {
        console.log(2345);
    });

    socket.on('msg', data => {
        display(data);
    });
</script>