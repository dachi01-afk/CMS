import "./bootstrap";
import Alpine from "alpinejs";
import axios from "axios";

window.Alpine = Alpine;

Alpine.start();

// ✅ Import Flowbite
import "flowbite";
import { initFlowbite } from "flowbite";

// ✅ Import jQuery & DataTables
import $ from "jquery";
import DataTable from "datatables.net";
import "datatables.net-responsive";
import "datatables.net-dt/css/dataTables.dataTables.css";

// ✅ Global assignment
window.$ = window.jQuery = $;

// ✅ Export agar DataTable bisa diakses dari file JS lain
export { DataTable };

document.addEventListener("DOMContentLoaded", () => {
    initFlowbite();

    const passwordInput = document.getElementById("password");
    const toggleBtn = document.getElementById("togglePassword");
    const eyeIcon = document.getElementById("eyeIcon");
    const eyeOffIcon = document.getElementById("eyeOffIcon");

    if (passwordInput && toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            const isHidden = passwordInput.type === "password";
            passwordInput.type = isHidden ? "text" : "password";

            if (eyeIcon && eyeOffIcon) {
                eyeIcon.classList.toggle("hidden", isHidden);
                eyeOffIcon.classList.toggle("hidden", !isHidden);
            }

            toggleBtn.setAttribute("aria-pressed", String(isHidden));
            toggleBtn.setAttribute(
                "aria-label",
                isHidden ? "Sembunyikan password" : "Tampilkan password",
            );
        });
    }

    // =========================
    // HEARTBEAT USER ONLINE
    // =========================
    const isAuthenticated =
        document
            .querySelector('meta[name="user-authenticated"]')
            ?.getAttribute("content") === "true";

    if (isAuthenticated) {
        setInterval(() => {
            axios
                .post("/heartbeat")
                .then((response) => {
                    // optional: console.log("Heartbeat success");
                })
                .catch((error) => {
                    console.error("Heartbeat gagal:", error);
                });
        }, 60000); // 60 detik
    }
});


