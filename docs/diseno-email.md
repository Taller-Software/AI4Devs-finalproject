# 📧 Diseño de Email - Código de Acceso

## 🎨 Estilo Visual

El email de verificación ahora tiene el **mismo diseño profesional** que la aplicación web:

### Paleta de Colores
- **Fondo principal**: Degradado oscuro (#1e293b → #0f172a)
- **Tarjetas**: Slate oscuro (#1e293b) con bordes (#334155)
- **Acentos**: Degradado azul-cyan (#3b82f6 → #06b6d4)
- **Código**: Cyan brillante (#22d3ee) con glow effect
- **Alertas**: Ámbar (#f59e0b / #fbbf24)

### Elementos de Diseño

#### 1. **Header con Logo**
- Círculo degradado azul-cyan
- Icono de candado SVG
- Título "Astillero La Roca" con efecto degradado
- Subtítulo descriptivo

#### 2. **Sección del Código**
- Caja oscura con borde destacado
- Código en tamaño grande (48px)
- Espaciado amplio entre caracteres
- Efecto de resplandor en el texto

#### 3. **Información Importante**
- Caja con borde lateral ámbar
- Icono de advertencia
- Lista con puntos clave:
  - Validez de 15 minutos
  - No compartir el código
  - Acción si no lo solicitaste

#### 4. **Botón de Acción**
- Degradado azul-cyan
- Sombra para profundidad
- Texto claro y directo

#### 5. **Footer Minimalista**
- Texto gris discreto
- Aviso de email automático
- Copyright

---

## 📱 Responsive Design

El email está optimizado para:
- ✅ Desktop (600px de ancho máximo)
- ✅ Tablet (se adapta automáticamente)
- ✅ Mobile (tablas fluidas)
- ✅ Clientes de email (Gmail, Outlook, Apple Mail)

---

## 📋 Versión de Texto Plano (AltBody)

Para clientes que no soportan HTML:

```
╔═══════════════════════════════════════════════════════════╗
║          ASTILLERO LA ROCA                                ║
║    Sistema de Gestión de Herramientas                     ║
╚═══════════════════════════════════════════════════════════╝

Hola, [Nombre]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
            TU CÓDIGO DE ACCESO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

                [CÓDIGO]

⚠️ INFORMACIÓN IMPORTANTE:
• Válido por 15 minutos
• No compartir con nadie
```

---

## 🧪 Cómo Probar el Nuevo Diseño

### 1. Solicitar un código de acceso
```bash
# Desde la aplicación web
1. Ingresa tu email
2. Click en "Solicitar Código de Acceso"
3. Revisa tu bandeja de entrada
```

### 2. Verificar en diferentes clientes

- **Gmail**: Soporte completo de estilos
- **Outlook**: Degradados limitados (fallback a colores sólidos)
- **Apple Mail**: Soporte completo
- **Thunderbird**: Soporte completo

---

## 🎯 Elementos Clave del Diseño

### Consistencia Visual
✅ Mismos colores que la app web
✅ Misma tipografía y espaciado
✅ Iconos SVG coherentes
✅ Bordes redondeados (16px, 12px, 8px)

### Seguridad y Claridad
✅ Código destacado visualmente
✅ Tiempo de validez prominente
✅ Advertencias claras
✅ Botón de acción directo

### Profesionalismo
✅ Diseño limpio y moderno
✅ Jerarquía visual clara
✅ Espaciado generoso
✅ Footer corporativo

---

## 🔧 Personalización

El template obtiene datos desde el `.env`:

```php
APP_URL=https://tu-dominio.com  // URL del botón de acceso
SMTP_FROM_NAME="Tu Empresa"     // Nombre del remitente
```

---

## 📸 Preview del Email

### Desktop View (600px)
```
┌────────────────────────────────────────────────┐
│                                                │
│            [🔒 Logo Circular]                  │
│         Astillero La Roca                      │
│   Sistema de Gestión de Herramientas          │
│                                                │
├────────────────────────────────────────────────┤
│                                                │
│  Hola, Juan Pérez                              │
│                                                │
│  Has solicitado acceso al Sistema...           │
│                                                │
│  ┌──────────────────────────────────────┐     │
│  │   Tu Código de Acceso                │     │
│  │                                      │     │
│  │         A B C D 1 2 3 4              │     │
│  │       (texto cyan brillante)         │     │
│  └──────────────────────────────────────┘     │
│                                                │
│  ⚠️ INFORMACIÓN IMPORTANTE                     │
│  • Válido por 15 minutos                       │
│  • No compartir                                │
│                                                │
│      [Acceder al Sistema]                      │
│     (botón azul degradado)                     │
│                                                │
├────────────────────────────────────────────────┤
│  Este es un mensaje automático...             │
│  © 2025 Astillero La Roca                     │
└────────────────────────────────────────────────┘
```

---

## ✨ Mejoras Implementadas

### Antes:
- ❌ Diseño básico HTML
- ❌ Colores genéricos
- ❌ Sin branding
- ❌ Texto plano simple

### Ahora:
- ✅ Diseño profesional coherente
- ✅ Paleta corporativa
- ✅ Branding completo
- ✅ Experiencia premium

---

## 🚀 Próximos Pasos

### Posibles Mejoras Futuras:

1. **Email de Bienvenida** - Al registrar nuevo usuario
2. **Recordatorios** - Herramientas sin devolver
3. **Alertas** - Mantenimiento programado
4. **Reportes** - Resumen mensual de actividad

---

**Fecha de actualización:** 2025-10-19
**Versión:** 2.0
**Compatible con:** PHPMailer 6.x
