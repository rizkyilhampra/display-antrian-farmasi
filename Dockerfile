FROM php:5.6-apache

RUN cp /etc/apt/sources.list /etc/apt/sources.list.bak && \
  echo "deb http://archive.debian.org/debian stretch main" > /etc/apt/sources.list && \
  echo "deb http://archive.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list

RUN apt-get update && \
  apt-get install -y --allow-unauthenticated \
  libfaketime \
  libpng-dev \
  libjpeg-dev \
  zip unzip && \
  docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr && \
  docker-php-ext-install gd mysqli pdo pdo_mysql

RUN a2enmod rewrite

COPY .docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

COPY --from=composer:1 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files
COPY . .

RUN composer install

RUN chown -R www-data:www-data /var/www/html