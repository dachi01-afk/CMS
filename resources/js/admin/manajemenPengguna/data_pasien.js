import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data tabel Pasien
$(function () {
    var table = $('#pasienTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/manajemen_pengguna/data_pasien",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'nama_pasien', name: 'nama_pasien' },
            { data: 'alamat', name: 'alamat' },
            { data: 'tanggal_lahir', name: 'tanggal_lahir' },
            { data: 'jenis_kelamin', name: 'jenis_kelamin' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center whitespace-nowrap' },
        ],
        dom: 't',
        rowCallback: function(row, data) {
            $(row).addClass('bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600');
            $('td', row).addClass('px-6 py-4 text-gray-900 dark:text-white');
        }
    });

    // 🔎 Search
    $('#pasien_searchInput').on('keyup', function () {
        table.search(this.value).draw();
    });

    const $info = $('#pasien_customInfo');
    const $pagination = $('#pasien_customPagination');
    const $perPage = $('#pasien_pageLength');

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        $info.text(`Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`);
        $pagination.empty();

        // Prev
        const prevDisabled = currentPage === 1 ? 'opacity-50 cursor-not-allowed' : '';
        $pagination.append(`<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`);

        // Pages
        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);
        if (end - start < maxVisible - 1) start = Math.max(end - maxVisible + 1, 1);

        for (let i = start; i <= end; i++) {
            const active = i === currentPage ? 'text-blue-600 bg-blue-50 border-blue-300 hover:bg-blue-100' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700';
            $pagination.append(`<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`);
        }

        // Next
        const nextDisabled = currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : '';
        $pagination.append(`<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`);
    }

    $pagination.on('click', 'a', function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass('opacity-50')) return;
        if ($link.attr('id') === 'btnPrev') table.page('previous').draw('page');
        else if ($link.attr('id') === 'btnNext') table.page('next').draw('page');
        else if ($link.hasClass('page-number')) table.page(parseInt($link.data('page')) - 1).draw('page');
    });

    $perPage.on('change', function () {
        table.page.len(parseInt($(this).val())).draw();
    });

    table.on('draw', updatePagination);
    updatePagination();
});


// Add Data Pasien
$(function () {
    const addModalElement = document.getElementById('addPasienModal');
    const addModal = addModalElement ? new Modal(addModalElement) : null;
    const $formAdd = $('#formAddPasien');

    function resetAddForm() {
        $formAdd[0].reset();
        $formAdd.find('.is-invalid').removeClass('is-invalid');
        $formAdd.find('.text-red-600').empty();
    }

    $('#btnAddPasien').on('click', function() {
        resetAddForm();
        if(addModal) addModal.show();
    });

    $('#closeAddPasienModal').on('click', function() {
        resetAddForm();
        if(addModal) addModal.hide();
    });

    $formAdd.on('submit', function(e) {
        e.preventDefault();
        const url = $formAdd.data('url');

        const formData = {
            nama_pasien: $('#nama_pasien').val(),
            alamat: $('#alamat').val(),
            tanggal_lahir: $('#tanggal_lahir').val(),
            jenis_kelamin: $('#jenis_kelamin').val(),
        };

        $('.text-red-600').empty();
        $formAdd.find('.is-invalid').removeClass('is-invalid');

        axios.post(url, formData)
            .then(response => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.data.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    addModal.hide();
                    $('#pasienTable').DataTable().ajax.reload(null, false);
                });
            })
            .catch(error => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal!',
                        text: 'Silakan periksa kembali isian formulir Anda.'
                    });
                    for (const field in errors) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#${field}-error`).html(errors[field][0]);
                    }
                }
            });
    });
});

// Update Data Pasien
$(function () {
    const editModalElement = document.getElementById('editPasienModal');
    const editModal = editModalElement ? new Modal(editModalElement) : null;
    const $formEdit = $('#formEditPasien');

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find('.is-invalid').removeClass('is-invalid');
        $formEdit.find('.text-red-600').empty();
    }

    $('body').on('click', '.btn-edit-pasien', function() {
        resetEditForm();
        const pasienId = $(this).data('id');

        axios.get(`/manajemen_pengguna/get_pasien_by_id/${pasienId}`)
            .then(response => {
                const pasien = response.data.data;
                const baseUrl = $formEdit.data('url');
                const finalUrl = baseUrl.replace('/0', '/' + pasien.id);
                $formEdit.data('url', finalUrl);

                $('#edit_pasien_id').val(pasien.id);
                $('#edit_nama_pasien').val(pasien.nama_pasien);
                $('#edit_alamat').val(pasien.alamat);
                $('#edit_tanggal_lahir').val(pasien.tanggal_lahir);
                $('#edit_jenis_kelamin').val(pasien.jenis_kelamin);

                if(editModal) editModal.show();
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Tidak dapat memuat data pasien.' });
            });
    });

    $formEdit.on('submit', function(e) {
        e.preventDefault();
        const url = $formEdit.data('url');

        const formData = {
            nama_pasien: $('#edit_nama_pasien').val(),
            alamat: $('#edit_alamat').val(),
            tanggal_lahir: $('#edit_tanggal_lahir').val(),
            jenis_kelamin: $('#edit_jenis_kelamin').val(),
            _method: 'PUT'
        };

        axios.post(url, formData)
            .then(response => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.data.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    editModal.hide();
                    $('#pasienTable').DataTable().ajax.reload(null, false);
                });
            })
            .catch(error => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    Swal.fire({ icon: 'error', title: 'Validasi Gagal!' });
                    for (const field in errors) {
                        $(`#edit_${field}`).addClass('is-invalid');
                        $(`#edit_${field}-error`).html(errors[field][0]);
                    }
                }
            });
    });

    $('#closeEditPasienModal').on('click', function() {
        if(editModal) editModal.hide();
        resetEditForm();
    });
});

// delete data 
$(function () {
    $('body').on('click', '.btn-delete-pasien', function() {
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
                axios.delete(`/manajemen_pengguna/delete_pasien/${dokterId}`)
                    .then(response => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            if ($('#pasienTable').length) {
                                $('#pasienTable').DataTable().ajax.reload(null, false);
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



