FROM php:8.2-apache
RUN a2enmod rewrite
RUN apt-get update && apt-get install -y \
      git unzip libicu-dev libzip-dev libpng-dev libonig-dev \
  && docker-php-ext-install mysqli pdo pdo_mysql intl zip gd \
  && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
