FROM mysql:8-debian

RUN set -xe \
    && rm /etc/apt/sources.list.d/mysql.list \
    #&& apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 467B942D3A79BD29 \
    && apt-get update \
    #&& apt-get install curl -y \
    #&& curl https://repo.mysql.com/RPM-GPG-KEY-mysql-2023 --output /etc/apt/trusted.gpg.d/mysql2023 \
    #&& gpg --dearmor /etc/apt/trusted.gpg.d/mysql2023 \
    #&& rm /etc/apt/trusted.gpg.d/mysql2023 \
    && apt-get upgrade -y \
    && apt-get clean \
    && rm -rf /tmp/*
