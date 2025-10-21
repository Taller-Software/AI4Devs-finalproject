# 🚀 Guía de Despliegue a Producción

**Proyecto:** Gestor de Herramientas - Astillero La Roca  
**Versión:** 1.0 (Post-Limpieza)  
**Fecha:** Octubre 2025

---

## 📋 Pre-requisitos

### Software Necesario
- **PHP:** 8.2 o superior
- **MySQL:** 8.0 o superior
- **Servidor Web:** Apache 2.4+ o Nginx 1.18+
- **Composer:** Última versión
- **SSL/TLS:** Certificado válido (Let's Encrypt recomendado)

### Extensiones PHP Requeridas
```bash
php -m | grep -E "pdo|mysqli|mbstring|openssl|curl|json"
```

Debe mostrar:
- pdo_mysql
- mysqli
- mbstring
- openssl
- curl
- json

---

## 🔧 Configuración del Servidor

### 1. Apache Configuration

Crear archivo de configuración: `/etc/apache2/sites-available/astillero.conf`

```apache
<VirtualHost *:443>
    ServerName astillero.tusitio.com
    ServerAlias www.astillero.tusitio.com
    
    DocumentRoot /var/www/astillero/public
    
    <Directory /var/www/astillero/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Seguridad adicional
        <FilesMatch "\.php$">
            Require all granted
        </FilesMatch>
    </Directory>
    
    # Bloquear acceso a archivos sensibles
    <FilesMatch "^\.env">
        Require all denied
    </FilesMatch>
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/chain.crt
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/astillero_error.log
    CustomLog ${APACHE_LOG_DIR}/astillero_access.log combined
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName astillero.tusitio.com
    ServerAlias www.astillero.tusitio.com
    
    Redirect permanent / https://astillero.tusitio.com/
</VirtualHost>
```

Activar módulos necesarios:
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo a2ensite astillero.conf
sudo systemctl reload apache2
```

### 2. Nginx Configuration (Alternativa)

Crear archivo: `/etc/nginx/sites-available/astillero`

```nginx
server {
    listen 80;
    server_name astillero.tusitio.com www.astillero.tusitio.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name astillero.tusitio.com www.astillero.tusitio.com;
    
    root /var/www/astillero/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # PHP Processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Block access to sensitive files
    location ~ /\.env {
        deny all;
    }
    
    location ~ /\.git {
        deny all;
    }
    
    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
    
    # Logs
    access_log /var/log/nginx/astillero_access.log;
    error_log /var/log/nginx/astillero_error.log;
}
```

Activar configuración:
```bash
sudo ln -s /etc/nginx/sites-available/astillero /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 📦 Instalación del Proyecto

### 1. Clonar el Repositorio
```bash
cd /var/www
sudo git clone https://github.com/tu-usuario/AI4Devs-finalproject.git astillero
cd astillero
```

### 2. Permisos del Sistema
```bash
# Dar permisos al usuario del servidor web
sudo chown -R www-data:www-data /var/www/astillero
sudo chmod -R 755 /var/www/astillero

# Permisos específicos para logs
sudo chmod -R 775 /var/www/astillero/src/utils/logs
```

### 3. Instalar Dependencias
```bash
composer install --no-dev --optimize-autoloader
```

### 4. Configuración del .env

Copiar y editar archivo de configuración:
```bash
cp .env.example .env
nano .env
```

**Configuración de PRODUCCIÓN:**
```env
# Environment
ENVIRONMENT=production

# Database
DB_HOST=localhost
DB_NAME=astillero_prod
DB_USER=astillero_user
DB_PASSWORD=CONTRASEÑA_SEGURA_AQUI
DB_CHARSET=utf8mb4

# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password-de-aplicacion
MAIL_ENCRYPTION=tls
MAIL_FROM=noreply@astillero.com
MAIL_FROM_NAME=Astillero La Roca

# Session Configuration
SESSION_COOKIE_NAME=ASTILLERO_SESSION
SESSION_DURATION=1800
SESSION_COOKIE_SECURE=true     # ← IMPORTANTE: true en producción con HTTPS
SESSION_COOKIE_HTTPONLY=true
SESSION_COOKIE_SAMESITE=Lax

# Rate Limiting
RATE_LIMIT_MAX_ATTEMPTS=5
RATE_LIMIT_WINDOW=900
RATE_LIMIT_BLOCK_DURATION=3600

# Paths (ajustar según tu configuración)
API_BASE_URL=/api
```

**⚠️ IMPORTANTE:** 
- Usa contraseñas fuertes y únicas
- `SESSION_COOKIE_SECURE=true` requiere HTTPS
- No subas el archivo `.env` a Git

### 5. Crear Base de Datos

```bash
# Conectar a MySQL
mysql -u root -p

# Crear base de datos y usuario
CREATE DATABASE astillero_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'astillero_user'@'localhost' IDENTIFIED BY 'CONTRASEÑA_SEGURA_AQUI';
GRANT ALL PRIVILEGES ON astillero_prod.* TO 'astillero_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Importar esquema
mysql -u astillero_user -p astillero_prod < db/schema.sql

# Importar datos iniciales
mysql -u astillero_user -p astillero_prod < db/data.sql
```

### 6. Verificar Instalación

Visita: `https://astillero.tusitio.com/src/api/check-db.php`

Debe mostrar:
```json
{
  "success": true,
  "message": "Base de datos configurada correctamente"
}
```

---

## 🔒 Configuración de Seguridad

### 1. SSL/TLS con Let's Encrypt

```bash
# Instalar Certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-apache

# Obtener certificado (Apache)
sudo certbot --apache -d astillero.tusitio.com -d www.astillero.tusitio.com

# O para Nginx
sudo certbot --nginx -d astillero.tusitio.com -d www.astillero.tusitio.com

# Renovación automática (ya configurado por Certbot)
sudo certbot renew --dry-run
```

### 2. Firewall (UFW)

```bash
# Habilitar firewall
sudo ufw enable

# Permitir SSH
sudo ufw allow 22/tcp

# Permitir HTTP y HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Verificar estado
sudo ufw status
```

### 3. Configuración PHP

Editar `/etc/php/8.2/apache2/php.ini`:

```ini
# Seguridad
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

# Session
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
session.cookie_samesite = "Lax"

# Upload
upload_max_filesize = 10M
post_max_size = 10M

# Memory
memory_limit = 256M
max_execution_time = 60
```

Reiniciar Apache/PHP-FPM:
```bash
sudo systemctl restart apache2
# O para Nginx
sudo systemctl restart php8.2-fpm
```

### 4. Backup de Base de Datos

Crear script de backup: `/usr/local/bin/backup-astillero.sh`

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/astillero"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="astillero_prod"
DB_USER="astillero_user"
DB_PASS="TU_PASSWORD"

mkdir -p $BACKUP_DIR

# Backup de base de datos
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Mantener solo últimos 30 días
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completado: backup_$DATE.sql.gz"
```

Dar permisos y programar:
```bash
sudo chmod +x /usr/local/bin/backup-astillero.sh

# Agregar a crontab (backup diario a las 2 AM)
sudo crontab -e
```

Agregar línea:
```
0 2 * * * /usr/local/bin/backup-astillero.sh >> /var/log/backup-astillero.log 2>&1
```

---

## 📊 Monitoreo y Logs

### 1. Logs del Sistema

**Ver logs en tiempo real:**
```bash
# Logs de Apache
sudo tail -f /var/log/apache2/astillero_error.log

# Logs de Nginx
sudo tail -f /var/log/nginx/astillero_error.log

# Logs de PHP
sudo tail -f /var/log/php/error.log

# Logs de la aplicación
sudo tail -f /var/www/astillero/src/utils/logs/app.log
```

### 2. Monitoreo de Sesiones

Consulta SQL para ver sesiones activas:
```sql
SELECT 
    COUNT(*) as sesiones_activas,
    DATE_FORMAT(FROM_UNIXTIME(last_activity), '%Y-%m-%d %H:%i:%s') as ultima_actividad
FROM sesiones
WHERE last_activity > UNIX_TIMESTAMP() - 1800
GROUP BY DATE(FROM_UNIXTIME(last_activity));
```

### 3. Limpieza de Sesiones Antiguas

Crear script: `/usr/local/bin/clean-sessions.sh`

```bash
#!/bin/bash
mysql -u astillero_user -pTU_PASSWORD astillero_prod <<EOF
DELETE FROM sesiones WHERE last_activity < UNIX_TIMESTAMP() - 86400;
DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR);
EOF

