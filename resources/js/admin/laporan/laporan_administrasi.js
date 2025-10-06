$(function () {
    var table = $('#administrasiTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/laporan/laporan_administrasi",
        autoWidth: false,
        columnDefs: [
            {"width" : "50px", "terget": 0 },
            {"width" : "35%", "terget": 1 },
            {"width" : "30%", "terget": 2 },
            {"width" : "35%", "terget": 3 },
        ],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'laporan', name: 'laporan' },
            { data: 'tarif', name: 'tarif' },
            { data: 'periode', name: 'periode' },
        ],
        dom: 't',
        rowCallback: function (row, data) {
            $(row).addClass('bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600');
            $('td', row).addClass('px-6 py-4 text-gray-900 dark:text-white');
        }
    });

    $('#administrasi_searchInput').on('keyup', function () {
        table.search(this.value).draw();
    });

    const $info = $('#administrasi_customInfo');
    const $pagination = $('#administrasi_customPagination');
    const $perPage = $('#administrasi_pageLength');

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        $info.text(`Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`);
        $pagination.empty();

        const prevDisabled = currentPage === 1 ? 'opacity-50 cursor-not-allowed' : '';
        $pagination.append(`<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`);

        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);
        if (end - start < maxVisible - 1) start = Math.max(end - maxVisible + 1, 1);

        for (let i = start; i <= end; i++) {
            const active = i === currentPage
                ? 'text-blue-600 bg-blue-50 border-blue-300 hover:bg-blue-100'
                : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700';
            $pagination.append(`<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`);
        }

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
