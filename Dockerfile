FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libxslt-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    gd \
    zip \
    intl \
    mbstring \
    xml \
    xsl

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Create logs directory and set proper permissions
RUN mkdir -p /app/logs && \
    chown -R www-data:www-data /app && \
    chmod -R 755 /app/logs
