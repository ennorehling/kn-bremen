FROM ubuntu:24.04

LABEL maintainer="Enno Rehling"

ARG WWWGROUP
ARG WWWUSER
ARG MYSQL_CLIENT="mysql-client"

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV LANG=C.UTF-8
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="knb"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy    true;" >> /etc/apt/apt.conf.d/99custom

RUN apt-get update && apt-get upgrade -y \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y gnupg gosu curl ca-certificates git supervisor \
       silversearcher-ag telnet ripgrep vim sudo \
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y \
       php8.5-cli php8.5-dev \
       php8.5-curl \
       php8.5-mysql php8.5-mbstring \
       php8.5-xml php8.5-zip php8.5-bcmath \
       php8.5-intl php8.5-readline \
       php8.5-ast php8.5-pcov \
    && curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
    && apt-get install -y $MYSQL_CLIENT \
    && apt-get install -y postgresql-client-$POSTGRES_VERSION \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN userdel -r ubuntu
RUN groupadd --force -g ${WWWGROUP} knb
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 knb
RUN git config --global --add safe.directory /var/www/html

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.4/cli/conf.d/99-knb.ini
COPY .bash_profile /home/knb/

RUN <<EOF
setcap "cap_net_bind_service=+ep" /usr/bin/php8.5
echo "knb ALL=(ALL:ALL) NOPASSWD:ALL" > /etc/sudoers.d/knb && \
    chmod 0440 /etc/sudoers.d/knb
chmod 666 /etc/php/8.4/cli/conf.d/99-knb.ini
chmod +x /usr/local/bin/start-container
EOF

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]
