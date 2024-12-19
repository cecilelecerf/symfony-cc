FROM php:8.2-fpm

# Installer les dépendances requises
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/symfony

# Copier les fichiers du projet
COPY . .

# Installer les dépendances Composer
RUN composer install --no-dev --optimize-autoloader || true

# Exposer le port 8000
EXPOSE 8000

# Démarrer le serveur Symfony
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
