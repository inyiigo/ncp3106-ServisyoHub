FROM php:8.2-apache

# Install PHP extensions required by this app.
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules and allow .htaccess overrides.
RUN a2enmod rewrite headers \
    && sed -ri 's#AllowOverride None#AllowOverride All#g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copy application files.
COPY . /var/www/html/

# Ensure writable upload directories exist.
RUN mkdir -p /var/www/html/assets/uploads/avatars \
    /var/www/html/assets/uploads/services \
    /var/www/html/uploads/storage \
    && chown -R www-data:www-data /var/www/html/assets/uploads /var/www/html/uploads

# Render provides PORT at runtime; reconfigure Apache to listen on it.
CMD PORT_TO_USE=${PORT:-10000}; \
    sed -ri "s/^Listen [0-9]+/Listen ${PORT_TO_USE}/" /etc/apache2/ports.conf; \
    sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT_TO_USE}>/" /etc/apache2/sites-available/000-default.conf; \
    apache2-foreground
