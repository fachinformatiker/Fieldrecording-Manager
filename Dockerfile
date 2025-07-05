# Use official PHP with Apache
FROM php:8.2-apache

# Install required extensions and tools
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY data/ .

# Create necessary directories and set permissions
RUN mkdir -p uploads data logs \
    && chown -R www-data:www-data . \
    && chmod -R 755 . \
    && chmod -R 777 uploads data logs

# Configure Apache
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
