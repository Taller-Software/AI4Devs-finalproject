# 🔒 Guía de Seguridad - Sistema de Gestión de Herramientas

## ⚠️ DATOS SENSIBLES - NO SUBIR AL REPOSITORIO

### Archivos que NUNCA deben estar en Git:

✅ **El .gitignore está configurado para proteger:**

1. **`.env`** - Contiene:
   - Credenciales de base de datos
   - Contraseñas SMTP (email)
   - Configuración de sesiones
   - Tokens y claves secretas

2. **Logs** - Pueden contener:
   - Información de usuarios
   - Datos de sesiones
   - Stack traces con rutas del servidor
   - IPs y datos de conexión

3. **Certificados SSL** (`.pem`, `.key`, `.crt`)
   - Claves privadas
   - Certificados SSL/TLS

4. **Backups de BD** (`.sql`, `.sql.gz`)
   - Datos completos de usuarios
   - Información empresarial sensible

5. **Archivos de sesión**
   - Tokens de sesión activos
   - Datos temporales de usuarios

---

## 🛡️ Configuración Segura del .env

### Para Desarrollo Local:
```bash
APP_ENV=development
APP_DEBUG=true  # Solo en local
SESSION_COOKIE_SECURE=false  # Solo porque no hay HTTPS local
```

### Para Producción:
```bash
APP_ENV=production
APP_DEBUG=false  # ¡CRÍTICO! Nunca true en producción
SESSION_COOKIE_SECURE=true  # Requiere HTTPS
LOG_LEVEL=error  # No logear información de debug
```

---

## 🔐 Credenciales SMTP de Gmail

### Cómo obtener una "App Password" segura:

1. Ve a tu cuenta de Google: https://myaccount.google.com/
2. Seguridad → Verificación en 2 pasos (debes activarla)
3. Contraseñas de aplicaciones
4. Genera una nueva contraseña específica para esta app
5. Úsala en `SMTP_PASS` del `.env`

**⚠️ NUNCA uses tu contraseña real de Gmail**

---

## 🚨 Verificación de Seguridad

### Antes de hacer commit, verifica:

```bash
# 1. Verifica que .env NO esté en Git
git ls-files .env
# Debe devolver vacío

# 2. Verifica archivos staged
git status
# No debe aparecer .env, *.log, *.sql

# 3. Busca credenciales accidentalmente commiteadas
git log -p | grep -i "password\|secret\|smtp_pass"
# No debe encontrar nada sensible
```

---

## 🔄 Si Accidentalmente Subiste Datos Sensibles

### ⚠️ ACCIÓN INMEDIATA:

1. **Cambiar TODAS las credenciales expuestas**
   - Nueva contraseña de base de datos
   - Nueva App Password de Gmail
   - Regenerar tokens/claves

2. **Limpiar historial de Git:**
   ```bash
   # Eliminar archivo del historial completo
   git filter-branch --force --index-filter \
     "git rm --cached --ignore-unmatch .env" \
     --prune-empty --tag-name-filter cat -- --all
   
   # Forzar push
   git push origin --force --all
   ```

3. **Notificar al equipo** del incidente

---

## 📋 Checklist de Seguridad Pre-Deploy

Antes de desplegar a producción:

- [ ] `.env` NO está en el repositorio
- [ ] `.env.example` NO tiene credenciales reales
- [ ] `APP_DEBUG=false` en producción
- [ ] `SESSION_COOKIE_SECURE=true` con HTTPS
- [ ] Base de datos con usuario limitado (no root)
- [ ] SMTP con App Password (no contraseña real)
- [ ] Firewall configurado (puerto 443, 80, 3306 solo local)
- [ ] Logs rotan automáticamente
- [ ] Backups encriptados y almacenados fuera del servidor web
- [ ] Certificado SSL válido instalado
- [ ] Headers de seguridad configurados (CSP, HSTS, X-Frame-Options)

---

## 🔍 Auditoría de Seguridad

### Herramientas recomendadas:

1. **git-secrets** - Previene commits con secretos
   ```bash
   git secrets --install
   git secrets --register-aws
   ```

2. **truffleHog** - Escanea historial buscando secretos
   ```bash
   truffleHog git file://path/to/repo
   ```

3. **PHP Security Checker**
   ```bash
   composer require --dev sensiolabs/security-checker
   php vendor/bin/security-checker security:check
   ```

---

## 📞 Contacto en Caso de Incidente

Si detectas una brecha de seguridad:
1. No crear issue público en GitHub
2. Contactar directamente al equipo de desarrollo
3. Documentar el incidente (fecha, alcance, datos expuestos)

---

## 📚 Recursos Adicionales

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [GitHub Secret Scanning](https://docs.github.com/en/code-security/secret-scanning)

---

**Última actualización:** 2025-10-19
**Revisión de seguridad:** Cada 3 meses
