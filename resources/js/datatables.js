import { DataTable } from "simple-datatables";

document.addEventListener('DOMContentLoaded', () => {
    // Check for tables with specific IDs or a common class
    const tables = document.querySelectorAll("table.datatable, #search-table, #selection-table, #pagination-table");

    window.SimpleDataTables = window.SimpleDataTables || {};

    tables.forEach(table => {
        if (table) {
            const dt = new DataTable(table, {
                searchable: true,
                fixedHeight: false,
                perPage: 10,
                labels: {
                    placeholder: "Cari...",
                    perPage: "entri per halaman",
                    noRows: "Tidak ada data ditemukan",
                    info: "Menampilkan {start} sampai {end} dari {rows} entri",
                }
            });

            if (table.id) {
                window.SimpleDataTables[table.id] = dt;
            }
        }
    });
});
