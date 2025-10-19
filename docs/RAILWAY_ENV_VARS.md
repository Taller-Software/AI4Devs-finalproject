# 🔧 Configuración de Variables de Entorno en Railway

## ❗ IMPORTANTE: Base de Datos MySQL

La aplicación **requiere** una base de datos MySQL configurada. Sigue estos pasos:

---

## 📋 Paso 1: Añadir Plugin de MySQL

1. En tu proyecto de Railway, haz clic en **"+ New"**
2. Selecciona **"Database" → "Add MySQL"**
3. Railway creará automáticamente una instancia MySQL

---

## 📋 Paso 2: Obtener Variables de Conexión

1. Haz clic en el servicio **MySQL** que acabas de crear
2. Ve a la pestaña **"Variables"**
3. Copia las siguientes variables (aparecen automáticamente):
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`
   - `MYSQLDATABASE`

---

## 📋 Paso 3: Configurar Variables en la Aplicación

1. Haz clic en tu servicio de **aplicación** (AI4Devs-finalproject)
2. Ve a la pestaña **"Variables"**
3. Añade las siguientes variables manualmente:

### 🗄️ Variables de Base de Datos

```bash
# Conexión MySQL
DB_HOST=${MYSQLHOST}          # O usa la variable directamente
DB_PORT=${MYSQLPORT}          # Por defecto: 3306
DB_NAME=${MYSQLDATABASE}      # Nombre de tu base de datos
DB_USER=${MYSQLUSER}          # Usuario MySQL
DB_PASSWORD=${MYSQLPASSWORD}  # Contraseña MySQL
```

### 📧 Variables de Email (Gmail SMTP)

```bash
# SMTP Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu-email@gmail.com
SMTP_PASS=xxxx xxxx xxxx xxxx  # App Password de Gmail (ver abajo)
SMTP_FROM_EMAIL=tu-email@gmail.com
SMTP_FROM_NAME=Astillero La Roca
```

**⚠️ Cómo obtener Gmail App Password:**
1. Ve a https://myaccount.google.com/apppasswords
2. Selecciona "Mail" y "Other (custom name)"
3. Escribe "Railway - Astillero"
4. Copia el password de 16 caracteres
5. Pégalo en `SMTP_PASS`

### 🔐 Variables de Seguridad

```bash
# Session Configuration
SESSION_DURATION=1800         # 30 minutos en segundos
SESSION_COOKIE_LIFETIME=1800  # 30 minutos

# Rate Limiting
RATE_LIMIT_MAX_ATTEMPTS=5     # Máximo intentos de login
RATE_LIMIT_WINDOW_SECONDS=900 # Ventana de 15 minutos
RATE_LIMIT_BLOCK_DURATION=1800 # Bloqueo de 30 minutos

# Other
PORT=$PORT                     # Railway lo asigna automáticamente
```

---

## 📋 Paso 4: Conectar MySQL a la Aplicación

**Opción A: Usar Referencias (Recomendado)**

Railway puede vincular automáticamente las variables:

1. En tu aplicación, ve a **Settings**
2. En **Service Variables**, añade referencias:
   ```
   DB_HOST = ${{MySQL.MYSQLHOST}}
   DB_PORT = ${{MySQL.MYSQLPORT}}
   DB_USER = ${{MySQL.MYSQLUSER}}
   DB_PASSWORD = ${{MySQL.MYSQLPASSWORD}}
   DB_NAME = ${{MySQL.MYSQLDATABASE}}
   ```

**Opción B: Copiar Manualmente**

Copia los valores directamente desde el servicio MySQL.

---

## 📋 Paso 5: Inicializar la Base de Datos

### Método 1: Script Automático (Recomendado)

Después de configurar las variables, ejecuta en Railway:

```bash
cd /app && chmod +x railway-init-db.sh && ./railway-init-db.sh
```

### Método 2: Manual (MySQL Client)

1. Conecta a MySQL desde tu terminal local:
   ```bash
   mysql -h MYSQLHOST -P MYSQLPORT -u MYSQLUSER -p
   ```

2. Copia y pega el contenido de `db/schema.sql`

3. Copia y pega el contenido de `db/data.sql`

---

## ✅ Verificación

1. Abre `https://tu-app.up.railway.app/`
2. Si ves la pantalla de login sin errores → ✅ Configurado correctamente
3. Si ves "Base de datos no configurada" → ❌ Revisa las variables

---

## 🔍 Troubleshooting

### Error: "Base de datos no configurada"

**Causa**: Variables de entorno no configuradas

**Solución**:
1. Ve a Railway → Tu App → Variables
2. Verifica que `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` existan
3. Redeploy la aplicación

### Error: "SQLSTATE[HY000] [2002] Connection refused"

**Causa**: MySQL no está accesible

**Solución**:
1. Verifica que el plugin MySQL esté **Running** (verde)
2. Verifica que `DB_HOST` apunte al host correcto
3. Verifica que `DB_PORT` sea 3306

### Error: "SQLSTATE[HY000] [1045] Access denied"

**Causa**: Credenciales incorrectas

**Solución**:
1. Copia de nuevo `DB_USER` y `DB_PASSWORD` desde MySQL
2. Asegúrate de no tener espacios extra
3. Redeploy

### Error: "Table doesn't exist"

**Causa**: Base de datos no inicializada

**Solución**:
1. Ejecuta `railway-init-db.sh` (ver Paso 5)
2. O ejecuta manualmente `db/schema.sql` y `db/data.sql`

---

## 📊 Variables Completas de Referencia

```bash
# Base de Datos
DB_HOST=containers-us-west-XXX.railway.app
DB_PORT=3306
DB_NAME=railway
DB_USER=root
DB_PASSWORD=XXXXXXXXXXXXXXXX

# SMTP
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu-email@gmail.com
SMTP_PASS=xxxx xxxx xxxx xxxx
SMTP_FROM_EMAIL=tu-email@gmail.com
SMTP_FROM_NAME=Astillero La Roca

# Seguridad
SESSION_DURATION=1800
SESSION_COOKIE_LIFETIME=1800
RATE_LIMIT_MAX_ATTEMPTS=5
RATE_LIMIT_WINDOW_SECONDS=900
RATE_LIMIT_BLOCK_DURATION=1800

# Railway
PORT=$PORT
```

---

## 🎯 Después de Configurar

1. ✅ Todas las variables configuradas
2. ✅ MySQL plugin corriendo
3. ✅ Base de datos inicializada
4. ✅ Redeploy de la aplicación
5. 🎉 ¡La aplicación debería funcionar!

---

**Última actualización**: Octubre 2025