echo "Sesiones antiguas eliminadas: $(date)"
```

Programar en crontab (cada hora):
```
0 * * * * /usr/local/bin/clean-sessions.sh >> /var/log/clean-sessions.log 2>&1
```

---

## ✅ Checklist de Despliegue

### Pre-Despliegue
- [ ] Backup de base de datos actual (si aplica)
- [ ] Certificado SSL configurado y válido
- [ ] Archivo `.env` con configuración de producción
- [ ] `SESSION_COOKIE_SECURE=true` en .env
- [ ] Firewall configurado
- [ ] PHP configurado para producción
- [ ] Permisos de archivos correctos

### Durante el Despliegue
- [ ] Código clonado en `/var/www/astillero`
- [ ] Composer install ejecutado
- [ ] Base de datos creada y poblada
- [ ] Configuración del servidor web activa
- [ ] SSL funcionando (HTTPS activo)
- [ ] Verificación de `/api/check-db.php` exitosa

### Post-Despliegue
- [ ] Prueba de login/logout funcionando
- [ ] Todas las ventanas popup abren correctamente
- [ ] Dashboard muestra datos correctamente
- [ ] Histórico carga sin errores
- [ ] Notificaciones toast funcionan
- [ ] Sesiones expiran correctamente (30 min)
- [ ] Logs sin errores críticos
- [ ] Backups programados y funcionando
- [ ] Monitoreo activo

---

## 🔥 Troubleshooting

### Problema: Error 500 al acceder

**Causa:** Permisos incorrectos o .htaccess mal configurado

**Solución:**
```bash
# Verificar permisos
ls -la /var/www/astillero

