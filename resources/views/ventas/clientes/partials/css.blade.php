<style>
    /* ==========================================
       ESTILOS BASE (Light Mode / Default)
       ========================================== */
    .kpi-card {
        background: #ffffff;
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        position: relative;
        height: 100%;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 800;
        color: #2c3e50;
    }

    .kpi-label {
        color: #888;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .kpi-icon {
        position: absolute;
        right: -10px;
        bottom: -15px;
        font-size: 5rem;
        opacity: 0.08;
        transform: rotate(-15deg);
        transition: 0.3s;
    }

    .kpi-card:hover .kpi-icon {
        transform: rotate(0deg) scale(1.1);
        opacity: 0.15;
    }

    /* FILTROS */
    .filter-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 15px 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .filter-card:hover {
        transform: translateY(-2px);
        background: #f8f9fa;
    }

    .filter-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #6c757d;
        text-transform: uppercase;
    }

    .filter-count {
        background: #e9ecef;
        color: #495057;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 800;
        font-size: 0.85rem;
    }

    .filter-card.active {
        background: #e0f7fa;
        border-color: #00bcd4;
    }

    .filter-card.active .filter-title {
        color: #00838f;
    }

    .filter-card.active .filter-count {
        background: #00bcd4;
        color: #fff;
    }

    /* BARRA DE BÚSQUEDA */
    .search-input {
        height: 55px;
        border-radius: 30px;
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        padding-left: 55px;
        font-size: 1.1rem;
        background: #fff;
        color: #495057;
    }

    .search-input:focus {
        outline: none;
        box-shadow: 0 5px 20px rgba(0, 210, 211, 0.25);
    }

    .search-icon {
        position: absolute;
        left: 25px;
        top: 18px;
        color: #00d2d3;
        font-size: 1.2rem;
        z-index: 5;
    }

    /* TABLA */
    .table-card {
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        border: none;
        overflow: hidden;
    }

    .table-hover tbody tr:hover {
        background-color: #f1fbfd;
    }

    .avatar-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        margin-right: 15px;
        background-color: rgba(0, 188, 212, 0.15);
        color: #00bcd4;
    }

    .avatar-ruc {
        background-color: rgba(255, 152, 0, 0.15);
        color: #f57c00;
    }

    /* BOTÓN */
    .btn-new-client {
        background: linear-gradient(135deg, #00d2d3 0%, #00a8ff 100%);
        border: 0;
        border-radius: 50px;
        padding: 10px 25px;
        color: white;
        font-weight: bold;
        box-shadow: 0 5px 15px rgba(0, 168, 255, 0.3);
        transition: 0.3s;
    }

    .btn-new-client:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(0, 168, 255, 0.4);
        color: white;
    }

    /* DARK MODE */
    .dark-mode .content-wrapper {
        background-color: #454d55 !important;
    }

    .dark-mode .kpi-card,
    .dark-mode .filter-card,
    .dark-mode .table-card {
        background-color: #343a40;
        color: #fff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }

    .dark-mode .kpi-value {
        color: #fff;
    }

    .dark-mode .kpi-label {
        color: #adb5bd;
    }

    .dark-mode .filter-title {
        color: #ced4da;
    }

    .dark-mode .search-input {
        background-color: #343a40;
        color: #fff;
        border: 1px solid #6c757d;
    }

    .dark-mode .search-input::placeholder {
        color: #adb5bd;
    }

    .dark-mode .filter-card:hover {
        background-color: #3f474e;
    }

    .dark-mode .filter-card.active {
        background-color: rgba(0, 188, 212, 0.2);
        border-color: #00bcd4;
    }

    .dark-mode .filter-card.active .filter-title {
        color: #00bcd4;
    }

    .dark-mode .filter-count {
        background-color: #495057;
        color: #fff;
    }

    .dark-mode .filter-card.active .filter-count {
        background-color: #00bcd4;
        color: #fff;
    }

    .dark-mode .table-hover tbody tr:hover {
        background-color: #3f474e;
    }

    .dark-mode .table {
        color: #fff;
    }

    .dark-mode .text-muted {
        color: #adb5bd !important;
    }

    .dark-mode .text-dark {
        color: #fff !important;
    }

    .dark-mode .avatar-circle {
        background-color: rgba(0, 188, 212, 0.25);
    }

    .dark-mode .avatar-ruc {
        background-color: rgba(255, 152, 0, 0.25);
    }


    /* todo lo que es para moviles*/

    @media (max-width: 991.98px) {

        /* Encabezado */
        .container-fluid.pt-4 {
            padding-top: 1rem !important;
        }

        .btn-new-client {
            padding: 8px 15px;
            font-size: 0.9rem;
        }

        /* KPIS / FILTROS */
        .filter-card {
            padding: 10px 15px;
        }

        .filter-title {
            font-size: 0.8rem;
        }

        .filter-count {
            padding: 2px 8px;
            font-size: 0.75rem;
        }

        .bonus-card {
            /* Asegura que el contenido quede centrado y visible */
            padding: 15px 15px !important;
        }

        /* Buscador */
        .search-input {
            height: 45px;
            font-size: 1rem;
            padding-left: 45px;
        }

        .search-icon {
            left: 15px;
            top: 14px;
            font-size: 1rem;
        }

        /* TABLA MÓVIL (Compresión extrema) */
        .table-hover td {
            padding: 0.4rem 0.5rem !important;
            vertical-align: middle;
            font-size: 0.85rem;
        }

        /* Ocultar columnas menos críticas */
        .table-hover thead th:nth-child(2),
        .table-hover tbody td:nth-child(2) {
            /* Documento - Se oculta el texto del documento para ahorrar espacio */
            display: none !important;
        }

        .table-hover thead th:nth-child(3),
        .table-hover tbody td:nth-child(3) {
            /* Contacto - Se oculta el email para solo dejar el teléfono */
            padding-right: 0.25rem !important;
        }

        .table-hover thead th:nth-child(4),
        .table-hover tbody td:nth-child(4) {
            /* Puntos */
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }

        .table-hover thead th:nth-child(1) {
            /* Cliente */
            width: 50% !important;
        }

        .table-hover thead th:nth-child(5) {
            /* Acciones */
            width: 20% !important;
        }

        /* Ajuste de avatar */
        .avatar-circle {
            width: 35px;
            height: 35px;
            font-size: 0.9rem;
            margin-right: 10px;
        }

        .font-weight-bold.text-dark {
            font-size: 0.9rem !important;
        }

        .text-muted small {
            font-size: 0.7rem;
        }

        /* Contacto: Ocultar email para solo mostrar teléfono */
        .table-hover tbody td:nth-child(3) .small {
            display: none;
        }

        /* Paginación */
        .pagination {
            justify-content: flex-end;
            /* Mover paginación a la derecha */
        }
    }

    /* ==========================================
       ESTILOS PERFILES MÉDICOS (tiendafarma-ux)
       ========================================== */
    .bg-light-green {
        background-color: rgba(16, 172, 132, 0.08) !important;
    }
    .text-teal {
        color: #0f8062 !important;
    }
    .rounded-xl {
        border-radius: 12px !important;
    }
    .rounded-lg {
        border-radius: 8px !important;
    }
    .transition-all {
        transition: all 0.3s ease-in-out;
    }
    .duration-300 {
        transition-duration: 300ms;
    }
    .btn-pm-trigger {
        transition: all 0.2s ease-in-out;
    }
    .btn-pm-trigger:hover {
        transform: scale(1.1);
        background-color: #28a745;
        color: #fff !important;
    }
    .btn-add-pm {
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);
        transition: all 0.3s ease;
    }
    .btn-add-pm:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(40, 167, 69, 0.3);
    }

    /* MODO OSCURO - PERFILES MÉDICOS */
    .dark-mode #modalPerfilesMedicos .modal-content {
        background-color: #343a40;
        color: #fff;
    }
    .dark-mode #modalPerfilesMedicos .bg-light {
        background-color: #3f474e !important;
    }
    .dark-mode #modalPerfilesMedicos .alert {
        background-color: rgba(16, 172, 132, 0.15) !important;
        color: #52d2b5 !important;
    }
    .dark-mode #modalPerfilesMedicos .alert .text-dark {
        color: #fff !important;
    }
    .dark-mode #modalPerfilesMedicos .table {
        color: #fff;
        background-color: #343a40;
    }
    .dark-mode #modalPerfilesMedicos .table thead th {
        background-color: #454d55 !important;
        color: #e9ecef;
        border-color: #56606a;
    }
    .dark-mode #modalPerfilesMedicos .table td {
        border-top-color: #56606a;
    }
    .dark-mode #modalPerfilesMedicos .table-hover tbody tr:hover {
        background-color: #3f474e;
    }
    .dark-mode #modalPerfilesMedicos .card {
        background-color: #3f474e !important;
    }
    .dark-mode #pm_form label {
        color: #e9ecef !important;
    }
    .dark-mode #pm_form textarea,
    .dark-mode #pm_form input {
        background-color: #454d55 !important;
        color: #fff !important;
        border: 1px solid #56606a !important;
    }
    .dark-mode #pm_form textarea:focus,
    .dark-mode #pm_form input:focus {
        border-color: #10ac84 !important;
        box-shadow: 0 0 0 0.2rem rgba(16, 172, 132, 0.25) !important;
    }
    .dark-mode #pm_form textarea::placeholder,
    .dark-mode #pm_form input::placeholder {
        color: #adb5bd;
    }
</style>