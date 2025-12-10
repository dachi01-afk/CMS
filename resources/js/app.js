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
``