# Use the official PHP Apache image
FROM php:apache

# Install dependencies for pdo_mysql extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite (optional but often needed)
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80