# Verificar logs
sudo tail -f /var/log/apache2/astillero_error.log

# Verificar .htaccess
cat /var/www/astillero/public/.htaccess
```

### Problema: Sesiones no persisten

**Causa:** `SESSION_COOKIE_SECURE=true` pero sin HTTPS

**Solución:**
- Verificar que HTTPS esté activo
- O cambiar temporalmente a `SESSION_COOKIE_SECURE=false` (no recomendado)

### Problema: Error de conexión a base de datos

**Causa:** Credenciales incorrectas en .env

**Solución:**
```bash
# Verificar .env
cat /var/www/astillero/.env | grep DB_

# Probar conexión manual
mysql -u astillero_user -p -h localhost astillero_prod
```

### Problema: CORS o 403 en API

**Causa:** Headers de seguridad mal configurados

**Solución:**
```bash
# Verificar SecurityHeadersMiddleware.php
cat /var/www/astillero/src/middlewares/SecurityHeadersMiddleware.php

# Verificar en navegador (F12 → Network → Headers)
```

---

## 📞 Soporte

### Contacto
- **Email:** soporte@astillero.com
- **Documentación:** Ver carpeta `/docs`

### Logs Importantes
```bash
/var/log/apache2/astillero_error.log
/var/log/nginx/astillero_error.log
/var/log/php/error.log
/var/www/astillero/src/utils/logs/app.log
```

---

## 🎉 ¡Despliegue Completado!

Si todos los checks están ✅, tu aplicación está lista para producción.

**Recuerda:**
- Monitorear logs regularmente
- Revisar backups semanalmente
- Actualizar certificados SSL antes de vencimiento
- Mantener PHP y MySQL actualizados

---

**Última actualización:** 19 de octubre de 2025  
**Versión del documento:** 1.0
