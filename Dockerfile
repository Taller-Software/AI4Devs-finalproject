# Usar imagen oficial de PHP 8.3 con Apache
FROM php:8.3-cli

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /app

# Copiar archivos de la aplicación
COPY . /app

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Exponer puerto
EXPOSE 8080

# Comando para iniciar el servidor
CMD ["php", "-d", "display_errors=1", "-d", "error_reporting=E_ALL", "-S", "0.0.0.0:8080", "-t", ".", "index.php"]
