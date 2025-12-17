<div class="barra-inferior-fija">
    <div class="container-fluid">
        <div class="row align-items-center">
            {{-- TOTAL GENERAL --}}
            <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                    <span class="text-muted mr-3 font-weight-bold text-uppercase" style="letter-spacing: 1px;">
                        Total a Pagar:
                    </span>
                    <h3 class="mb-0 text-primary font-weight-bold" id="total-general-fijo">
                        S/ 0.00
                    </h3>
                </div>
            </div>

            {{-- BOTONES DE ACCIÓN --}}
            <div class="col-md-6 text-end">
                <a href="{{ route('compras.index') }}" class="btn btn-secondary mr-2 px-4">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-success px-5 shadow-sm font-weight-bold">
                    <i class="fas fa-save mr-2"></i> GUARDAR COMPRA
                </button>
            </div>
        </div>
    </div>
</div>