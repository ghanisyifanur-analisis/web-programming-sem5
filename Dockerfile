FROM php:8.2-apache

# Install ekstensi MySQL/PDO
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy seluruh source code
COPY . /var/www/html

# Atur permission
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
