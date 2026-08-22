FROM php:8.3-fpm

# Definir argumentos para el UID y GID del usuario del host (para evitar problemas de permisos de archivos)
ARG USER_ID=1000
ARG GROUP_ID=1000

# Instalar dependencias del sistema y herramientas de base de datos
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libzip-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    default-mysql-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurar e instalar extensiones PHP necesarias
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install \
    pdo_mysql \
    gd \
    zip \
    soap \
    bcmath \
    opcache

# Instalar Composer de forma segura
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Crear un usuario y grupo del sistema con el mismo UID/GID que el usuario del host
RUN groupadd -g ${GROUP_ID} laravel && \
    useradd -u ${USER_ID} -g laravel -m -s /bin/bash laravel

# Modificar el usuario de ejecución de PHP-FPM para que sea el usuario 'laravel'
RUN sed -i "s/user = www-data/user = laravel/g" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i "s/group = www-data/group = laravel/g" /usr/local/etc/php-fpm.d/www.conf

# Asignar permisos al directorio de trabajo
RUN chown -R laravel:laravel /var/www/html

# Cambiar al usuario laravel
USER laravel

# Exponer el puerto 9000 para PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
