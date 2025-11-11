(() => {
  const root = document.getElementById('form-create');
  if (!root) return;

  // ------- Utils -------
  const $ = (sel, ctx = root) => (ctx || document).querySelector(sel);
  const on = (el, ev, fn, opts) => el && el.addEventListener(ev, fn, opts);
  const cls = (el, ...c) => el && el.classList.add(...c);

  // ------- Inputs que disparan búsqueda -------
  const $cod = $('#codigo');
  const $nom = $('#nombre');
  const $bar = document.getElementById('codigo_barra')
            || document.getElementById('codigo_barras')
            || $('#codigo_barra, #codigo_barras');

  // ------- Campos a autorrellenar (solo medicamento) -------
  const $medId = $('#medicamento_existente_id');
  const $categoria = $('#categoria_id');
  const $lab = $('#laboratorio');
  const $pres = $('#presentacion');

  // ------- Contenedor de sugerencias (o lo creamos en caliente) -------
  let $suggestions = $('#suggestions');

  function ensureSuggestionsFor($input) {
    if ($suggestions && $suggestions.isConnected) return $suggestions;
    // Si no existe, crearlo debajo del input activo
    const wrap = $input?.closest('.position-relative') || $input?.parentElement || root;
    $suggestions = document.createElement('div');
    $suggestions.id = 'suggestions';
    $suggestions.className = 'list-group position-absolute w-100 shadow';
    $suggestions.style.zIndex = 1050;
    $suggestions.style.maxHeight = '240px';
    $suggestions.style.overflow = 'auto';
    $suggestions.style.display = 'none';
    // Asegurar posicionamiento relativo del contenedor padre
    if (wrap && getComputedStyle(wrap).position === 'static') wrap.style.position = 'relative';
    (wrap || root).appendChild($suggestions);
    return $suggestions;
  }

  // ------- URL del lookup (con fallbacks) -------
  const LOOKUP_URL =
    (root?.dataset?.lookupUrl) ||
    (document.querySelector('meta[name="lookup-url"]')?.content) ||
    (window.LOOKUP_URL || '');

  if (!LOOKUP_URL) {
    console.warn('[lookup] No hay URL. Usa data-lookup-url en #form-create o <meta name="lookup-url">');
  }

  // ------- Debounce + AbortController -------
  let debounceTO, ctrl, lastInput;
  const debounce = (fn, ms = 250) => { clearTimeout(debounceTO); debounceTO = setTimeout(fn, ms); };

  // ------- Normalizar respuesta del backend -------
  function normalize(json) {
    // { exact, suggestions }
    if (json && (json.exact || json.suggestions)) {
      return {
        exact: json.exact || null,
        suggestions: Array.isArray(json.suggestions) ? json.suggestions : []
      };
    }
    // { data: [...] }
    if (json && Array.isArray(json.data)) return { exact: null, suggestions: json.data };
    // Array directo
    if (Array.isArray(json)) return { exact: null, suggestions: json };
    // { items: [...] } / { results: [...] }
    const items = json?.items || json?.results || [];
    if (Array.isArray(items)) return { exact: null, suggestions: items };
    return { exact: null, suggestions: [] };
  }

  // ------- Fetch JSON seguro (detecta HTML / redirect) -------
  async function fetchJson(url) {
    const r = await fetch(url, {
      method: 'GET',
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      signal: ctrl?.signal
    });

    const ct = r.headers.get('content-type') || '';
    if (!r.ok) {
      throw new Error(`HTTP ${r.status}`);
    }
    if (!ct.includes('application/json')) {
      const txt = await r.text();
      console.error('[lookup] Respuesta no-JSON (¿redirect/login?):', txt.slice(0, 200));
      throw new Error('Respuesta no-JSON (quizá 302/HTML). Revisa middlewares de la ruta lookup.');
    }
    return r.json();
  }

  // ------- Pintar sugerencias -------
  function renderSuggestions(items, $input) {
    const box = ensureSuggestionsFor($input);
    if (!items?.length) { box.style.display = 'none'; return; }

    box.innerHTML = '';
    items.slice(0, 12).forEach(it => {
      const nombre = it.nombre ?? it.name ?? '';
      const codigo = it.codigo ?? it.code ?? '';
      const barra  = it.codigo_barra ?? it.codigo_barras ?? it.barcode ?? '';

      const a = document.createElement('a');
      a.href = '#';
      a.className = 'list-group-item list-group-item-action';
      a.textContent = `${nombre} — [${codigo || 's/cod'}] ${barra ? ' / ' + barra : ''}`;
      a.addEventListener('click', (e) => {
        e.preventDefault();
        rellenar(it);
        box.style.display = 'none';
      });
      box.appendChild(a);
    });
    box.style.display = 'block';
  }

  // ------- Mostrar error como “sugerencia” (visual) -------
  function renderError(msg, $input) {
    const box = ensureSuggestionsFor($input);
    box.innerHTML = '';
    const a = document.createElement('div');
    a.className = 'list-group-item list-group-item-action text-danger';
    a.textContent = msg;
    box.appendChild(a);
    box.style.display = 'block';
  }

  // ------- Rellenar formulario (solo medicamento) -------
  function rellenar(it) {
    // Inputs (asegúrate de que existan con estos IDs en tu Blade)
    const $medId = document.getElementById('medicamento_existente_id');
  
    const $cod  = document.getElementById('codigo');
    const $nom  = document.getElementById('nombre');
  
    // toleramos #codigo_barra o #codigo_barras en tu HTML
    const $bar  = document.getElementById('codigo_barra')
              || document.getElementById('codigo_barras');
  
    const $categoria = document.getElementById('categoria_id');
    const $lab  = document.getElementById('laboratorio');
    const $pres = document.getElementById('presentacion');
  
    const $forma = document.getElementById('forma_farmaceutica');
    const $conc  = document.getElementById('concentracion');
    const $reg   = document.getElementById('registro_sanitario');
    const $desc  = document.getElementById('descripcion');
  
    // opcional si tienes un input para URL de imagen
    const $imgUrlInput = document.getElementById('imagen_url_input');
  
    // preview si quieres mostrarla
    const preview = document.getElementById('preview');
    const placeholder = document.getElementById('placeholder');
  
    if ($medId) $medId.value = it.id || '';
  
    if ($cod)  $cod.value  = it.codigo ?? '';
    if ($nom)  $nom.value  = it.nombre ?? '';
    if ($bar)  $bar.value  = it.codigo_barra ?? it.codigo_barras ?? '';
  
    if ($categoria && (it.categoria_id ?? '') !== '') $categoria.value = it.categoria_id;
    if ($lab)  $lab.value  = it.laboratorio ?? '';
    if ($pres) $pres.value = it.presentacion ?? '';
  
    if ($forma) $forma.value = it.forma_farmaceutica ?? '';
    if ($conc)  $conc.value  = it.concentracion ?? '';
    if ($reg)   $reg.value   = it.registro_sanitario ?? '';
    if ($desc)  $desc.value  = it.descripcion ?? '';
  
    if ($imgUrlInput) $imgUrlInput.value = it.imagen_url ?? '';
  
    // Mostrar preview si viene imagen_url
    if (preview && it.imagen_url) {
      preview.src = it.imagen_url;
      preview.style.display = 'block';
      if (placeholder) placeholder.style.display = 'none';
    }
  }
  

  // ------- Core: buscar por texto -------
  async function buscar(q, $input) {
    const query = (q || '').trim();
    const box = ensureSuggestionsFor($input);

    if (!LOOKUP_URL) { renderError('No hay URL de búsqueda.', $input); return; }
    if (query.length < 1) { box.style.display = 'none'; return; }

    try {
      if (ctrl) ctrl.abort();
      ctrl = new AbortController();

      console.debug('[lookup] query:', query);

      // 1) ?q=  2) fallback ?term=
      let json;
      try {
        json = await fetchJson(`${LOOKUP_URL}?q=${encodeURIComponent(query)}`);
      } catch (e1) {
        console.warn('[lookup] fallback ?term=: ', e1.message);
        json = await fetchJson(`${LOOKUP_URL}?term=${encodeURIComponent(query)}`);
      }

      const { exact, suggestions } = normalize(json);

      if (exact) {
        // Autorrellenar directo si hay match exacto
        rellenar(exact);
        box.style.display = 'none';
        return;
      }

      // Sugerencias
      renderSuggestions(suggestions || [], $input);
    } catch (e) {
      if (e.name === 'AbortError') return;
      console.error('[lookup] error:', e);
      renderError('No se pudo consultar (ver consola).', $input);
    }
  }

  // ------- Bind de eventos (Nombre, Código, Código de barras) -------
  [[$nom, 'nombre'], [$cod, 'codigo'], [$bar, 'barra']].forEach(([el]) => {
    on(el, 'input', (ev) => { lastInput = ev.currentTarget; debounce(() => buscar(el.value, lastInput), 250); });
    on(el, 'blur',  (ev) => { const v = el?.value?.trim(); lastInput = ev.currentTarget; if (v) buscar(v, lastInput); });
    // extra por si algún navegador no dispara input adecuadamente
    on(el, 'keyup', (ev) => { if (!ev.isComposing) { lastInput = ev.currentTarget; debounce(() => buscar(el.value, lastInput), 250); }});
  });

  // ------- Cerrar sugerencias si clic fuera -------
  document.addEventListener('click', (e) => {
    const box = $suggestions;
    if (!box) return;
    if (!e.target.closest('#suggestions')) box.style.display = 'none';
  });
})();
