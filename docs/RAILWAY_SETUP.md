# 🚂 Guía Completa de Deploy en Railway

## 📋 Checklist Pre-Deploy

Antes de desplegar, asegúrate de tener:

- [ ] Cuenta en Railway.app creada
- [ ] Repositorio en GitHub con el código actualizado
- [ ] Gmail App Password generada (para SMTP)
- [ ] Acceso a las credenciales necesarias

## 📦 Archivos de Configuración Railway

El proyecto incluye los siguientes archivos para Railway:

- **`nixpacks.toml`**: Configuración principal de Railway (instala PHP 8.2 y Composer)
- **`package.json`**: Define el comando de inicio del servidor
- **`railway-router.php`**: Router simplificado para servir la aplicación
- **`railway-init-db.sh`**: Script para inicializar la base de datos

---

## 🚀 Paso 1: Crear Proyecto en Railway

### 1.1 Desde GitHub
```bash
1. Ve a https://railway.app
2. Click en "Start a New Project"
3. Selecciona "Deploy from GitHub repo"
4. Autoriza a Railway para acceder a tus repos
5. Selecciona "Taller-Software/AI4Devs-finalproject"
6. Railway detectará automáticamente que es un proyecto PHP
```

### 1.2 Configuración Inicial
```
Railway creará un servicio llamado "AI4Devs-finalproject"
El proyecto se desplegará automáticamente
```

---

## 🗄️ Paso 2: Agregar Base de Datos MySQL

### 2.1 Crear Servicio MySQL
```bash
1. En tu proyecto Railway, click en "+ New"
2. Selecciona "Database" → "Add MySQL"
3. Railway creará una instancia MySQL automáticamente
```

### 2.2 Obtener Credenciales
```bash
1. Click en el servicio MySQL
2. Ve a la pestaña "Variables"
3. Copia estas credenciales:
   - MYSQLHOST
   - MYSQLPORT
   - MYSQLUSER
   - MYSQLPASSWORD
   - MYSQLDATABASE
```

### 2.3 Importar Schema y Datos

**Opción A: Desde Railway CLI**
```bash
# Instalar Railway CLI
npm install -g @railway/cli

# Login
railway login

# Conectar al proyecto
railway link

# Importar schema
railway run mysql -h $MYSQLHOST -P $MYSQLPORT -u $MYSQLUSER -p$MYSQLPASSWORD $MYSQLDATABASE < db/schema.sql

# Importar datos
railway run mysql -h $MYSQLHOST -P $MYSQLPORT -u $MYSQLUSER -p$MYSQLPASSWORD $MYSQLDATABASE < db/data.sql
```

**Opción B: Desde MySQL Workbench/Adminer**
```bash
1. Conecta usando las credenciales de Railway
2. Ejecuta manualmente:
   - db/schema.sql
   - db/data.sql
```

---

## ⚙️ Paso 3: Configurar Variables de Entorno

### 3.1 En Railway Dashboard
```bash
1. Click en tu servicio web (AI4Devs-finalproject)
2. Ve a "Variables"
3. Click en "RAW Editor"
4. Pega el siguiente contenido (ajustando los valores):
```

### 3.2 Variables a Configurar

```env
# Aplicación
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-proyecto.up.railway.app

# Base de Datos (Usar las credenciales de Railway MySQL)
DB_HOST=${{MySQL.MYSQLHOST}}
DB_NAME=${{MySQL.MYSQLDATABASE}}
DB_USER=${{MySQL.MYSQLUSER}}
DB_PASS=${{MySQL.MYSQLPASSWORD}}
DB_PORT=${{MySQL.MYSQLPORT}}

# SMTP Gmail (Usar tu App Password)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu-email@gmail.com
SMTP_PASS=tu-app-password-16-caracteres
SMTP_FROM_EMAIL=tu-email@gmail.com
SMTP_FROM_NAME=Astillero La Roca

# Sesión
SESSION_DURATION=1800
SESSION_COOKIE_NAME=ASTILLERO_SESSION
SESSION_COOKIE_SECURE=true
SESSION_COOKIE_HTTPONLY=true

# Rate Limiting
RATE_LIMIT_MAX_ATTEMPTS=5
RATE_LIMIT_WINDOW=900
RATE_LIMIT_BLOCK_DURATION=3600

# Logs
LOG_ENABLED=true
LOG_PATH=/tmp/logs
LOG_LEVEL=error

# API
API_VERSION=1.0.0
API_BASE_PATH=/api
```

### 3.3 Referencia de Variables MySQL
Railway proporciona referencias automáticas:
```
${{MySQL.MYSQLHOST}}      → Host de la BD
${{MySQL.MYSQLDATABASE}}  → Nombre de la BD
${{MySQL.MYSQLUSER}}      → Usuario
${{MySQL.MYSQLPASSWORD}}  → Contraseña
${{MySQL.MYSQLPORT}}      → Puerto
```

