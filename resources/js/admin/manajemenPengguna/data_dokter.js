import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data tabel Dokter
$(function () {
    // Inisialisasi DataTable
    var table = $('#dokterTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,  
        searching: true, 
        ordering: true, 
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "manajemen_pengguna/data_dokter",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'nama_dokter', name: 'nama_dokter' },
            { data: 'spesialisasi', name: 'spesialisasi' },
            { data: 'email', name: 'email' },
            { data: 'no_hp', name: 'no_hp' },
            { 
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false,
                className: 'text-center whitespace-nowrap' 
            },
        ],
        dom: 't',
        rowCallback: function(row, data) {
            $(row).addClass('bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600');
            $('td', row).addClass('px-6 py-4 text-gray-900 dark:text-white');
        }
    });

    // 🔎 Hubungkan search input Dokter
    $('#dokter_searchInput').on('keyup', function () {
        table.search(this.value).draw();
    });

    const $info = $('#dokter_customInfo');
    const $pagination = $('#dokter_customPagination');
    const $perPage = $('#dokter_pageLength');

    // 🔁 Update Pagination Dinamis
    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        $info.text(`Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`);

        $pagination.empty();

        // Tombol Prev
        const prevDisabled = currentPage === 1 ? 'opacity-50 cursor-not-allowed' : '';
        $pagination.append(`
            <li>
                <a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a>
            </li>
        `);

        // Nomor halaman
        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);
        if (end - start < maxVisible - 1) {
            start = Math.max(end - maxVisible + 1, 1);
        }

        for (let i = start; i <= end; i++) {
            const active = i === currentPage 
                ? 'text-blue-600 bg-blue-50 border-blue-300 hover:bg-blue-100' 
                : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700';

            $pagination.append(`
                <li>
                    <a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Tombol Next
        const nextDisabled = currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : '';
        $pagination.append(`
            <li>
                <a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a>
            </li>
        `);
    }

    // Navigasi tombol prev / next / nomor halaman
    $pagination.on('click', 'a', function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass('opacity-50')) return;

        if ($link.attr('id') === 'btnPrev') {
            table.page('previous').draw('page');
        } else if ($link.attr('id') === 'btnNext') {
            table.page('next').draw('page');
        } else if ($link.hasClass('page-number')) {
            const page = parseInt($link.data('page')) - 1;
            table.page(page).draw('page');
        }
    });

    // Dropdown per page
    $perPage.on('change', function () {
        const val = parseInt($(this).val());
        table.page.len(val).draw();
    });

    // Update pagination setiap kali DataTable digambar ulang
    table.on('draw', updatePagination);

    // Jalankan pertama kali
    updatePagination();
});

// ADD DOKTER
$(function () {
    const addModalElement = document.getElementById('addDokterModal');
    const addModal = addModalElement ? new Modal(addModalElement) : null;
    const $formAdd = $('#formAddDokter');

    function resetAddForm() {
        $formAdd[0].reset();
        $formAdd.find('.is-invalid').removeClass('is-invalid');
        $formAdd.find('.text-red-600').empty();
    }

    $('#btnAddDokter').on('click', function() {
        resetAddForm();
        addModal?.show();
    });

    $('#closeAddDokterModal').on('click', function() {
        addModal?.hide();
        resetAddForm();
    });

    $formAdd.on('submit', function(e) {
        e.preventDefault();
        const url = $formAdd.data('url');
        const formData = {
            nama_dokter: $('#nama_dokter').val(),
            spesialisasi: $('#spesialisasi').val(),
            email: $('#email_dokter').val(),
            no_hp: $('#no_hp').val(),
        };

        axios.post(url, formData)
            .then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.data.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    addModal?.hide();
                    $('#dokterTable').DataTable().ajax.reload(null, false);
                });
            })
            .catch(err => {
                if (err.response?.status === 422) {
                    const errors = err.response.data.errors;
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal!',
                        text: 'Silakan periksa kembali input Anda.'
                    });
                    for (const field in errors) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#${field}-error`).html(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Server!',
                        text: 'Terjadi kesalahan server.'
                    });
                    console.error(err);
                }
            });
    });
});


// EDIT DOKTER
$(function () {
    const editModalElement = document.getElementById('editDokterModal');
    const editModal = editModalElement ? new Modal(editModalElement) : null;
    const $formEdit = $('#formEditDokter');

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find('.is-invalid').removeClass('is-invalid');
        $formEdit.find('.text-red-600').empty();
    }

    // buka modal edit
    $('body').on('click', '.btn-edit-dokter', function() {
        resetEditForm();
        const dokterId = $(this).data('id');

        axios.get(`/manajemen_pengguna/get_dokter_by_id/${dokterId}`)
            .then(res => {
                const dokter = res.data.data;
                const baseUrl = $formEdit.data('url');
                const finalUrl = baseUrl.replace('/0', '/' + dokter.id);
                $formEdit.data('url', finalUrl);

                $('#edit_dokter_id').val(dokter.id);
                $('#edit_nama_dokter').val(dokter.nama_dokter);
                $('#edit_spesialisasi').val(dokter.spesialisasi);
                $('#edit_email_dokter').val(dokter.email);
                $('#edit_no_hp').val(dokter.no_hp);

                editModal?.show();
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Tidak dapat memuat data dokter.'
                });
            });
    });

    // simpan update
    $formEdit.on('submit', function(e) {
        e.preventDefault();
        const url = $formEdit.data('url');
        const formData = {
            nama_dokter: $('#edit_nama_dokter').val(),
            spesialisasi: $('#edit_spesialisasi').val(),
            email: $('#edit_email_dokter').val(),
            no_hp: $('#edit_no_hp').val(),
            _method: 'PUT'
        };

        axios.post(url, formData)
            .then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.data.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    editModal?.hide();
                    $('#dokterTable').DataTable().ajax.reload(null, false);
                });
            })
            .catch(err => {
                if (err.response?.status === 422) {
                    const errors = err.response.data.errors;
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal!',
                        text: 'Silakan periksa kembali input Anda.'
                    });
                    for (const field in errors) {
                        $(`#edit_${field}`).addClass('is-invalid');
                        $(`#edit_${field}-error`).html(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Server!',
                        text: 'Terjadi kesalahan server.'
                    });
                    // console.error(err);
                }
            });
    });

    $('#closeEditDokterModal').on('click', function() {
        editModal?.hide();
        resetEditForm();
    });
});


// delete data dokter
$(function () {
    $('body').on('click', '.btn-delete-dokter', function() {
        const dokterId = $(this).data('id');
        if (!dokterId) return;

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/manajemen_pengguna/delete_dokter/${dokterId}`)
                    .then(response => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            if ($('#dokterTable').length) {
                                $('#dokterTable').DataTable().ajax.reload(null, false);
                            } else {
                                window.location.reload();
                            }
                        });
                    })
                    .catch(error => {
                        console.error("SERVER ERROR:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan server. Silakan coba lagi.'
                        });
                    });
            }
        });
    });
});

