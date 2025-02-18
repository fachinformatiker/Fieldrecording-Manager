FROM php:8.2-apache

# Install SQLite and required PHP extensions
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Set working directory
WORKDIR /var/www/html

# Copy project files into the container
COPY data/ .

# Expose port 80
EXPOSE 80
