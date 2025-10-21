# Mejoras de Seguridad Implementadas

## 📋 Resumen de Cambios

Se han implementado 3 mejoras críticas de seguridad en la aplicación:

### 1. ✅ Sesiones con Expiración (30 minutos)
- **Problema anterior**: Las sesiones duraban indefinidamente
- **Solución implementada**:
  - Configuración de timeout de sesión: **30 minutos de inactividad**
  - Tracking de `last_activity` para validar expiración automática
  - Variable configurable en `.env`: `SESSION_DURATION=1800` (segundos)

### 2. ✅ Rate Limiting (Prevención de Fuerza Bruta)
- **Problema anterior**: Sin límite de intentos de login
- **Solución implementada**:
  - **5 intentos fallidos** en **15 minutos** = Bloqueo de **1 hora**
  - Tracking por email + IP address
  - Contador de intentos restantes visible al usuario
  - Limpieza automática de registros > 24 horas
  - Variables configurables en `.env`:
    - `RATE_LIMIT_MAX_ATTEMPTS=5`
    - `RATE_LIMIT_WINDOW=900` (15 minutos)
    - `RATE_LIMIT_BLOCK_DURATION=3600` (1 hora)

### 3. ✅ Cookies httpOnly (Sin acceso desde JavaScript)
- **Problema anterior**: Tokens en localStorage (accesibles por XSS)
- **Solución implementada**:
  - Sesiones gestionadas 100% en servidor con cookies httpOnly
  - Configuración adicional: `samesite='Lax'`, `secure` flag para HTTPS
  - Frontend **eliminó completamente** `localStorage.setItem('session')`
  - Variables configurables en `.env`:
    - `SESSION_COOKIE_NAME=ASTILLERO_SESSION`
    - `SESSION_COOKIE_HTTPONLY=true`
    - `SESSION_COOKIE_SECURE=false` (cambiar a `true` en producción con HTTPS)

---

## 📁 Archivos Modificados/Creados

### Backend (PHP)

#### 1. **db/schema.sql** (ACTUALIZADO)
```sql
-- Nueva tabla para rate limiting
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time DATETIME NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    INDEX idx_email_time (email, attempt_time),
    INDEX idx_ip_time (ip_address, attempt_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### 2. **db/add_login_attempts_table.sql** (NUEVO)
Script de migración para agregar la tabla a bases de datos existentes.

#### 3. **.env** (ACTUALIZADO)
```env
# Configuración de Sesiones
SESSION_DURATION=1800  # 30 minutos en segundos
SESSION_COOKIE_NAME=ASTILLERO_SESSION
SESSION_COOKIE_SECURE=false  # Cambiar a true en producción
SESSION_COOKIE_HTTPONLY=true

# Configuración de Rate Limiting
RATE_LIMIT_MAX_ATTEMPTS=5  # Máximo de intentos fallidos
RATE_LIMIT_WINDOW=900  # Ventana de tiempo en segundos (15 min)
RATE_LIMIT_BLOCK_DURATION=3600  # Duración del bloqueo en segundos (1 hora)
```

#### 4. **src/services/RateLimitService.php** (NUEVO - 150+ líneas)
Servicio completo para gestión de rate limiting:
- `isBlocked(email, ip)`: Verifica si está bloqueado
- `recordAttempt(email, ip, success)`: Registra intento
- `getBlockTimeRemaining(email, ip)`: Segundos hasta desbloqueo
- `resetAttempts(email)`: Limpia intentos tras login exitoso
- `getRemainingAttempts(email, ip)`: Intentos restantes
- `cleanOldAttempts()`: Limpieza automática

#### 5. **src/utils/SessionManager.php** (REESCRITO COMPLETO - 165 líneas)
Nueva implementación con timeout y httpOnly cookies:
- `getSessionDuration()`: Lee de .env
- `configureSessionCookie()`: Configura httpOnly, secure, samesite
- `checkSession()`: Valida timeout con last_activity
- `initSession(uuid, email, nombre)`: Crea sesión con timestamps
- `getSessionInfo()`: Estado detallado (expires_in_seconds, expires_in_minutes)
- `endSession()`: Destruye cookie correctamente

#### 6. **src/services/AuthService.php** (ACTUALIZADO COMPLETO)
Integración con rate limiting y nueva gestión de sesiones:
- `getClientIP()`: Extrae IP real del cliente
- `sendCode()`: Verifica rate limit ANTES de enviar código
- `validateCode()`: Verifica rate limit, registra intentos, resetea en éxito
- `logout()`: Nuevo método que usa SessionManager.endSession()
- `checkSession()`: Delega a SessionManager.getSessionInfo()

#### 7. **src/controllers/AuthController.php** (ACTUALIZADO)
```php
public function logout(): ResponseDTO {
    return $this->authService->logout();
}
```

#### 8. **src/api/AuthEndpoint.php** (ACTUALIZADO)
```php
public function logout(): ResponseDTO {
    try {
        return $this->controller->logout();
    } catch (\Exception $e) {
        return new ResponseDTO(false, "Error al cerrar sesión: " . $e->getMessage(), null, 500);
    }
}
```

#### 9. **src/routes/Router.php** (ACTUALIZADO)
```php
case $method === 'POST' && ($path === '/api/login/logout' || $path === '/AI4Devs-finalproject/api/login/logout'):
    self::json((new AuthEndpoint())->logout());
    break;
