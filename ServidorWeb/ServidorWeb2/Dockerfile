# /home/admin/Dockerfile

# 1. Usar una imagen más nueva (PHP 8.1)
FROM php:8.1-fpm

# 2. Instalar dependencias esenciales y driver ODBC 
#    - Ahora usa msodbcsql18 (la versión recomendada para PHP 8+)
#    - La versión de Debian se mantiene en 10, pero se usa una imagen PHP 8.1.
RUN apt-get update && apt-get install -y \
    build-essential \
    libtool \
    autoconf \
    unixodbc-dev \
    libsqlite3-dev \
    curl \
    gnupg2 \
    apt-transport-https \
    # Limpieza
    && rm -rf /var/lib/apt/lists/*

# 3. Configurar el repositorio de Microsoft SQL Server (Debian 10 Buster)
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /etc/apt/trusted.gpg.d/microsoft.gpg
RUN curl -fsSL https://packages.microsoft.com/config/debian/10/prod.list > /etc/apt/sources.list.d/mssql-release.list

# 4. Instalar el driver ODBC (msodbcsql18) y las herramientas
RUN apt-get update
RUN ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools

# 5. Instalar las extensiones PHP sqlsrv y pdo_sqlsrv
#    - Usaremos la última versión (5.11.0 o la más reciente compatible con PHP 8.1)
#    - Ya no necesitamos arreglos de rutas ni de libtool.
RUN pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# 6. Establecer el directorio de trabajo
WORKDIR /var/www/html
