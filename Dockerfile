FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        default-mysql-client \
        gzip \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql gd \
    && a2enmod rewrite \
    && echo 'FallbackResource /index.php' >> /etc/apache2/conf-available/docker-php.conf

COPY docker/apache-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/public/assets/uploads

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

EXPOSE 80
