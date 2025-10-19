# Configuración de Variables de Entorno en Railway

## Problema
El endpoint `/api/check-db` responde con:
```json
{
  "success": false,
  "message": "Variables de entorno de base de datos no configuradas",
  "debug": {
    "DB_HOST": "MISSING",
    "DB_NAME": "MISSING",
    "DB_USER": "MISSING",
    "DB_PASS": "MISSING"
  }
}
```

## Solución

### Opción 1: Usar Base de Datos MySQL de Railway (Recomendado)

1. **Crear el servicio de MySQL en Railway:**
   - En tu proyecto de Railway, haz clic en `+ New`
   - Selecciona `Database` → `Add MySQL`
   - Railway creará automáticamente un servicio MySQL

2. **Configurar las variables en tu servicio web:**
   
   Railway expone automáticamente las variables del MySQL como `MYSQLHOST`, `MYSQLDATABASE`, etc. Tu código ya detecta estas variables automáticamente, pero para mayor claridad puedes configurar aliases:

   - Ve a tu servicio web (el que corre la aplicación PHP)
   - Haz clic en la pestaña `Variables`
   - **IMPORTANTE:** Verifica el nombre exacto de tu servicio MySQL (puede ser `MySQL`, `mysql`, o tener otro nombre)
   - Agrega las siguientes variables usando referencias al servicio MySQL:

   ```
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_NAME=${{MySQL.MYSQLDATABASE}}
   DB_USER=${{MySQL.MYSQLUSER}}
   DB_PASS=${{MySQL.MYSQLPASSWORD}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   ```

   **Nota:** Reemplaza `MySQL` con el nombre exacto de tu servicio MySQL si es diferente. Puedes verificarlo en la lista de servicios de tu proyecto.

   **Alternativa - Sin aliases:** El código ya detecta automáticamente `MYSQLHOST`, `MYSQLDATABASE`, `MYSQLUSER`, y `MYSQLPASSWORD`, así que técnicamente no necesitas agregar estas variables si Railway ya las expone.

### Opción 2: Usar Base de Datos Externa

Si ya tienes una base de datos MySQL externa:

1. Ve a tu servicio en Railway
2. Haz clic en la pestaña `Variables`
3. Agrega manualmente:
   ```
   DB_HOST=tu-host.com
   DB_NAME=nombre_de_tu_bd
   DB_USER=tu_usuario
   DB_PASS=tu_contraseña
   DB_PORT=3306
   ```

### Opción 3: Usar PostgreSQL de Railway

Si prefieres PostgreSQL:

1. En tu proyecto de Railway, haz clic en `+ New`
2. Selecciona `Database` → `Add PostgreSQL`
3. Configura las variables en tu servicio web:
   ```
   DB_HOST=${{Postgres.PGHOST}}
   DB_NAME=${{Postgres.PGDATABASE}}
   DB_USER=${{Postgres.PGUSER}}
   DB_PASS=${{Postgres.PGPASSWORD}}
   DB_PORT=${{Postgres.PGPORT}}
   DB_TYPE=pgsql
   ```

## Verificación

Después de configurar las variables:

1. Railway redesplegará automáticamente tu aplicación
2. Verifica que las variables se hayan aplicado:
   - Ve a `https://tu-app.up.railway.app/api/railway-debug`
   - Deberías ver las variables configuradas (las contraseñas estarán ocultas)

3. Verifica la conexión a la base de datos:
   - Ve a `https://tu-app.up.railway.app/api/check-db`
   - Deberías ver un mensaje de éxito

## Inicializar la Base de Datos

Una vez configuradas las variables:

1. Ejecuta el script de inicialización:
   ```
   https://tu-app.up.railway.app/api/init
   ```

2. Este script:
   - Creará las tablas necesarias (`usuarios`, `herramientas`, `ubicaciones`, `historico_uso`, `codigos_login`, `intentos_login`)
   - Insertará datos iniciales
   - Te mostrará un mensaje de éxito

## Solución de Problemas

### Error: "Class AuthController not found"
Este error ya fue corregido en el código. Asegúrate de:
1. Hacer commit y push de los cambios recientes
2. Railway redesplegará automáticamente

### Error de conexión a la base de datos
- Verifica que las variables estén correctamente escritas
- Si usas referencias (`${{...}}`), verifica que el nombre del servicio sea correcto
- Revisa los logs en Railway para más detalles

### Variables no se aplican
- Las variables de entorno solo se aplican después de un redespliegue
- Railway redesplega automáticamente al cambiar variables
- Espera unos segundos y verifica nuevamente

## Variables Adicionales Recomendadas

Además de las variables de base de datos, considera configurar:

```
# Entorno
APP_ENV=production

# Email (para envío de códigos de login)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu-email@gmail.com
SMTP_PASS=tu-app-password
SMTP_FROM=noreply@tuapp.com
SMTP_FROM_NAME=Tu Aplicación

# Seguridad
SESSION_LIFETIME=3600
CSRF_TOKEN_LIFETIME=7200
```

## Comandos Útiles en Railway CLI (Opcional)

Si prefieres usar la línea de comandos:

```bash
# Instalar Railway CLI
npm i -g @railway/cli

# Login
railway login

# Listar variables
railway variables

# Agregar variable
railway variables set DB_HOST=valor

# Ver logs
railway logs
```
