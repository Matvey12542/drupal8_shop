FROM drupal:8.2.0-rc1-apache
ADD . /var/www/html
WORKDIR /var/www/html

CMD cd ~/

RUN apt-get install curl
ENV DRUSH_VERSION 8.1.2

# Install Drush 8 with the phar file.
RUN curl -fsSL -o /usr/local/bin/drush "https://github.com/drush-ops/drush/releases/download/$DRUSH_VERSION/drush.phar" && \
  chmod +x /usr/local/bin/drush


# Install modules
apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libxml2-dev \
    && docker-php-ext-install mcrypt pdo_mysql mysqli mbstring opcache soap bcmath \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd

# Setup xdebug
RUN curl -L -o /root/xdebug.tgz https://pecl.php.net/get/xdebug-2.3.2.tgz && \
	cd /root && \
	tar -zxvf xdebug.tgz && \
	cd /root/xdebug-2.3.2 && \
	/usr/local/bin/phpize && \
	./configure --enable-xdebug --with-php-config=/usr/local/bin/php-config && \
	make  && \
	make install && \
	cd /root && \
	rm -fr /root/xdebug-2.3.2 && \
	rm -fr /root/xdebug.tgz

# Test your install.
RUN drush core-status
