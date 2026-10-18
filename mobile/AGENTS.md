# Instrucciones de la aplicación móvil

Aplicar también [las instrucciones raíz](../AGENTS.md). Ámbito: mobile/.

- Stack: Flutter, Riverpod para estado, GoRouter para navegación y Dio para acceso HTTP.
- Priorizar Android con VS Code y Android Studio/SDK. Preparar iOS únicamente en una fase futura con Mac y Xcode; no asumir que puede compilarse o validarse en Windows.
- Organizar por módulos funcionales y separar presentación, casos de uso y acceso a datos, aplicando arquitectura hexagonal solo donde aporte valor.
- Partir de especificaciones, contratos de API y criterios de aceptación; aplicar TDD al comportamiento y pruebas de interfaz cuando corresponda.
- Mantener explícito el contexto de entidad fiscal. Evitar mezclar datos, estado o cachés entre entidades y limpiar el contexto al cambiar de entidad o sesión; la autorización debe verificarse también en el backend.
- Mostrar extracciones documentales de IA como pendientes hasta su validación humana, con posibilidad de revisar y corregir.
- Consumir resultados fiscales deterministas del backend conforme al contrato; no duplicar reglas con IA ni presentar soporte fuera de los modelos 130, 303 y 420 documentados.
- Consultar DOC-04, DOC-06, DOC-07 y DOC-08 en [el índice](../docs/README.md).
- En esta tarea no inicializar Flutter, generar código de aplicación ni instalar dependencias.
