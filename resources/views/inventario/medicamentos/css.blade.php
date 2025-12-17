<style>
    /* ============================================================
       VARIABLES Y DARK MODE
       ============================================================ */
    :root {
        --bg-default: #ffffff;
        --bg-light: #f8f9fa;
        --text-dark: #343a40;
        --border-color: #dee2e6;
        --color-danger: #dc3545;
        --color-success: #28a745;
    }

    body.dark-mode {
        --bg-default: #343a40;
        --bg-light: #3f474e;
        --text-dark: #f8f9fa;
        --border-color: #4b6584;
    }

    /* APLICACIÓN GLOBAL DEL DARK MODE */
    .card,
    .modal-content {
        background-color: var(--bg-default);
        color: var(--text-dark);
        border: 1px solid var(--border-color);
    }

    .card-header,
    .bg-light,
    .modal-footer {
        background-color: var(--bg-light) !important;
        border-color: var(--border-color);
    }

    .text-dark {
        color: var(--text-dark) !important;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(var(--text-dark), 0.05);
    }

    /* Inputs en Dark Mode */
    .dark-mode .form-control {
        background-color: #3f474e;
        color: #f8f9fa;
        border-color: #6c757d;
    }

    .dark-mode .input-group-text {
        background-color: #4b6584;
        color: #f8f9fa;
        border-color: #6c757d;
    }


    /* ============================================================
       RESPONSIVIDAD MÓVIL (<= 767.98px)
       ============================================================ */
    @media (max-width: 767.98px) {

        /* General */
        h1 {
            font-size: 1.5rem !important;
        }

        .btn {
            font-size: 0.8rem;
        }

        /* 1. INDEX / TABLA */
        .table-responsive .table th,
        .table-responsive .table td {
            padding: 0.5rem 0.5rem !important;
            font-size: 0.85rem;
            white-space: normal;
            /* Permitir salto de línea en celdas largas */
        }

        .table-responsive .table {
            min-width: 100%;
            /* Asegura que no se desborde */
        }

        /* Columna Producto: más pequeña */
        .table-responsive .table th:nth-child(2),
        .table-responsive .table td:nth-child(2) {
            width: 40% !important;
        }

        /* Ocultar Categoría en Móvil */
        .table-responsive .table th:nth-child(3),
        .table-responsive .table td:nth-child(3) {
            display: none;
        }

        /* Compactar Stock/Precio: Ocultar el botón rápido de edición para ahorrar espacio */
        .table-responsive .table td:nth-child(4) .btn-xs {
            display: none;
        }

        /* Ajuste de filtros */
        .card-body .d-flex.justify-content-end {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .card-body .d-flex.justify-content-end>span,
        .card-body .d-flex.justify-content-end>.input-group {
            margin-right: 0 !important;
            width: 100% !important;
            margin-bottom: 0.5rem;
        }

        /* 2. SHOW (Detalle) */
        .list-group-item {
            font-size: 0.9rem;
        }

        .profile-username {
            font-size: 1.5rem;
        }

        /* 3. HISTORIAL */
        .historial-title-box {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .historial-title-box>div:first-child {
            margin-bottom: 0.5rem;
        }
    }

    /* Animación simple si no usas Animate.css completo */
    @keyframes pulse-red {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .animate__pulse {
        animation: pulse-red 2s infinite;
    }
</style>