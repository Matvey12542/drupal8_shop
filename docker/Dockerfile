FROM drupal:8.2.0-rc1-apache

ENV DRUSH_VERSION 8.1.2
RUN curl -fsSL -o /usr/local/bin/drush "https://github.com/drush-ops/drush/releases/download/$DRUSH_VERSION/drush.phar" && \
  chmod +x /usr/local/bin/drush

  #Use ->  alias dr="cd /var/www/drupal8/docker && docker-compose exec web drush".
  #than -> dr status for use drush.

# Test your install.
RUN drush core-status

#Install Drupal Console
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer require drupal/console:~1.0 --prefer-dist --optimize-autoloader
RUN composer update drupal/console --with-dependencies
RUN curl https://drupalconsole.com/installer -L -o drupal.phar
RUN mv drupal.phar /usr/local/bin/drupal
RUN chmod +x /usr/local/bin/drupal

WORKDIR /var/www/html
RUN drupal init

#php.ini
COPY php.ini /etc/php7/conf.d/xx-drupal.ini

#xdebug
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini

