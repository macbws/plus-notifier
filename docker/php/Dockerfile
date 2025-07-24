FROM php:8.1-fpm

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    @composer \
    intl \
    opcache \
    xdebug
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && apt-get install -y symfony-cli
COPY app.ini /usr/local/etc/php/conf.d/app.ini
RUN git config --global user.email "19225941-macbws@users.noreply.gitlab.com"
RUN git config --global user.name "macbws"
RUN git config --global --add safe.directory /app # prevent error "detected dubious ownership in repository at '/app'" for use git in the container terminal
RUN ln -sf /bin/bash /bin/sh

WORKDIR /app

EXPOSE 9000
