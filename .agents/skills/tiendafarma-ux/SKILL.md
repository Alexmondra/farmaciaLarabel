---
name: tiendafarma-ux
description: |
  Especialista en diseño de interfaz (UI) y experiencia de usuario (UX) para la tienda virtual de la farmacia.
  Proporciona pautas de diseño elegante, moderno, limpio y profesional, paletas de colores médicas/confiables,
  micro-interacciones, componentes responsivos y mejoras visuales en las plantillas Blade.

  Trigger inmediatamente para:
  - Diseño/UX de la tienda: Modificación de plantillas en resources/views/tienda, diseño de layouts, cabeceras, pie de página, botones, tarjetas de productos.
  - Estilos/CSS: Reglas de TailwindCSS, Bootstrap, gradientes, sombras, bordes redondeados y efectos visuales modernos.
  - Interactividad: Animaciones de carrito, estados de hover de productos, modales de confirmación y búsquedas dinámicas.
---

# Diseñador UX/UI Especialista en Farmacia Virtual (tiendafarma-ux)

Esta habilidad proporciona guías específicas de diseño, mejores prácticas y estilos CSS para transformar la tienda virtual de la farmacia en una plataforma elegante, intuitiva y sumamente profesional.

## 1. Identidad Visual y Paleta de Colores
Para transmitir confianza, salud, limpieza y profesionalismo, la interfaz debe utilizar una paleta refinada y evitar colores genéricos estridentes.

### Colores Principales (Sistema Médico Elegante)
* **Primario (Salud & Confianza)**: Verde menta o esmeralda suave combinado con verde azulado (Teal/Mint).
  - Tailwind: `text-emerald-600`, `bg-emerald-500`, `hover:bg-emerald-600`
  - HSL Recomendado: `hsl(162, 76%, 45%)` (`#10b981`) o `hsl(174, 72%, 41%)` (`#14b8a6`)
* **Secundario (Limpieza & Calma)**: Azul clínico profundo.
  - Tailwind: `text-cyan-800`, `bg-cyan-900`
* **Base Neutra (Fondo)**: Fondos claros con matices fríos para sensación de limpieza.
  - Tailwind: `bg-slate-50`, `bg-gray-50/50`
* **Texto y Contraste**: Gris pizarra oscuro para legibilidad óptima.
  - Tailwind: `text-slate-800`, `text-slate-600`
* **Acentos de Alerta/Promoción**:
  - Tailwind: `bg-rose-50 text-rose-600` (Descuentos)
  - Tailwind: `bg-amber-50 text-amber-600` (Receta médica requerida)

### Efecto Glassmorphism en Elementos Flotantes
* Cabeceras pegajosas (sticky) o modales que flotan sobre el contenido:
  ```html
  <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100/80">
  ```

## 2. Componentes Clave & Estructuras Elegantes

### Tarjeta de Producto (Product Card)
* Debe sentirse premium, con bordes muy suaves (`rounded-2xl` o `rounded-xl`) y sombras sutiles que reaccionan al pasar el mouse.
* **HTML/Tailwind Estructura**:
  ```html
  <div class="group relative bg-white border border-slate-100/80 rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-100">
      <!-- Badge de descuento o estado -->
      <span class="absolute top-3 left-3 bg-emerald-50 text-emerald-600 text-xs font-semibold px-2.5 py-1 rounded-full">
          Envío Gratis
      </span>
      <!-- Contenedor Imagen -->
      <div class="aspect-square w-full overflow-hidden rounded-xl bg-slate-50 flex items-center justify-center p-4">
          <img src="..." alt="..." class="h-full object-contain transition-transform duration-300 group-hover:scale-105">
      </div>
      <!-- Detalles -->
      <div class="mt-4">
          <span class="text-xs text-slate-400 font-medium tracking-wide uppercase">Categoría</span>
          <h3 class="text-sm font-semibold text-slate-800 mt-1 line-clamp-2 min-h-[40px]">Nombre del Medicamento</h3>
          <!-- Precio -->
          <div class="mt-3 flex items-baseline justify-between">
              <div>
                  <span class="text-xs text-slate-400 line-through">S/ 12.00</span>
                  <span class="text-lg font-bold text-emerald-600 ml-1">S/ 9.90</span>
              </div>
              <button class="bg-emerald-50 text-emerald-600 p-2 rounded-xl hover:bg-emerald-600 hover:text-white transition-all duration-300">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
              </button>
          </div>
      </div>
  </div>
  ```

### Cabecera (Navbar)
* Logotipo de la farmacia limpio con ícono representativo.
* Barra de búsqueda con bordes redondeados (`rounded-full`) e ícono de lupa integrado.
* Indicador de carrito flotante con un círculo de color vibrante (ej: `bg-rose-500` con `animate-pulse`).

### Carrito & Checkout Elegantes
* Estilo de resumen de pedido flotante o barra lateral moderna (slide-over).
* Pasos del checkout con indicadores visuales claros (Línea de tiempo / Stepper) y elegantes transiciones.

## 3. Micro-animaciones e Interactividad
* Usar `transition-all duration-300 ease-in-out` en botones, enlaces y tarjetas.
* Efectos de escala suaves al pulsar botones importantes: `active:scale-95`.
* Desvanecimientos elegantes para modales o alertas dinámicas.
