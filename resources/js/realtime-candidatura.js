import Echo from "laravel-echo";

document.addEventListener("DOMContentLoaded", () => {
    if (typeof Echo !== "undefined") {
        Echo.channel("candidaturas").listen(".AnaliseConcluida", (e) => {
            console.log(
                "Análise concluida para a candidatura ID: ",
                e.candidaturaId,
            );

            if (
                window.location.pathname.includes("/recrutador") ||
                window.location.pathname.includes("/candidaturas")
            ) {
                window.location.reload();
            }
        });
    }
});
