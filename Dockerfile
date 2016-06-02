FROM php:7.0.7-apache

RUN apt-get update && apt-get -y install wget libjansson4 libhiredis0.10

# Download mod_auth_openidc
RUN wget --quiet --output-document=/tmp/oidc.deb https://github.com/pingidentity/mod_auth_openidc/releases/download/v1.8.8/libapache2-mod-auth-openidc_1.8.8-1_amd64.deb ; dpkg -i /tmp/oidc.deb ; apt-get install -fy && dpkg -i /tmp/oidc.deb

# Enable apache module mod_auth_openidc
RUN a2enmod auth_openidc

COPY ./html /var/www/html

CMD apache2ctl -D FOREGROUND

EXPOSE 80

COPY ./000-default.conf /etc/apache2/sites-enabled/000-default.conf