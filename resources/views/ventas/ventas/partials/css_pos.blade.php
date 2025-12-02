<style>
    /* =========================================
       ESTILOS GENERALES POS (Originales)
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
        background: white;
        border: 1px solid #ced4da;
        border-top: none;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19);
        border-radius: 0 0 5px 5px;
        display: none;
    }

    #resultados-medicamentos.active,
    #resultados-categorias.active {
        display: block;
    }

    /* NAVEGACIÓN TECLADO (VERDE) */
    .resultado-medicamento.active-key,
    .item-categoria.active-key {
        background-color: #28a745 !important;
        color: white !important;
        border-color: #28a745;
        font-weight: bold;
    }

    .resultado-medicamento.active-key small,
    .resultado-medicamento.active-key .text-muted,
    .item-categoria.active-key small {
        color: #f8f9fa !important;
    }

    /* TABLA CARRITO Y TOTALES */
    .table-carrito th {
        background-color: #f4f6f9;
        border-top: 0;
        font-size: 0.9rem;
    }

    .table-carrito td {
        vertical-align: middle;
        font-size: 0.95rem;
    }

    .total-display {
        font-size: 2.2rem;
        font-weight: bold;
        color: #28a745;
        line-height: 1.2;
    }

    .vuelto-display {
        font-size: 1.5rem;
        font-weight: 800;
        color: #dc3545;
    }

    .input-pago {
        font-size: 1.2rem;
        font-weight: bold;
        text-align: center;
    }

    /* Input cliente POS */
    .card-cliente-pos {
        height: 100%;
        min-height: 180px;
    }

    /* CSS del Buscador de Clientes (Include original) */
    .input-cliente-pos {
        font-weight: 700;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }

    .input-cliente-pos:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
    }

    .loader-input {
        background-color: #fff;
        border-left: none;
        color: #007bff;
    }

    .display-nombre-cliente {
        background-color: #f8f9fa !important;
        color: #343a40;
        font-weight: 700;
        border: 1px solid #ced4da;
    }

    .display-nombre-cliente.not-found {
        color: #dc3545;
        border-color: #dc3545;
        background-color: #fff5f5 !important;
    }
</style>