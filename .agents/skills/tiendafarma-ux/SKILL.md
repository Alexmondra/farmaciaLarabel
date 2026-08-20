---

name: tiendafarma-ux

description: |
Esta skill debe consultarse y aplicarse ANTES de realizar cualquier
modificación visual, estructural o interactiva dentro de la tienda.

Define el sistema visual, UX, colores, componentes, espaciado,
responsive design, accesibilidad, animaciones, ecommerce patterns
y criterios de calidad de la plataforma.

# ============================================================

# REGLA PRINCIPAL — CONSULTA OBLIGATORIA

# ============================================================

Esta skill es la FUENTE DE VERDAD para el diseño UX/UI de la tienda.

ANTES de realizar cualquier tarea relacionada con la tienda,
el agente DEBE consultar mentalmente y aplicar las reglas definidas
en esta skill.

Esto aplica incluso cuando el usuario solicite únicamente:

* Cambiar un color
* Modificar un botón
* Crear una nueva página
* Crear una nueva sección
* Modificar una tarjeta
* Crear un formulario
* Crear un modal
* Crear un carrito
* Crear un checkout
* Modificar el navbar
* Modificar el footer
* Agregar una animación
* Crear un filtro
* Crear una búsqueda
* Crear una página de producto
* Modificar una plantilla Blade
* Crear componentes Tailwind
* Corregir responsive
* Mejorar UX
* Agregar promociones
* Crear banners
* Crear categorías
* Agregar wishlist
* Agregar estados de producto

Nunca realizar una modificación visual aislada ignorando
el sistema definido en esta skill.

# ============================================================

# ORDEN DE PRIORIDAD

# ============================================================

Cuando se trabaje en la tienda, seguir este orden:

1. Reglas de esta skill
2. UX y accesibilidad
3. Consistencia con el sistema visual existente
4. Requerimiento específico del usuario
5. Implementación técnica
6. Detalles estéticos secundarios

Si el requerimiento del usuario entra en conflicto con esta skill,
se debe buscar una solución que cumpla el objetivo solicitado
sin romper el sistema de diseño.

Si es necesario cambiar una regla visual importante,
primero identificar el impacto sobre el resto de la interfaz.

# ============================================================

# PROCESO OBLIGATORIO ANTES DE MODIFICAR

# ============================================================

Antes de escribir o modificar código relacionado con UI/UX:

1. Identificar qué componente o página se está modificando.
2. Revisar las reglas de esta skill relacionadas con ese componente.
3. Mantener la paleta visual definida.
4. Mantener la jerarquía visual.
5. Mantener los patrones de spacing.
6. Mantener los border-radius.
7. Mantener las sombras.
8. Mantener las animaciones.
9. Mantener responsive design.
10. Mantener accesibilidad.
11. Verificar que el nuevo diseño no contradiga componentes existentes.

No diseñar cada pantalla desde cero.

Todo nuevo componente debe parecer parte
del mismo producto.

# ============================================================

# IDENTIDAD VISUAL

# ============================================================

La tienda debe sentirse como:

```
FARMACIA MODERNA
+
HEALTHCARE
+
ECOMMERCE PREMIUM
```

Debe transmitir:

* Confianza
* Salud
* Limpieza
* Profesionalismo
* Tecnología
* Seguridad
* Cercanía

Evitar una apariencia:

* Genérica
* Antigua
* Saturada
* Excesivamente blanca
* Excesivamente verde
* Similar a un dashboard administrativo

# ============================================================

# SISTEMA DE COLOR

# ============================================================

Fondo principal:

```
#F5F8F7
```

Fondo secundario:

```
#EEF5F3
```

Superficie:

```
#FFFFFF
```

Verde principal:

```
#0F9F82
```

Verde oscuro:

```
#087F6A
```

Azul médico:

```
#0F4C5C
```

Texto principal:

```
#17332F
```

Texto secundario:

```
#647875
```

Borde:

```
#E2ECE9
```

Descuento:

```
#E85D75
```

Advertencia:

```
#D99A22
```

# ============================================================

# REGLA DEL BLANCO

# ============================================================

NO utilizar:

```
bg-white
```

como fondo general de toda la tienda.

El fondo principal debe ser:

```
bg-[#F5F8F7]
```

El blanco debe utilizarse principalmente
como superficie:

* Cards
* Modales
* Drawers
* Formularios
* Navbar
* Contenedores destacados

Objetivo:

```
Fondo suave
+
Superficies blancas
+
Verde controlado
```

Esto genera profundidad sin utilizar sombras exageradas.

# ============================================================

# REGLA 80 / 15 / 5

# ============================================================

La composición visual debe aproximarse a:

```
80% neutros
15% verde/teal
5% colores de estado
```

No convertir toda la interfaz en verde.

# ============================================================

# TIPOGRAFÍA

# ============================================================

Preferir:

```
Inter
Manrope
Plus Jakarta Sans
```

Texto principal:

```
#17332F
```

