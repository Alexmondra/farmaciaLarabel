<style>
    /* === ESTILO FUTURISTA / CLEAN === */
    :root {
        --neon-teal: #20c997;
        --dark-bg: #1e272e;
        --panel-bg: #ffffff;
        --border-color: #e9ecef;
    }

    /* Adaptación Dark Mode */
    body.dark-mode {
        --panel-bg: #2c3e50;
        --border-color: #4b6584;
    }

    .glass-panel {
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .glass-panel:hover {
        box-shadow: 0 8px 15px rgba(32, 201, 151, 0.08);
        /* Sombra Teal suave */
        border-color: var(--neon-teal);
    }

    .form-control-futuristic {
        background-color: transparent;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 0.9rem;
        color: #495057;
    }

    body.dark-mode .form-control-futuristic {
        color: #ecf0f1;
    }

    .form-control-futuristic:focus {
        border-color: var(--neon-teal);
        box-shadow: 0 0 0 3px rgba(32, 201, 151, 0.15);
        background-color: transparent;
    }

    .label-futuristic {
        text-transform: uppercase;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #adb5bd;
        margin-bottom: 5px;
        display: block;
    }

    .section-title {
        font-size: 0.9rem;
        font-weight: 800;
        color: #34495e;
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    body.dark-mode .section-title {
        color: #ecf0f1;
    }

    .section-title i {
        color: var(--neon-teal);
        margin-right: 8px;
        background: rgba(32, 201, 151, 0.1);
        padding: 6px;
        border-radius: 6px;
    }

    /* En css.blade.php o <style> */
    #res-busqueda .list-group-item.active {
        background-color: #20c997;
        /* Tu color Teal */
        border-color: #20c997;
        color: white !important;
    }

    /* Forzar texto blanco en los hijos cuando está activo */
    #res-busqueda .list-group-item.active strong,
    #res-busqueda .list-group-item.active small,
    #res-busqueda .list-group-item.active span {
        color: white !important;
    }
</style>