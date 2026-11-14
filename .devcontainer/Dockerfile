FROM mcr.microsoft.com/devcontainers/php:4-8.4-trixie

# Install MariaDB client
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y mariadb-client nodejs npm \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*

RUN pecl install ast
#RUN docker-php-ext-install ast

# Install php-mysql driver
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN ln -sf /usr/local/bin/php /usr/bin/
RUN echo "extension=ast" >> /usr/local/etc/php/conf.d/ast.ini
RUN echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/99-php-override.ini

# overrwrite xdebug settings
RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=develop" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini

# [Optional] Uncomment this section to install additional OS packages.
# RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
#     && apt-get -y install --no-install-recommends <your-package-list-here>

# [Optional] Uncomment this line to install global node packages.
# RUN su vscode -c "source /usr/local/share/nvm/nvm.sh && npm install -g <your-package-here>" 2>&1
