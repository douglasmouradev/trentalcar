FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql gd \
    && a2enmod rewrite \
    && sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && echo 'FallbackResource /index.php' >> /etc/apache2/conf-available/docker-php.conf

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/public/assets/uploads

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

EXPOSE 80
