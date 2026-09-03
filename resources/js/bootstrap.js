import axios from "axios";
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    window.Echo = new Echo({
        broadcaster: "reverb",
        key: reverbKey,
        wsHost: import.meta.env.VITE_REVERB_HOST || "websocket-production-7a18.up.railway.app",
        wsPort: import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 443,
        wssPort: import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
        enabledTransports: ["ws", "wss"],
    });

    window.Echo.channel("candidaturas").listen(".AnaliseConcluida", (e) => {
        const path = window.location.pathname;
        if (path.includes("/recrutador") || path.includes("/candidaturas")) {
            window.location.reload();
        }
    });
}
