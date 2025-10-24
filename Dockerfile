FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure GD with jpeg and freetype support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Copy Apache configuration
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Create writable directory
RUN mkdir -p /var/www/html/writable && \
    chmod -R 775 /var/www/html/writable && \
    chown -R www-data:www-data /var/www/html/writable

# Change ownership
RUN chown -R www-data:www-data /var/www/html

# Set environment variables for Railway
ENV PORT=80

# Expose port
EXPOSE 80

# Create script to start Apache with dynamic port
RUN echo '#!/bin/bash\n\
echo "Starting Apache..."\n\
echo "Port: $PORT"\n\
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf\n\
sed -i "s/:80>/:$PORT>/" /etc/apache2/sites-available/000-default.conf\n\
apache2-foreground' > /usr/local/bin/start-apache.sh && \
chmod +x /usr/local/bin/start-apache.sh

# Start Apache using our script
CMD ["/usr/local/bin/start-apache.sh"]
