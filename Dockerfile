FROM drupal:8.2.0-rc1-apache
ADD . /var/www/html
WORKDIR /var/www/html

CMD cd ~/

RUN apt-get install curl
ENV DRUSH_VERSION 8.1.2

# Install Drush 8 with the phar file.
RUN curl -fsSL -o /usr/local/bin/drush "https://github.com/drush-ops/drush/releases/download/$DRUSH_VERSION/drush.phar" && \
  chmod +x /usr/local/bin/drush

# Test your install.
RUN drush core-status
																																							