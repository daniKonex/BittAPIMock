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

# Copy production environment file
COPY .env.production /var/www/html/.env

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

# Set default port (Railway will override this)
ENV PORT=80

# Expose port
EXPOSE $PORT

# Set CodeIgniter environment variables
ENV CI_ENVIRONMENT=production
ENV WRITABLE_DIR=/var/www/html/writable

# Create startup script for Railway
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Log everything to a file and stdout\n\
exec > >(tee -a /var/log/startup.log)\n\
exec 2>&1\n\
\n\
echo "=== RAILWAY STARTUP LOG ==="\n\
date\n\
echo "PORT: $PORT"\n\
echo "PWD: $(pwd)"\n\
echo "USER: $(whoami)"\n\
\n\
echo "=== Starting Apache on port $PORT ==="\n\
echo "Configuring Apache for port $PORT"\n\
\n\
# Ensure writable directories exist and have correct permissions\n\
mkdir -p /var/www/html/writable/cache /var/www/html/writable/logs /var/www/html/writable/session /var/www/html/writable/uploads\n\
chmod -R 777 /var/www/html/writable\n\
chown -R www-data:www-data /var/www/html/writable\n\
\n\
# Update Apache port configuration\n\
echo "Listen $PORT" > /etc/apache2/ports.conf\n\
\n\
# Update virtual host configuration\n\
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf\n\
\n\
# Set ServerName to avoid warnings\n\
echo "ServerName localhost" >> /etc/apache2/apache2.conf\n\
\n\
# Show configuration for debugging\n\
echo "Apache ports.conf:"\n\
cat /etc/apache2/ports.conf\n\
echo "Virtual host config:"\n\
head -5 /etc/apache2/sites-available/000-default.conf\n\
echo "Writable directory permissions:"\n\
ls -la /var/www/html/writable/\n\
echo "Environment file:"\n\
ls -la /var/www/html/.env\n\
echo "Public directory:"\n\
ls -la /var/www/html/public/\n\
echo "Testing PHP:"\n\
php -v\n\
echo "Testing health.php directly:"\n\
php /var/www/html/public/health.php\n\
\n\
# Enable Apache error logging\n\
echo "ErrorLog /dev/stderr" >> /etc/apache2/apache2.conf\n\
echo "LogLevel debug" >> /etc/apache2/apache2.conf\n\
\n\
# Enable PHP error logging\n\
echo "log_errors = On" >> /usr/local/etc/php/php.ini\n\
echo "error_log = /dev/stderr" >> /usr/local/etc/php/php.ini\n\
echo "display_errors = On" >> /usr/local/etc/php/php.ini\n\
echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini\n\
\n\
# Test PHP files before starting Apache\n\
echo "=== TESTING PHP FILES ==="\n\
echo "Testing health.php:"\n\
php -l /var/www/html/public/health.php\n\
echo "Testing debug.php:"\n\
php -l /var/www/html/public/debug.php\n\
echo "Testing index.php:"\n\
php -l /var/www/html/public/index.php\n\
\n\
# Test file permissions\n\
echo "=== FILE PERMISSIONS ==="\n\
ls -la /var/www/html/\n\
ls -la /var/www/html/public/\n\
ls -la /var/www/html/app/\n\
ls -la /var/www/html/vendor/ | head -10\n\
\n\
# Test composer autoload\n\
echo "=== TESTING COMPOSER ==="\n\
php -r "require_once \"/var/www/html/vendor/autoload.php\"; echo \"Autoload OK\\n\";"\n\
\n\
# Test basic PHP execution\n\
echo "=== TESTING BASIC PHP ==="\n\
php -r "echo \"PHP execution OK\\n\";"\n\
\n\
# Start Apache with verbose logging\n\
echo "Starting Apache with debug logging..."\n\
exec apache2-foreground' > /usr/local/bin/start-apache.sh && \
chmod +x /usr/local/bin/start-apache.sh

# Start Apache using our script
CMD ["/usr/local/bin/start-apache.sh"]