---

## 🔄 Paso 4: Deploy

### 4.1 Commit y Push
```bash
# Asegúrate de estar en develop
git status

# Agregar cambios
git add .

# Commit
git commit -m "feat: Configuración para Railway"

# Push
git push origin develop
```

### 4.2 Railway Deploy Automático
```
Railway detectará el push y desplegará automáticamente
Puedes ver el progreso en:
Railway Dashboard → Deployments → View Logs
```

### 4.3 Verificar Deploy
```bash
1. Espera a que el deploy termine (1-3 minutos)
2. Verás "Build successful" y "Deployment live"
3. Click en la URL generada (ej: tu-proyecto.up.railway.app)
```

---

## 🧪 Paso 5: Verificación y Pruebas

### 5.1 Verificar que la App Carga
```bash
1. Abre la URL de Railway en tu navegador
2. Deberías ver la pantalla de login
```

### 5.2 Probar Flujo de Login
```bash
1. Ingresa un email de usuario (debe existir en la BD)
2. Click en "Solicitar Código"
3. Verifica que recibes el email
4. Ingresa el código
5. Verifica que accedes al dashboard
```

### 5.3 Ver Logs en Tiempo Real
```bash
# Opción 1: Railway Dashboard
Railway → Tu Servicio → Logs

# Opción 2: Railway CLI
railway logs
```

---

## 🐛 Troubleshooting

### Error: "Application failed to respond"
```bash
Causa: El puerto no está configurado correctamente
Solución: Verifica que Procfile use $PORT
```

### Error: "Database connection refused"
```bash
Causa: Variables de BD incorrectas
Solución:
1. Verifica las variables DB_*
2. Asegúrate de usar referencias ${{MySQL.*}}
3. Restart el servicio
```

### Error: "SMTP authentication failed"
```bash
Causa: Credenciales Gmail incorrectas
Solución:
1. Verifica que SMTP_PASS sea App Password (16 caracteres sin espacios)
2. No uses tu contraseña regular de Gmail
3. Genera nuevo App Password en Google Account
```

### Error: "Session not persisting"
```bash
Causa: SESSION_COOKIE_SECURE debe ser true en producción
Solución: Ya está configurado en variables de entorno
```

### Error: "404 Not Found" en assets
```bash
Causa: Rutas incorrectas
Solución: api.js detecta automáticamente el entorno
```

---

## 📊 Monitoreo

### Ver Métricas
```bash
Railway Dashboard → Tu Servicio → Metrics
- CPU Usage
- Memory Usage
- Network
```

### Ver Logs de Errores
```bash
Railway Dashboard → Tu Servicio → Logs
Filtra por: "error" o "exception"
```

---

## 🔧 Comandos Útiles

### Railway CLI
```bash
# Instalar
npm install -g @railway/cli

# Login
railway login

# Link proyecto
railway link

# Ver logs
railway logs

# Ejecutar comando en Railway
railway run [comando]

# Abrir Dashboard
railway open
```

### Git
```bash
# Ver rama actual
git branch

# Cambiar a main
git checkout main

# Merge develop → main
git merge develop

# Push a Railway (auto-deploy)
git push origin main
```

---

## 🎯 URLs Importantes

| Servicio | URL |
|----------|-----|
| Railway Dashboard | https://railway.app/dashboard |
| Tu Aplicación | https://[tu-proyecto].up.railway.app |
| Railway Docs | https://docs.railway.app |
| Railway Status | https://status.railway.app |

---

## ✅ Checklist Post-Deploy

- [ ] Aplicación carga correctamente
- [ ] Base de datos conectada
- [ ] Login funciona
- [ ] Emails se envían correctamente
- [ ] Sesiones persisten
- [ ] Rate limiting funciona
- [ ] Todas las funcionalidades probadas
- [ ] Variables de entorno verificadas
- [ ] Logs monitoreados
- [ ] URL compartida con el equipo

---

## 🚀 Próximos Pasos

1. **Configurar Dominio Custom** (opcional)
   ```bash
   Railway Dashboard → Settings → Custom Domain
   ```

2. **Configurar Backups Automáticos**
   ```bash
   Considera usar Railway Cron Jobs para backups
   ```

3. **Monitoreo y Alertas**
   ```bash
   Configura notificaciones en Railway
   ```

4. **CI/CD Avanzado**
   ```bash
   Configura GitHub Actions para testing antes de deploy
   ```

---

**¿Necesitas ayuda?** 
- Railway Discord: https://discord.gg/railway
- Documentación: https://docs.railway.app

---

**Fecha de actualización:** 2025-10-19  
**Versión:** 1.0  
**Autor:** AI4Devs Team
