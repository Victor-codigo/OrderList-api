FROM php:8.3-fpm

ARG TIMEZONE
ARG WORK_DIR
ARG GIT_NAME
ARG GIT_EMAIL

# Update dependencies
RUN apt-get update

# install WGET
RUN apt-get install -y --no-install-recommends wget

# Install SUDO
RUN apt-get install -y --no-install-recommends sudo

# Install APT-UTILS
RUN apt-get install -y --no-install-recommends apt-utils

# Install DIALOG
RUN apt-get install -y --no-install-recommends dialog

# Install LOCALES
RUN apt-get install -y --no-install-recommends locales
RUN sed -i '/en_US.UTF-8/s/^# //g' /etc/locale.gen && \
    locale-gen
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

# Install ssh client
RUN apt-get install -y --no-install-recommends openssh-client

# Install GIT
RUN apt-get install -y --no-install-recommends git

# Install ZLIB
RUN apt-get install -y --no-install-recommends zlib1g-dev

# Install LIBXML
RUN apt-get install -y --no-install-recommends libxml2-dev

# Install INTL
RUN apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

# Install ZIP
RUN apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install zip

# Install UNZIP
RUN apt-get install -y --no-install-recommends unzip

# Install PDO
RUN docker-php-ext-install pdo pdo_mysql

# install GD
RUN apt-get update && apt-get install -y \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libpng-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd

# Install OPCACHE
RUN docker-php-ext-install pdo pdo_mysql opcache

# Install APCu and APC backward compatibility
RUN pecl install apcu \
    && docker-php-ext-enable apcu

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install nodejs
RUN curl -sL https://deb.nodesource.com/setup_lts.x | bash - \
    && apt-get install -y nodejs

# Install yarn
RUN npm install -g yarn

# Xdebug.ini
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# PHP.ini
COPY ./php.ini /usr/local/etc/php/conf.d/php.ini


# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Timezone
RUN ln -snf /usr/share/zoneinfo/$TIMEZONE /etc/localtime && echo $TIMEZONE > /etc/timezone


# Create user developer
ARG USER_ID
ARG GROUP_ID

RUN addgroup --gid $GROUP_ID developers
RUN adduser --disabled-password --gecos '' --uid $USER_ID --gid $GROUP_ID developer

USER $USER_ID

# Work path
WORKDIR $WORK_DIR


# Share ssh keys with container
RUN mkdir -p ~/.ssh && ln -s /run/secrets/ssh-keys ~/.ssh/id_ed25519
RUN chown -R $USER_ID:$GROUP_ID ~/.ssh


COPY ./.bashrc /home/developer/.bashrc_temp
RUN cat /home/developer/.bashrc_temp >> /home/developer/.bashrc
RUN rm /home/developer/.bashrc_temp


# Git configuration
RUN git config --global user.email $GIT_EMAIL
RUN git config --global user.name $GIT_NAME
