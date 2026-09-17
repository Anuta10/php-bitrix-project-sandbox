FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

COPY . /var/www/html

RUN mkdir -p /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/storage

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
