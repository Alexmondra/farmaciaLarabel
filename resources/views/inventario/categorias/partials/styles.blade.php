<style>
    /* --- DEFINICIÓN DE VARIABLES (TEMA DINÁMICO) --- */
    :root {
        /* Colores Base (Modo Claro) */
        --pharma-primary: #00b894;
        --pharma-text: #2d3436;
        --pharma-bg-card: #ffffff;
        --pharma-bg-subtle: #f1f2f6;
        /* Fondos suaves (buscadores, headers tabla) */
        --pharma-border: #f1f2f6;
        --pharma-hover: #f0fbf7;
        --pharma-shadow: rgba(0, 0, 0, 0.08);
        --btn-icon-bg: #f1f2f6;
        --btn-icon-color: #2d3436;
    }

    /* Override para Modo Oscuro (Se activa cuando el body tiene la clase dark-mode) */
    .dark-mode {
        --pharma-text: #ecf0f1;
        /* Texto claro */
        --pharma-bg-card: #343a40;
        /* Gris oscuro de AdminLTE */
        --pharma-bg-subtle: #3f474e;
        /* Un poco más claro que el fondo */
        --pharma-border: #4b545c;
        --pharma-hover: #3f474e;
        --pharma-shadow: rgba(0, 0, 0, 0.3);
        --btn-icon-bg: #454d55;
        --btn-icon-color: #ffffff;

        /* Ajustamos inputs del buscador para que se vean bien en oscuro */
        input::placeholder {
            color: #adb5bd;
        }
    }

    /* --- ESTILOS USANDO LAS VARIABLES --- */

    .card-modern {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px var(--pharma-shadow);
        overflow: hidden;
        background-color: var(--pharma-bg-card);
        /* USO DE VARIABLE */
        color: var(--pharma-text);
    }

    .header-modern {
        background: var(--pharma-bg-card);
        /* USO DE VARIABLE */
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--pharma-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Títulos */
    .header-modern h3 {
        color: var(--pharma-text) !important;
    }

    /* Buscador "Píldora" */
    .search-pill {
        background: var(--pharma-bg-subtle);
        /* USO DE VARIABLE */
        border-radius: 50px;
        padding: 0.6rem 1.2rem;
        display: flex;
        align-items: center;
        width: 280px;
        transition: all 0.3s;
        border: 2px solid transparent;
    }

    .search-pill:focus-within {
        background: var(--pharma-bg-card);
        border-color: var(--pharma-primary);
        box-shadow: 0 0 0 4px rgba(0, 184, 148, 0.1);
    }

    .search-pill input {
        border: none;
        background: transparent;
        width: 100%;
        margin-left: 10px;
        outline: none;
        color: var(--pharma-text);
        font-weight: 500;
    }

    .search-pill i {
        color: var(--pharma-text);
        opacity: 0.5;
    }

    /* Botón Nuevo */
    .btn-add-modern {
        background: var(--pharma-primary);
        color: white;
        border-radius: 50px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 184, 148, 0.3);
        transition: all 0.3s;
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-add-modern:hover {
        background: #00a383;
        transform: translateY(-2px);
    }

    /* TABLA */
    .table-modern thead th {
        border: none;
        background: var(--pharma-bg-subtle);
        /* Fondo sutil adaptable */
        color: var(--pharma-text);
        opacity: 0.8;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 1rem 1.5rem;
        white-space: nowrap;
    }

    .table-modern tbody td {
        border-bottom: 1px solid var(--pharma-border);
        padding: 1rem 1.5rem;
        vertical-align: middle;
        color: var(--pharma-text);
    }

    .item-row:hover {
        background-color: var(--pharma-hover);
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
        border: none;
        background: var(--btn-icon-bg);
        color: var(--btn-icon-color);
    }

    .btn-icon:hover {
        transform: scale(1.1);
        filter: brightness(0.95);
    }

    /* Footer */
    .card-footer {
        background-color: var(--pharma-bg-card) !important;
        border-top: 1px solid var(--pharma-border);
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 768px) {
        .header-modern {
            padding: 1.25rem;
            flex-direction: column;
            align-items: stretch;
        }

        .header-actions {
            flex-direction: column;
            width: 100%;
            margin-top: 15px;
            gap: 10px;
        }

        .search-pill {
            width: 100%;
        }

        .btn-add-modern {
            width: 100%;
            padding: 0.8rem;
        }

        .table-modern thead th,
        .table-modern tbody td {
            padding: 0.75rem;
        }

        .col-desc {
            display: none;
        }
    }
</style>