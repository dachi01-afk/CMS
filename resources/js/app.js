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
import "datatables.net-dt/css/dataTables.dataTables.css"; // <== CSS-nya juga

// // ✅ Global assignment
// window.Alpine = Alpine;
window.$ = window.jQuery = $;

// // ✅ Start Alpine
// Alpine.start();

// ✅ Export agar DataTable bisa diakses dari file JS lain
export { DataTable };

document.addEventListener("DOMContentLoaded", () => {
    const passwordInput = document.getElementById("password");
    const toggleBtn = document.getElementById("togglePassword");
    const eyeIcon = document.getElementById("eyeIcon");
    const eyeOffIcon = document.getElementById("eyeOffIcon");

    if (!passwordInput || !toggleBtn) return;

    toggleBtn.addEventListener("click", () => {
        const isHidden = passwordInput.type === "password";
        passwordInput.type = isHidden ? "text" : "password";

        // swap icons
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
});
