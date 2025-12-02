<style>
    /* --- ESTILOS TABLE MODERN (INVOICE STYLE) --- */
    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 15px;
        overflow: visible !important;
    }

    .table-modern thead th {
        border: none;
        color: #6c757d;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 0 15px 10px 15px;
    }

    .table-modern tbody tr {
        background-color: #fff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .table-modern tbody tr:hover {
        transform: none !important;
        background-color: #f8f9fa;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);

        position: relative;
        z-index: 1100 !important;
    }

    .table-modern tbody tr:focus-within {
        background-color: #eef2ff;
        position: relative;
        z-index: 1100 !important;

    }

    .table-modern td {
        vertical-align: top;
        padding: 20px;
        background: #fff;
        border-top: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
    }

    .table-modern td:first-child {
        border-left: 1px solid #f1f5f9;
        border-top-left-radius: 15px;
        border-bottom-left-radius: 15px;
    }

    .table-modern td:last-child {
        border-right: 1px solid #f1f5f9;
        border-top-right-radius: 15px;
        border-bottom-right-radius: 15px;
    }

    /* INPUTS */
    .input-modern {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.9rem;
        padding: 8px 12px;
        width: 100%;
        background-color: #f8fafc;
        transition: all 0.2s;
    }

    .input-modern:focus {
        background-color: #fff;
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .label-mini {
        display: block;
        font-size: 0.7rem;
        color: #94a3b8;
        margin-bottom: 4px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .subtotal-text {
        font-size: 1.1rem;
        font-weight: 800;
        color: #1e293b;
    }


    /*buscar */
    .search-container {
        position: relative;
        /* Esto es lo nuevo: permite que el z-index funcione */
        z-index: 10;
    }

    /* TRUCO DE MAGIA: */
    /* Cuando el buscador tenga el foco (o escribas en él), lo traemos al frente */
    .search-container:focus-within {
        z-index: 2000 !important;
        /* 2000 gana al 1030 del footer */
    }

    /* --- ESTILO PARA EL ITEM SELECCIONADO CON TECLADO --- */
    .search-item.active-keyboard {
        background-color: #e8f0fe;
        /* Azul clarito */
        border-left: 4px solid #007bff;
        /* Borde azul a la izquierda */
        font-weight: 600;
    }


    /* --- BARRA INFERIOR FIJA --- */
    .barra-inferior-fija {
        position: fixed;
        bottom: 0;
        right: 0;
        background-color: #ffffff;
        border-top: 1px solid #dee2e6;
        padding: 15px 30px;
        z-index: 1030;
        box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.05);
        transition: left 0.3s ease-in-out;
        left: 0;
    }

    @media (min-width: 768px) {
        body:not(.sidebar-collapse) .barra-inferior-fija {
            left: 250px;
        }

        body.sidebar-collapse .barra-inferior-fija {
            left: 4.6rem;
        }
    }

    @media (max-width: 992px) {
        .table-modern thead {
            display: none;
        }

        .table-modern tbody tr {
            display: block;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            overflow: hidden;
        }

        .table-modern td {
            display: block;
            width: 100%;
            text-align: left;
            padding: 15px;
            border: none;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-modern td:first-child,
        .table-modern td:last-child {
            border-radius: 0;
            border: none;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-modern td:last-child {
            border-bottom: none;
            background-color: #f8fafc;
        }

        .barra-inferior-fija {
            left: 0 !important;
            padding: 10px 15px;
        }

        .barra-inferior-fija .row {
            flex-direction: column;
            gap: 10px;
        }

        .barra-inferior-fija .col-md-6 {
            width: 100%;
            text-align: center !important;
        }

        .barra-inferior-fija h3 {
            font-size: 1.5rem;
        }

        .barra-inferior-fija .btn {
            width: 100%;
            margin-bottom: 5px;
            display: block;
        }
    }

    .card-header-gradient {
        background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
        color: white;
        border-bottom: 0;
        border-radius: 0.25rem 0.25rem 0 0;
    }

    .form-label-icon {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }

    .form-label-icon i {
        margin-right: 8px;
        color: #17a2b8;
        font-size: 1.1em;
        width: 20px;
        text-align: center;
    }

    .input-enhanced {
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 10px 12px;
        background-color: #fff;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .input-enhanced:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.15);
    }

    .group-box {
        background-color: #fdfdfd;
        border: 1px solid #f1f3f5;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        margin-bottom: 20px;
    }
</style>


<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    /* Estilo para el botón del ojito/más */
    .btn-addon-icon {
        width: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    /* CORRECCIÓN PARA SELECT2 DENTRO DE INPUT-GROUP */
    .input-group .select2-container--bootstrap-5 {
        flex-grow: 1;
        /* Ocupa todo el espacio restante */
        width: auto !important;
        /* Evita que fuerce el 100% */
    }

    .input-group .select2-container--bootstrap-5 .select2-selection {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        height: 38px;
        /* Altura estándar de Bootstrap */
    }

    /* Estilo del botón pegado a la derecha */
    .btn-addon-right {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left: 0;
        /* Para que se fusione visualmente */
        display: flex;
        align-items: center;
        padding-left: 15px;
        padding-right: 15px;
    }

    /* =============================================
       OVERRIDES PARA MODO OSCURO (AdminLTE / Bootstrap)
       ============================================= */

    /* 1. TABLA MODERNA EN MODO OSCURO */
    body.dark-mode .table-modern thead th {
        color: #ced4da;
        /* Texto encabezado más claro */
    }

    body.dark-mode .table-modern tbody tr {
        background-color: #343a40;
        /* Fondo fila oscuro */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        /* Sombra más fuerte para contraste */
    }

    body.dark-mode .table-modern tbody tr:hover {
        background-color: #3f474e;
        /* Hover ligeramente más claro */
    }

    body.dark-mode .table-modern td {
        background: #343a40;
        border-top: 1px solid #4b545c;
        border-bottom: 1px solid #4b545c;
        color: #fff;
    }

    body.dark-mode .table-modern td:first-child {
        border-left: 1px solid #4b545c;
    }

    body.dark-mode .table-modern td:last-child {
        border-right: 1px solid #4b545c;
    }

    /* En móvil (responsive) */
    @media (max-width: 992px) {
        body.dark-mode .table-modern tbody tr {
            border: 1px solid #4b545c;
        }

        body.dark-mode .table-modern td {
            border-bottom: 1px solid #4b545c;
        }

        body.dark-mode .table-modern td:last-child {
            background-color: #3f474e;
            /* Footer de la card en móvil */
        }
    }

    /* 2. INPUTS Y FORMULARIOS */
    body.dark-mode .input-modern,
    body.dark-mode .input-enhanced,
    body.dark-mode .form-control {
        background-color: #343a40;
        border-color: #6c757d;
        color: #fff;
    }

    body.dark-mode .input-modern:focus,
    body.dark-mode .input-enhanced:focus {
        background-color: #3f474e;
        border-color: #17a2b8;
        /* Mantener el color cyan del tema */
        color: #fff;
    }

    body.dark-mode .label-mini {
        color: #adb5bd;
        /* Etiquetas más claras */
    }

    body.dark-mode .subtotal-text {
        color: #fff;
    }

    body.dark-mode .form-label-icon {
        color: #ced4da;
    }

    body.dark-mode .group-box {
        background-color: #343a40;
        /* Fondo de caja agrupada */
        border: 1px solid #4b545c;
    }

    /* 3. BARRA INFERIOR FIJA */
    body.dark-mode .barra-inferior-fija {
        background-color: #343a40;
        border-top: 1px solid #4b545c;
        color: #fff;
    }

    /* 4. RESULTADOS DE BÚSQUEDA */
    body.dark-mode .search-item.active-keyboard {
        background-color: #3f474e;
        /* Fondo activo oscuro */
        border-left: 4px solid #17a2b8;
        /* Borde cyan */
        color: #fff;
    }

    /* 5. SELECT2 EN MODO OSCURO (Esto suele ser doloroso, aquí está arreglado) */
    body.dark-mode .select2-container--bootstrap-5 .select2-selection {
        background-color: #343a40;
        border-color: #6c757d;
        color: #fff;
    }

    body.dark-mode .select2-container--bootstrap-5 .select2-selection__rendered {
        color: #fff !important;
    }

    body.dark-mode .select2-container--bootstrap-5 .select2-dropdown {
        background-color: #343a40;
        border-color: #6c757d;
    }

    body.dark-mode .select2-container--bootstrap-5 .select2-results__option {
        color: #fff;
        background-color: #343a40;
    }

    body.dark-mode .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #17a2b8 !important;
        /* Cyan al pasar el mouse */
        color: #fff !important;
    }

    body.dark-mode .select2-container--bootstrap-5 .select2-search__field {
        background-color: #3f474e;
        color: #fff;
        border-color: #6c757d;
    }

    /* Botón Addon (el del ojito/más) */
    body.dark-mode .btn-addon-icon,
    body.dark-mode .btn-addon-right {
        border-color: #6c757d;
    }
</style>