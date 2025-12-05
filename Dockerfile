# Dockerfile for MyBBA PHP Application
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY public/ /var/www/html/
COPY config/ /var/www/config/
COPY database/ /var/www/database/
COPY vendor/ /var/www/vendor/

# Create uploads directory with proper permissions
RUN mkdir -p /var/www/html/uploads/bukti_pembayaran \
    && mkdir -p /var/www/html/uploads/informasi \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 755 /var/www/html/uploads

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache
RUN echo '<Directory /var/www/html/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/mybba.conf \
    && a2enconf mybba

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
