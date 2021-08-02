class EpSocket {

    webSocket: WebSocket;
    events: {};

    constructor(webSocket: WebSocket) {
        this.webSocket = webSocket;
    }

    onOpen(callback: any): void {
        this.webSocket.onopen = callback;
    }

    onClose(callback: any): void {
        this.webSocket.onclose = callback;
    }

    on(event: string, callback: Function): void {
        this.events[event] = callback;
    }

    emit(event: string, data: any): void {
        this.webSocket.send(JSON.stringify([
            event,
            data
        ]));
    }

    run(): void {
        let self: EpSocket = this;
        this.webSocket.onmessage = function (ev: MessageEvent) {
            let message = JSON.parse(ev.data);
            self.events[message[0]](message[1]);
        };
    }
}