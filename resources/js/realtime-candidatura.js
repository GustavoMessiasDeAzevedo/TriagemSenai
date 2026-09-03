import Echo from "laravel-echo";

document.addEventListener("DOMContentLoaded", () => {
    if (window.Echo && typeof window.Echo.channel === "function") {
        window.Echo.channel("candidaturas").listen(".AnaliseConcluida", (e) => {
            console.log("Análise concluída para a candidatura ID: ", e.candidaturaId);

            const path = window.location.pathname;
            if (path.includes("/recrutador") || path.includes("/candidaturas")) {
                window.location.reload();
            }
        });
    }
});