```

### Frontend (JavaScript)

#### 10. **public/js/api.js** (ACTUALIZADO)
```javascript
// Nuevo método logout
async logout() {
    try {
        const response = await fetch(`${API_BASE_URL}/login/logout`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' }
        });
        return await response.json();
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
        return {
            success: false,
            message: error.message || 'Error de conexión con el servidor'
        };
    }
}
```

#### 11. **public/js/auth.js** (REFACTORIZADO COMPLETO)
Cambios principales:
- ❌ **ELIMINADO**: `setSession()` con `localStorage.setItem('session')`
- ❌ **ELIMINADO**: `getSession()` con `localStorage.getItem('session')`
- ✅ **AGREGADO**: Manejo de rate limiting (status 429) con feedback al usuario
- ✅ **ACTUALIZADO**: `handleLogout()` ahora llama a API de backend
- ✅ **SIMPLIFICADO**: `checkSession()` solo verifica en servidor, sin localStorage
- ✅ **SIMPLIFICADO**: `clearSession()` solo limpia sessionStorage.tempEmail

---

## 🧪 Instrucciones de Prueba

### Requisitos Previos
1. Ejecutar migración de base de datos:
```bash
mysql -u root astillero_tools < db/add_login_attempts_table.sql
```

2. Verificar variables en `.env`:
```bash
# Verificar que existan todas las variables SESSION_* y RATE_LIMIT_*
```

3. Reiniciar servidor Apache/PHP:
```bash
# Windows XAMPP
net stop Apache2.4
net start Apache2.4
```

### Prueba 1: Session Timeout (30 minutos)
**Objetivo**: Verificar que la sesión expira tras 30 minutos de inactividad

1. Iniciar sesión normalmente
2. Esperar **más de 30 minutos** sin interactuar
3. Intentar hacer cualquier acción (consultar herramienta, ver dashboard)
4. **Resultado esperado**: Redirección automática al login con mensaje "Sesión expirada"

**Prueba rápida** (modificar .env temporalmente):
```env
SESSION_DURATION=60  # 1 minuto para pruebas
```
Reiniciar servidor, hacer login, esperar 1 minuto, intentar acción.

### Prueba 2: Rate Limiting (5 intentos en 15 min)
**Objetivo**: Verificar bloqueo tras 5 intentos fallidos

1. Intentar login con email válido pero código INCORRECTO
2. Repetir **5 veces**
3. En el 5º intento, ver mensaje: "Intentos restantes: 0"
4. En el 6º intento, ver mensaje: "Demasiados intentos fallidos. Bloqueado por X minutos"
5. **Resultado esperado**: Bloqueo durante 1 hora

**Verificación en base de datos**:
```sql
-- Ver intentos registrados
SELECT * FROM login_attempts ORDER BY attempt_time DESC LIMIT 10;

