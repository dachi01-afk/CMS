import axios from 'axios';
import Chart from 'chart.js/auto';


// diagram kunjungan
const ctx = document.getElementById('chartKunjungan').getContext('2d');
let chart;

function loadChart() {
    const jenis = document.getElementById('jenisKunjungan').value;
    const poli = document.getElementById('jenisPoli').value;
    const periode = document.getElementById('periodeFilter').value;
    const tahun = document.getElementById('tahunFilter').value;

    // Objek parameter query untuk dikirimkan ke Axios
    const params = {
        jenis_kunjungan: jenis,
        poli: poli,
        periode: periode,
        tahun: tahun
    };

    // --- Mengganti fetch dengan axios ---
    axios.get('/dashboard/chart-kunjungan', {
        params: params // Axios secara otomatis membuat URL query string dari objek params
    })
    .then(response => {
        // Axios sudah otomatis mengkonversi JSON, data ada di response.data
        const res = response.data;
        
        // console.log(res);

        const ctx = document.getElementById("chartKunjungan").getContext("2d");
        if (chart) chart.destroy();

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: res.labels,
                datasets: [{
                    label: 'Jumlah Kunjungan',
                    data: res.data,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0,0,255,0.1)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // === Update Summary ===
        document.getElementById("totalKunjungan").textContent = res.summary.total;
        document.getElementById("persenKunjungan").textContent = res.summary.percentage + "%";
        document.getElementById("teksPerbandinganKunjungan").textContent = res.summary.compare_text;

        const persenWrapper = document.getElementById("grafikKunjungan");
        const arrowIcon = document.querySelector("#arrowKunjungan");
        
        // Membersihkan kelas warna yang sudah ada
        persenWrapper.classList.remove("text-red-500", "text-green-500");

        if (res.summary.percentage >= 0) {
            persenWrapper.classList.add("text-green-500");
            arrowIcon.setAttribute("d", "M5 15l7-7 7 7"); // panah ke atas
        } else {
            persenWrapper.classList.add("text-red-500");
            arrowIcon.setAttribute("d", "M19 9l-7 7-7-7"); // panah ke bawah
        }
    })
    .catch(error => {
        // Penanganan error terpusat
        console.error('Error saat mengambil data kunjungan:', error);
        // Anda bisa menambahkan UI feedback di sini, misal: alert('Gagal memuat data!');
    });
}

// Event Listeners (TETAP SAMA)
document.getElementById('jenisKunjungan').addEventListener('change', loadChart);
document.getElementById('jenisPoli').addEventListener('change', loadChart);
document.getElementById('periodeFilter').addEventListener('change', loadChart);
loadChart();

document.addEventListener("DOMContentLoaded", function () {
    axios.get('/dashboard/getdashboardmetrics')
        .then(function (response) {
            const Data = response.data;
            
            // Update setiap card dengan data dari backend
            updateCard("card-consultation", Data.consultation_time, Data.consultation_percentage, Data.compare_text, Data.consultation_is_positive);
            updateCard("card-new-patient", Data.new_patient_count, Data.new_patient_percentage, Data.compare_text, Data.new_patient_is_positive);
            updateCard("card-registered", Data.registered_patient_count, Data.registered_patient_percentage, Data.compare_text, Data.registered_is_positive);
            updateCard("card-doctor-wait", Data.doctor_wait_time, Data.doctor_wait_percentage, Data.compare_text, Data.doctor_wait_is_positive);
            updateCard("card-medicine", Data.medicine_out_count, Data.medicine_out_percentage, Data.compare_text, Data.medicine_out_is_positive);
            updateCard("card-pharmacy", Data.pharmacy_wait_time, Data.pharmacy_wait_percentage, Data.compare_text, Data.pharmacy_wait_is_positive);
        })
        .catch(function (error) {
            console.error("Error loading dashboard metrics:", error);
        });

    function updateCard(id, value, percentage, context, isPositive) {
        
        const card = document.getElementById(id);
        // if (!card) return;
         if (!card) {
            console.error(`Elemen card dengan ID: ${id} tidak ditemukan.`);
            return;
        }

        const valueEl = card.querySelector("[data-role='value']");
        const percentageEl = card.querySelector("[data-role='percentage']");
        const contextEl = card.querySelector("[data-role='context']");

        if (valueEl) valueEl.textContent = value ?? "-";
        if (percentageEl) {
            percentageEl.textContent = percentage ?? "-";
            percentageEl.classList.toggle("text-green-600", isPositive);
            percentageEl.classList.toggle("text-red-600", !isPositive);
        }
        if (contextEl) contextEl.textContent = context ?? "-";
        }
});

// Pendapatan Bulanan

// const rupiahFormat = new Intl.NumberFormat("id-ID", {
//     style: "currency",
//     currency: "IDR",
//     minimumFractionDigits: 0
// });
// function getPendapatanBulanan() {
//     fetch(`/dashboard/getpendapatanbulanan`)
//     .then(res => res.json())
//     .then(res => {
//         // console.log(res);
//         document.getElementById("totalpendapatan").textContent = rupiahFormat.format(res.total);
//         document.getElementById("pesentasePendapatan").textContent = res.percentage + "%";
//         document.getElementById("pendapatandiBulanSekarang").textContent = res.compare_text;
        

//         const wrapper = document.getElementById("tandapanahVisualPendapatan");
//         const arrow = document.getElementById("indikatorGrafikPendapatan");

//          // Atur ulang kelas warna
//         wrapper.classList.remove("bg-green-300", "bg-red-300");

//             if (res.percentage > 0) {
//             wrapper.classList.add("bg-green-300");
//             arrow.setAttribute("d", "M640-720v80h104L536-434 376-594 80-296l56 56 240-240 160 160 264-264v104h80v-240H640Z"); // Panah ke atas
//             arrow.setAttribute("fill", "#008000"); // Warna hijau
//         } else {
//             wrapper.classList.add("bg-red-300");
//             arrow.setAttribute("d", "M640-240v-80h104L536-526 376-366 80-664l56-56 240 240 160-160 264 264v-104h80v240H640Z"); // Panah ke bawah
//             arrow.setAttribute("fill", "#EA3323"); // Warna merah
//         }
        
//         // Tambahan: jika persentase 0, panah mendatar
//         if (res.percentage === 0) {
//                 arrow.setAttribute("d", "M80-480h800v-80H80v80Z");
//         }
//     })
//     .catch(err => {
//         console.error('Error fetching average consultation time:', err);
//     });
//  }
//  getPendapatanBulanan()


 // Pendapatan Bulanan
// function getPengeluaranBulanan() {
//     fetch(`/dashboard/getpengeluaranbulanan`)
//     .then(res => res.json())
//     .then(res => {
//         // console.log(res);
//         document.getElementById("totalPengeluaran").textContent = rupiahFormat.format(res.total);
//         document.getElementById("presantasePengeluaran").textContent = res.percentage + "%";
//         document.getElementById("pengeluarandiBulanSekarang").textContent = res.compare_text;
        

//         const wrapper = document.getElementById("tandapanahVisualPengeluaran");
//         const arrow = document.getElementById("indikatorGrafikPengeluaran");

//          // Atur ulang kelas warna
//         wrapper.classList.remove("bg-green-300", "bg-red-300");

//             if (res.percentage > 0) {
//             wrapper.classList.add("bg-green-300");
//             arrow.setAttribute("d", "M640-720v80h104L536-434 376-594 80-296l56 56 240-240 160 160 264-264v104h80v-240H640Z"); // Panah ke atas
//             arrow.setAttribute("fill", "#008000"); // Warna hijau
//         } else {
//             wrapper.classList.add("bg-red-300");
//             arrow.setAttribute("d", "M640-240v-80h104L536-526 376-366 80-664l56-56 240 240 160-160 264 264v-104h80v240H640Z"); // Panah ke bawah
//             arrow.setAttribute("fill", "#EA3323"); // Warna merah
//         }
        
//         // Tambahan: jika persentase 0, panah mendatar
//         if (res.percentage === 0) {
//                 arrow.setAttribute("d", "M80-480h800v-80H80v80Z");
//         }
//     })
//     .catch(err => {
//         console.error('Error fetching average consultation time:', err);
//     });
//  }
//  getPengeluaranBulanan()

$(function () {
    const $tabel = $('#kunjungan-table');
    
    // 1. Ambil URL dari atribut data-url
    const dataUrl = $tabel.data('url'); 

    if (dataUrl) {
        $tabel.DataTable({
            processing: true, 
            responsive: true,

            dom: '<"flex justify-between items-center mb-4"lf>rtip', 
            
            // 2. Gunakan URL yang sudah diproses oleh Blade
            ajax: dataUrl, 
            
            columns: [
                // {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false}, 
                {data: 'nama', name: 'nama'},
                {data: 'tenaga_medis', name: 'tenaga_medis'},
                {data: 'jadwal', name: 'jadwal'},
                {data: 'status', name: 'status'}, 
            ]
        });
    } else {
        console.error("DataTables Error: Pastikan elemen tabel memiliki atribut 'data-url' dengan route Blade.");
    }
});






