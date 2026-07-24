# Reglas del Proyecto (Workspace Rules)

Este archivo define las reglas de comportamiento, convenciones de código y principios de diseño para el proyecto de la farmacia.

## Agente Especialista: tiendafarma-ux
Para cualquier tarea que involucre el diseño visual, estilos, UX, o modificación de plantillas de la tienda virtual (`resources/views/tienda`), se deben seguir las directrices de la habilidad `tiendafarma-ux`.

### Principios de Diseño
1. **Estilo Salud y Confianza**: Usar la paleta de colores limpia (verdes menta/esmeralda, azul clínico y gris pizarra oscuro). Evitar colores genéricos fuertes o estridentes.
2. **Interactividad Premium**: Todas las interacciones principales (botones de compra, tarjetas de producto, botones de navegación) deben tener transiciones suaves (`transition-all duration-300 ease-in-out`).
3. **Bordes Suaves (Rounded Corners)**: Emplear esquinas redondeadas modernas (`rounded-xl`, `rounded-2xl`) para suavizar el aspecto visual y dar una sensación orgánica y amigable.
4. **Glassmorphism**: En elementos superpuestos o fijos (como la cabecera pegajosa), aplicar `backdrop-blur-md` con un fondo translúcido (`bg-white/80`).
5. **Responsividad Total**: Asegurar que todas las interfaces funcionen impecablemente desde pantallas de celulares pequeños hasta monitores grandes.
6. **Legibilidad de Texto**: Cuidar el contraste y utilizar un espaciado equilibrado (`tracking-wide` y `leading-relaxed`).

## Agente Especialista: tiendafarma-chat
Para cualquier tarea relacionada con el desarrollo, configuración, lógica, flujos de conversación o prompts del chatbot de atención al usuario, se deben seguir las directrices de la habilidad `tiendafarma-chat`.

### Principios Conversacionales y de Seguridad
1. **Prioridad Médica**: Recomendar siempre y de manera prioritaria la consulta con un médico profesional cualificado antes de tomar decisiones sobre tratamientos o medicamentos.
2. **Diagnóstico No Oficial**: Ofrecer respuestas con fines informativos y orientativos, evitando diagnosticar formalmente al usuario.
3. **Recomendación en Caso de Urgencias/Malestares**: En caso de síntomas menores o urgencias del usuario, sugerir medicamentos de venta libre (OTC) exclusivos de la tienda/farmacia virtual.
4. **Protocolo ante Emergencias Graves**: Derivar inmediatamente a servicios de emergencias oficiales locales (ej. 911) ante casos críticos (dificultad respiratoria, dolor torácico, pérdida de conocimiento, etc.).