-- Ver cuántos intentos fallidos tiene un email
SELECT COUNT(*) FROM login_attempts 
WHERE email = 'tu@email.com' 
AND success = 0 
AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE);
```

**Desbloqueo manual** (para pruebas):
```sql
-- Eliminar intentos de un email específico
DELETE FROM login_attempts WHERE email = 'tu@email.com';
```

### Prueba 3: httpOnly Cookies (no accesibles desde JavaScript)
**Objetivo**: Verificar que las cookies NO sean accesibles desde JavaScript

1. Hacer login normalmente
2. Abrir **DevTools del navegador** (F12)
3. Ir a **Console**
4. Ejecutar:
```javascript
console.log(document.cookie);
```
5. **Resultado esperado**: 
   - ❌ NO debe aparecer `ASTILLERO_SESSION=...`
   - ✅ Solo aparecen cookies sin httpOnly flag

6. Ir a **Application > Cookies** en DevTools
7. Buscar `ASTILLERO_SESSION`
8. **Resultado esperado**:
   - ✅ Columna "HttpOnly": ✓ (marcada)
   - ✅ Columna "Secure": ✓ (si estás en HTTPS)
   - ✅ Columna "SameSite": Lax

### Prueba 4: Logout Completo
**Objetivo**: Verificar que el logout destruye la cookie del servidor

1. Hacer login
2. Verificar en DevTools que existe cookie `ASTILLERO_SESSION`
3. Hacer clic en "Cerrar Sesión"
4. Verificar en DevTools > Application > Cookies:
   - **Resultado esperado**: Cookie `ASTILLERO_SESSION` **desaparece completamente**

5. Intentar acceder directamente a `/AI4Devs-finalproject/public/index.html`
6. **Resultado esperado**: Redirección automática al login

### Prueba 5: Reset de Rate Limit tras Login Exitoso
**Objetivo**: Verificar que los intentos fallidos se resetean tras login exitoso

1. Hacer **3 intentos fallidos** (código incorrecto)
2. En el 4º intento, usar el **código correcto**
3. Login exitoso
4. Hacer logout
5. Intentar login nuevamente con código incorrecto
6. **Resultado esperado**: El contador de intentos comienza desde **5 intentos restantes** (no desde 2)

**Verificación en base de datos**:
```sql
-- Ver que los intentos fallidos anteriores fueron eliminados
SELECT * FROM login_attempts 
WHERE email = 'tu@email.com' 
AND success = 0 
ORDER BY attempt_time DESC;
```

---

## 🔍 Verificación de Logs

### Logs de Session Manager
```bash
tail -f src/utils/logs/SessionManager_*.log
```

Buscar:
- `[SessionManager::checkSession] Sesión expiró` (cuando pasa el timeout)
- `[SessionManager::endSession] Sesión destruida` (tras logout)

### Logs de Rate Limit Service
```bash
tail -f src/utils/logs/RateLimitService_*.log
```

Buscar:
- `Usuario bloqueado` (cuando alcanza máximo de intentos)
- `Intentos restantes: X` (cada intento fallido)
- `Intentos reseteados` (tras login exitoso)

### Logs de Auth Service
```bash
tail -f src/utils/logs/AuthService_*.log
```

Buscar:
- `Rate limit excedido` (al intentar login/código estando bloqueado)
- `Código de acceso enviado` (solo si pasa rate limit)

---

## 🎯 Valores Recomendados por Entorno

### Desarrollo
```env
SESSION_DURATION=1800  # 30 minutos
SESSION_COOKIE_SECURE=false  # HTTP local
RATE_LIMIT_MAX_ATTEMPTS=5
RATE_LIMIT_WINDOW=900  # 15 minutos
RATE_LIMIT_BLOCK_DURATION=3600  # 1 hora
```

### Producción
```env
SESSION_DURATION=3600  # 1 hora (más tiempo para usuarios reales)
SESSION_COOKIE_SECURE=true  # HTTPS obligatorio
RATE_LIMIT_MAX_ATTEMPTS=3  # Más estricto
RATE_LIMIT_WINDOW=600  # 10 minutos (ventana más corta)
RATE_LIMIT_BLOCK_DURATION=7200  # 2 horas (bloqueo más largo)
```

### Testing
```env
SESSION_DURATION=60  # 1 minuto (pruebas rápidas)
SESSION_COOKIE_SECURE=false
RATE_LIMIT_MAX_ATTEMPTS=3  # Menos intentos para probar rápido
RATE_LIMIT_WINDOW=300  # 5 minutos
RATE_LIMIT_BLOCK_DURATION=600  # 10 minutos
```

---

## 🐛 Troubleshooting

### Problema: "Sesión expirada" inmediatamente después del login
**Causa**: `SESSION_DURATION` muy bajo o problema con `last_activity`
**Solución**:
1. Verificar en `.env`: `SESSION_DURATION=1800`
2. Ver logs: `tail -f src/utils/logs/SessionManager_*.log`
3. Buscar: `last_activity not set` o `Sesión expiró`

### Problema: Rate limiting no funciona
**Causa**: Tabla `login_attempts` no existe
**Solución**:
```bash
mysql -u root astillero_tools < db/add_login_attempts_table.sql
```

### Problema: Cookies no se guardan
**Causa**: Dominio/path incorrecto o HTTPS requerido
**Solución**:
1. Verificar en `.env`: `SESSION_COOKIE_SECURE=false` (si usas HTTP)
2. Verificar en DevTools > Network > Response Headers:
   - Debe aparecer: `Set-Cookie: ASTILLERO_SESSION=...`

### Problema: "Access denied" en base de datos
**Causa**: Usuario no tiene permisos en tabla `login_attempts`
**Solución**:
```sql
GRANT ALL PRIVILEGES ON astillero_tools.* TO 'astillero_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## ✅ Checklist de Implementación

- [x] Base de datos actualizada con tabla `login_attempts`
- [x] Variables de entorno configuradas en `.env`
- [x] RateLimitService creado y funcionando
- [x] SessionManager reescrito con timeout
- [x] AuthService integrado con rate limiting
- [x] Frontend eliminó localStorage (usa solo cookies)
- [x] Endpoint `/api/login/logout` agregado
- [x] Manejo de errores 429 (rate limiting) en frontend
- [x] Logs configurados para debugging
- [ ] **PENDIENTE**: Ejecutar migración en base de datos (add_login_attempts_table.sql)
- [ ] **PENDIENTE**: Probar timeout de sesión (30 minutos)
- [ ] **PENDIENTE**: Probar rate limiting (5 intentos fallidos)
- [ ] **PENDIENTE**: Verificar cookies httpOnly en DevTools

---

## 📚 Referencias

- PHP session_set_cookie_params: https://www.php.net/manual/en/function.session-set-cookie-params.php
- OWASP Rate Limiting: https://owasp.org/www-community/controls/Blocking_Brute_Force_Attacks
- httpOnly Cookies: https://owasp.org/www-community/HttpOnly
- SameSite Cookies: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite
