namespace Ep {
    export class EpSocket {

        webSocket: WebSocket;
        events: {};

        constructor(url: string) {
            this.webSocket = new WebSocket(url);
        }

        public onOpen(callback: any): void {
            this.webSocket.onopen = callback;
        }

        public onClose(callback: any): void {
            this.webSocket.onclose = callback;
        }

        public on(event: string, callback: Function): void {
            this.events[event] = callback;
        }

        public emit(event: string, data: any): void {
            this.webSocket.send(JSON.stringify([
                event,
                data
            ]));
        }

        public run(): void {
            let self: EpSocket = this;
            this.webSocket.onmessage = function (ev: MessageEvent) {
                let message = JSON.parse(ev.data);
                self.events[message[0]](message[1]);
            };
        }
    }
}
