import axios from "axios";
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

import Echo from "laravel-echo";
import Pusher from "pusher-js";
window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    try {
        window.Echo = new Echo({
            broadcaster: "reverb",
            key: reverbKey,
            wsHost: import.meta.env.VITE_REVERB_HOST || "127.0.0.1",
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS:
                (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
            enabledTransports: ["ws", "wss"],
        });

        document.addEventListener("DOMContentLoaded", () => {
            if (window.Echo && typeof window.Echo.channel === "function") {
                window.Echo.channel("candidaturas").listen(
                    ".AnaliseConcluida",
                    () => {
                        if (
                            window.location.pathname.includes("/recrutador") ||
                            window.location.pathname.includes("/candidaturas")
                        ) {
                            window.location.reload();
                        }
                    },
                );
            }
        });
    } catch (e) {
        console.warn("Reverb/Echo não inicializado:", e);
    }
}
