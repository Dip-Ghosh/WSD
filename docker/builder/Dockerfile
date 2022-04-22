FROM php:7.4.26-fpm-buster

# Creating the builder user
USER root

# prerequisites
RUN apt-get update && \
    apt-get install \
    git \
    pcregrep \
    openssl libfontconfig \
    zip unzip \
    -y

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions

RUN install-php-extensions \
        mongodb \
        xdebug \
        ""

# Add the container user
ARG USER_NAME=builder
ARG USER_HOME=/home/$USER_NAME
ARG UID=1000
ARG GID=1000
RUN groupadd -g $GID -o $USER_NAME
RUN useradd -m -u $UID -g $GID -o -s /bin/bash $USER_NAME

# Fix a failing build_dev target on local
RUN mkdir -p /var/log/php
RUN chmod -R ugo+rw /var/log/php

COPY ./prepend.php /usr/local/etc/prepend.php


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.1.12

# Installing Backend build tools
USER $USER_NAME
