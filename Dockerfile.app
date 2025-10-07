FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    zip \
    curl \
    unzip \
    git \
    supervisor \
    gnupg

# Install Node.js and npm
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip exif pcntl bcmath gd intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# RUN pecl install mongodb && docker-php-ext-enable mongodb

# Set working directory
WORKDIR /var/www/html

# Copy package files first for better caching
COPY composer.json composer.lock ./
COPY package.json package-lock.json* ./

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Install PHP dependencies
RUN composer install --prefer-dist --no-scripts --no-autoloader

# Copy the rest of the application
COPY . .

# Install npm packages and build assets
RUN npm install && npm run build

# Mark the working directory as safe for Git
RUN git config --global --add safe.directory /var/www/html

# Copy custom entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Fix permissions for Laravel directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

USER www-data
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

