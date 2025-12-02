# DOCKERFILE - WEBSERVER1

# Imagen base: PHP 8.2 con Apache
FROM php:8.2-apache   

# -------------------------
# Dependencias necesarias
# -------------------------
RUN apt-get update && apt-get install -y \
    gnupg2 \                 # Herramientas de cifrado (necesarias para repositorios firmados)
    unixodbc \               # Driver ODBC genérico
    unixodbc-dev \           # Librerías de desarrollo ODBC
    curl \                   # Descargas y scripts
    apt-transport-https \    # Soporte para repos HTTPS
    lsb-release \            # Detectar versión de distribución
    && rm -rf /var/lib/apt/lists/*

# -------------------------
# Repositorio Microsoft para ODBC
# -------------------------
RUN curl https://packages.microsoft.com/keys/microsoft.asc \
      | tee /usr/share/keyrings/microsoft.asc \
    && echo "deb [signed-by=/usr/share/keyrings/microsoft.asc] \
       https://packages.microsoft.com/debian/$(lsb_release -rs)/prod \
       $(lsb_release -cs) main" \
      | tee /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools18 \
    && rm -rf /var/lib/apt/lists/*

# -------------------------
# Extensiones PHP necesarias
# -------------------------
RUN docker-php-ext-install pdo \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv
