<style>
    /* Ajuste para que la tarjeta de cliente tenga la misma altura que la de medicamentos */
    .card-cliente-pos {
        height: 100%;
        min-height: 180px;
    }

    /* Estilo para el input de búsqueda de cliente */
    .input-cliente-pos {
        font-weight: 700;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }

    /* Efecto de enfoque (Focus) */
    .input-cliente-pos:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
    }

    /* Loader (Relojito) dentro del input */
    .loader-input {
        background-color: #fff;
        border-left: none;
        color: #007bff;
    }

    /* Nombre del cliente encontrado (Readonly bonito) */
    .display-nombre-cliente {
        background-color: #f8f9fa !important;
        color: #343a40;
        font-weight: 700;
        border: 1px solid #ced4da;
    }

    /* Estado de error (No encontrado) */
    .display-nombre-cliente.not-found {
        color: #dc3545;
        border-color: #dc3545;
        background-color: #fff5f5 !important;
    }
</style>