Texto secundario:

```
#647875
```

Evitar negro puro salvo casos excepcionales.

# ============================================================

# BORDER RADIUS

# ============================================================

Inputs:

```
rounded-xl
```

Botones:

```
rounded-xl
```

Cards:

```
rounded-2xl
```

Modales:

```
rounded-2xl
rounded-3xl
```

Badges:

```
rounded-full
```

No utilizar rounded-full indiscriminadamente.

# ============================================================

# SOMBRAS

# ============================================================

Las sombras deben ser suaves.

Preferir:

```
shadow-sm
shadow-md
shadow-lg
```

Ejemplo premium:

```
shadow-slate-200/60
```

Evitar sombras negras fuertes.

# ============================================================

# ANIMACIONES

# ============================================================

Las animaciones deben comunicar interacción.

Utilizar:

```
transition-all duration-300 ease-out
```

Hover:

```
hover:-translate-y-1
```

Botones:

```
active:scale-95
```

Imágenes:

```
group-hover:scale-105
```

No utilizar animaciones llamativas sin necesidad.

Evitar:

```
animate-bounce
```

como comportamiento permanente.

# ============================================================

# NAVBAR

# ============================================================

Navbar:

```
sticky
top-0
z-50
```

Puede utilizar:

```
bg-white/85
backdrop-blur-xl
border-b border-slate-200/60
```

Debe contener:

```
Logo
Buscador
Cuenta
Carrito
```

En móvil debe simplificarse.

# ============================================================

# BUSCADOR

# ============================================================

El buscador es un componente prioritario.

Debe:

* Ser fácilmente visible
* Tener icono
* Tener estado focus
* Permitir búsqueda rápida
* Mostrar resultados dinámicos cuando corresponda

Estilo:

```
rounded-full
bg-[#F5F8F7]
border-[#E2ECE9]
```

Focus:

```
border-emerald-500
ring-4
ring-emerald-500/10
```

# ============================================================

# PRODUCT CARDS

# ============================================================

Las cards deben utilizar:

```
bg-white
border-[#E2ECE9]
rounded-2xl
```

Hover:

```
hover:-translate-y-1
hover:shadow-lg
```

Imagen:

```
aspect-square
rounded-xl
bg-[#F5F8F7]
object-contain
```

La imagen debe ser protagonista.

Precio actual:

```
text-emerald-600
font-bold
```

Precio anterior:

```
line-through
text-slate-400
```

# ============================================================

# BOTONES

# ============================================================

Primario:

```
bg-emerald-600
hover:bg-emerald-700
text-white
rounded-xl
```

Secundario:

```
bg-emerald-50
text-emerald-700
```

Neutral:

```
bg-white
border-[#E2ECE9]
```

Todos deben tener:

```
transition-all duration-300
```

Acciones importantes:

```
active:scale-95
```

# ============================================================

# CARRITO

# ============================================================

Preferir Drawer/Slide-over.

Desktop:

```
ancho aproximado 400px
```

Mobile:

```
width 100%
```

Debe mostrar:

* Productos
* Cantidades
* Subtotal
* Envío
* Total
* CTA principal

Al agregar un producto:

* Actualizar contador
* Dar feedback
* Realizar micro-interacción

# ============================================================

# CHECKOUT

# ============================================================

Debe ser simple y sin distracciones.

Utilizar:

```
Datos
↓
Entrega
↓
Pago
↓
Confirmación
```

El paso actual debe destacar.

El checkout debe transmitir seguridad.

# ============================================================

# FORMULARIOS

# ============================================================

Inputs:

```
bg-[#F8FAFA]
border-[#E2ECE9]
rounded-xl
```

Focus:

```
border-emerald-500
ring-4
ring-emerald-500/10
```

Los errores deben aparecer junto al campo correspondiente.

# ============================================================

# RESPONSIVE

# ============================================================

Mobile-first obligatorio.

Debe funcionar correctamente como mínimo en:

```
320px
375px
390px
768px
1024px
1280px
1440px+
```

No asumir que un diseño desktop simplemente debe reducirse.

La experiencia móvil debe diseñarse conscientemente.

# ============================================================

# ACCESIBILIDAD

# ============================================================

Siempre:

* alt en imágenes
* labels en formularios
* focus visible
* contraste suficiente
* nombres claros para botones
* aria-label cuando sea necesario
* navegación por teclado
* estados que no dependan únicamente del color

# ============================================================

# COMPONENTIZACIÓN

# ============================================================

Cuando sea posible, reutilizar componentes.

Ejemplos:

```
ProductCard
Button
Badge
SearchBar
CartDrawer
Modal
Input
CategoryCard
Price
EmptyState
LoadingSkeleton
```

No duplicar componentes visuales innecesariamente.

# ============================================================

# BLADE / LARAVEL

# ============================================================

Cuando se trabaje dentro de:

```
resources/views/tienda
```

mantener:

