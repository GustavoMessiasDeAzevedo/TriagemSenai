import axios from "axios";
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: "epparfu7fxpxgi4dkwuk",
    wsHost: "websocket-production-7a18.up.railway.app",
    wsPort: 443,
    wssPort: 443,
    forceTLS: true,
    enabledTransports: ["ws", "wss"],
});

window.Echo.channel("candidaturas").listen(".AnaliseConcluida", (e) => {
    const path = window.location.pathname;
    if (path.includes("/recrutador") || path.includes("/candidaturas")) {
        window.location.reload();
    }
});
