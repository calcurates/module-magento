FROM mysql:5.7

RUN set -xe \
    && apt-get update \
    && apt-get upgrade -y \
    && apt-get clean \
    && rm -rf /tmp/*

COPY server.cnf /etc/mysql/conf.d/server.cnf
