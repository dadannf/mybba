# Dockerfile untuk MyBBA School Management System
FROM php:8.2-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /var/www/mybba

# Copy application files
COPY . /var/www/mybba

# Set permissions
RUN chown -R www-data:www-data /var/www/mybba \
    && chmod -R 755 /var/www/mybba \
    && chmod -R 775 /var/www/mybba/public/uploads

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisord.conf

# Expose port
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
