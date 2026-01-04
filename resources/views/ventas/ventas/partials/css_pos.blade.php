<style>
    /* =========================================
       VARIABLES GLOBALES Y DARK MODE
       ========================================= */
    #toast-container {
        z-index: 9999 !important;
    }

    :root {
        --color-success: #28a745;
        --color-primary: #007bff;
        --color-danger: #dc3545;
        --bg-white-mode: #ffffff;
        --bg-card-mode: #ffffff;
        --text-dark-mode-light: #343a40;
        --border-color-mode: #ced4da;
    }

    body.dark-mode {
        --bg-white-mode: #343a40;
        --bg-card-mode: #2c3e50;
        --text-dark-mode-light: #f8f9fa;
        --border-color-mode: #4b6584;
    }

    /* === APLICACIÓN GENERAL DEL DARK MODE === */
    .card {
        background-color: var(--bg-card-mode);
        color: var(--text-dark-mode-light);
        border: 1px solid var(--border-color-mode);
    }

    .card-header,
    .table-head-fixed thead {
        background-color: var(--bg-white-mode) !important;
        border-bottom: 1px solid var(--border-color-mode);
    }

    .text-dark-mode-light {
        color: var(--text-dark-mode-light) !important;
    }

    /* Inputs en Dark Mode */
    .form-control,
    .input-cliente-pos,
    .display-nombre-cliente {
        background-color: var(--bg-white-mode);
        color: var(--text-dark-mode-light);
        border-color: var(--border-color-mode);
    }

    body.dark-mode .form-control:focus {
        background-color: #3f474e;
    }

    body.dark-mode .input-group-text {
        background-color: #3f474e;
        color: #f8f9fa;
        border-color: #4b6584;
    }

    /* =========================================
       CLASES CRUCIALES DEL BUSCADOR POS
       ========================================= */

    .search-container,
    .search-container-cat {
        position: relative;
    }

    /* Listas Flotantes (Resultados) */
    #resultados-medicamentos,
    #resultados-categorias {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 9999;
        max-height: 350px;
        overflow-y: auto;
        background: var(--bg-white-mode);
        border: 1px solid var(--border-color-mode);
        border-top: none;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19);
        border-radius: 0 0 5px 5px;
        display: none;
    }

    #resultados-medicamentos.active,
    #resultados-categorias.active {
        display: block;
    }

    /* NAVEGACIÓN TECLADO */
    .resultado-medicamento.active-key,
    .item-categoria.active-key {
        background-color: var(--color-success) !important;
        color: white !important;
        border-color: var(--color-success);
        font-weight: bold;
    }

    .resultado-medicamento.active-key small,
    .resultado-medicamento.active-key .text-muted {
        color: #f8f9fa !important;
    }

    /* =========================================
       TABLA CARRITO Y CONTROL DE INPUTS (DESKTOP/TABLET)
       ========================================= */

    .table-carrito th {
        font-size: 0.9rem;
    }

    .table-carrito td {
        vertical-align: middle;
        font-size: 0.95rem;
    }

    /* ** ANCHOS DE INPUTS PARA PANTALLA GRANDE ** */
    .table-carrito .input-edit-cant,
    .table-carrito .input-edit-precio {
        /* Claves de alineación horizontal para que el text-center del TD funcione */
        display: block;
        margin: 0 auto;
        /* Centra el input horizontalmente */

        padding: 0.2rem 0.4rem;
        font-size: 0.85rem;
    }

    .table-carrito .input-edit-cant {
        width: 65px !important;
    }

    .table-carrito .input-edit-precio {
        width: 85px !important;
    }


    .card-body.table-responsive {
        height: 350px;
        /* Altura estándar para desktop */
    }


    /* =========================================
       TOTALES Y COBRO
       ========================================= */

    .total-display {
        font-size: 2.2rem;
        font-weight: bold;
        color: var(--color-success);
        line-height: 1.2;
    }

    .vuelto-display {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--color-danger);
    }

    .original-price-strike {
        font-size: 0.6em;
        text-decoration: line-through;
        opacity: 0.7;
        color: #495057;
        display: inline-block;
    }

    body.dark-mode .original-price-strike {
        color: #ced4da;
    }

    /* Cliente */
    .display-nombre-cliente {
        font-weight: 700;
        border: 1px solid var(--border-color-mode);
    }

    /* Color de borde del loader en Dark Mode */
    body.dark-mode .loader-input {
        color: var(--color-primary);
        background-color: var(--bg-white-mode);
    }

    /* estilos para cantidad sobregirara el stock en veneta*/
    .table-carrito .form-control.is-invalid {
        background-image: none !important;
        padding-right: 0.5rem !important;
        border-color: #dc3545 !important;
    }

    .table-carrito .form-control.is-invalid {
        color: #dc3545;
        font-weight: bold;
    }

    /* =========================================
       RESPONSIVIDAD MÓVIL (<= 768px)
       ========================================= */
    @media (max-width: 767.98px) {

        /* Reducir títulos y espaciado */
        .card-header h3 {
            font-size: 1rem !important;
        }

        /* COMPRESIÓN DE TABLA PARA MÓVIL */
        .table-carrito th,
        .table-carrito td {
            font-size: 0.75rem;
            /* Fuente más pequeña en móvil */
            padding: 0.2rem !important;
            white-space: normal;
        }

        /* FUERZA ANCHOS MÍNIMOS EN MÓVIL (para que quepa todo) */
        .table-carrito .input-edit-cant {
            width: 40px !important;
            padding: 0.1rem;
        }

        .table-carrito .input-edit-precio {
            width: 55px !important;
            padding: 0.1rem;
        }

        /* Reducir el tamaño de fuente del total */
        .total-display {
            font-size: 1.8rem;
        }

        .vuelto-display {
            font-size: 1.2rem;
        }

        /* Altura de la tabla reducida en móvil */
        .card-body.table-responsive {
            height: 300px !important;
        }
    }
</style>