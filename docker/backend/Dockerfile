FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    curl \
    && docker-php-ext-configure pgsql --with-pgsql=/usr/local \
    && docker-php-ext-install pdo_pgsql pgsql \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html
COPY docker/backend/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/backend/php.ini /usr/local/etc/php/conf.d/app.ini

EXPOSE 80

CMD ["apache2-foreground"]