* Blade
* TailwindCSS
* Alpine.js
* Componentes reutilizables
* Responsive
* Accesibilidad

No modificar lógica backend si el requerimiento
es únicamente visual.

No romper:

* Variables Blade
* Directivas
* Loops
* Forms
* Rutas
* Componentes existentes

# ============================================================

# JAVASCRIPT

# ============================================================

Preferir CSS para:

* Hover
* Transiciones
* Transformaciones
* Estados visuales

Utilizar Alpine.js/JavaScript para:

* Modales
* Drawers
* Carrito
* Búsqueda
* Filtros
* Dropdowns
* Wishlist
* Interacciones reales

No utilizar JavaScript para solucionar algo
que CSS puede resolver.

# ============================================================

# UX DE FARMACIA

# ============================================================

La interfaz debe considerar que el usuario
puede estar buscando productos relacionados con salud.

Por lo tanto:

* La información debe ser clara.
* No esconder información importante.
* Mostrar claramente cuando un producto requiere receta.
* Diferenciar productos agotados.
* Mostrar disponibilidad.
* Mostrar información de entrega.
* Mostrar seguridad del pago.
* Evitar mensajes ambiguos.

# ============================================================

# REGLA DE CONSISTENCIA

# ============================================================

Cuando se cree un nuevo componente:

NO preguntarse únicamente:

```
"¿Se ve bonito?"
```

Preguntarse:

```
"¿Parece que siempre perteneció a esta tienda?"
```

Debe respetar:

```
Color
Tipografía
Radius
Shadow
Spacing
Animation
Responsive
Accesibilidad
```

# ============================================================

# REGLA DE MODIFICACIÓN

# ============================================================

Si el usuario pide:

```
"haz este botón más bonito"
```

No crear un botón completamente diferente.

Primero identificar:

* Sistema de botones existente
* Color principal
* Radius
* Sombra
* Estados
* Animaciones

Después realizar la mejora dentro del sistema.

Si el usuario pide:

```
"crea una nueva página"
```

Primero aplicar:

```
Fondo
Tipografía
Espaciado
Componentes
Jerarquía
Responsive
Accesibilidad
```

de esta skill.

# ============================================================

# REGLA CONTRA LA SATURACIÓN

# ============================================================

No añadir elementos únicamente para llenar espacio.

Antes de añadir un componente preguntar:

```
¿Mejora la navegación?
¿Mejora la conversión?
¿Genera confianza?
¿Aporta información?
```

Si no aporta valor:

```
No añadirlo.
```

# ============================================================

# JERARQUÍA

# ============================================================

Cada pantalla debe tener:

```
1 acción principal
1-2 acciones secundarias
resto de acciones terciarias
```

No todos los botones deben competir visualmente.

# ============================================================

# CALIDAD PREMIUM

# ============================================================

La apariencia premium debe venir de:

* Espacio negativo
* Tipografía
* Proporciones
* Jerarquía
* Consistencia
* Color
* Micro-interacciones
* Calidad de imágenes

No de:

* Muchas sombras
* Muchos gradientes
* Animaciones excesivas
* Colores saturados
* Bordes exagerados

# ============================================================

# CHECKLIST OBLIGATORIO

# ============================================================

Antes de considerar terminada una modificación:

* [ ] Consulté las reglas de esta skill.
* [ ] Mantengo la identidad visual.
* [ ] El fondo principal es coherente.
* [ ] No abusé del blanco puro.
* [ ] No abusé del verde.
* [ ] Los componentes tienen jerarquía.
* [ ] Los bordes son sutiles.
* [ ] Las sombras son suaves.
* [ ] Los hover funcionan.
* [ ] Los focus funcionan.
* [ ] Las animaciones son discretas.
* [ ] El diseño funciona en móvil.
* [ ] La accesibilidad está contemplada.
* [ ] No rompí la lógica Blade.
* [ ] No dupliqué componentes innecesariamente.
* [ ] El componente parece parte del mismo sistema.
* [ ] La acción principal es evidente.
* [ ] La interfaz no está saturada.
* [ ] La experiencia transmite confianza.
* [ ] El resultado se siente premium.

# ============================================================

# REGLA FINAL — FUENTE DE VERDAD

# ============================================================

Esta skill debe considerarse el DESIGN SYSTEM BASE
de la tienda virtual.

Cada nueva funcionalidad visual debe construirse
SOBRE este sistema y no crear un sistema paralelo.

Antes de implementar:

```
CONSULTAR SKILL
      ↓
IDENTIFICAR COMPONENTE
      ↓
APLICAR SISTEMA VISUAL
      ↓
IMPLEMENTAR
      ↓
COMPROBAR RESPONSIVE
      ↓
COMPROBAR UX
      ↓
COMPROBAR ACCESIBILIDAD
      ↓
ENTREGAR
```

La meta no es únicamente que cada pantalla
funcione.

La meta es que toda la tienda se perciba
como UN SOLO PRODUCTO coherente, moderno,
confiable y premium.
--------------------
