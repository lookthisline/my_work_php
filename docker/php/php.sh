# !/bin/bash

# 安装 PhpRedis
mkdir -p /usr/src/php/ext/redis && \
curl -L -o /tmp/redis.tar.gz https://github.com/phpredis/phpredis/archive/5.3.2.tar.gz && \
tar -zxf /tmp/redis.tar.gz && \
mv phpredis-5.3.2/* /usr/src/php/ext/redis && \
rm -r /tmp/*

cd /usr/local/bin

cat >> /etc/apt/sources.list <<EOF
deb http://mirrors.163.com/debian/ stretch main non-free contrib
deb http://mirrors.163.com/debian/ stretch-updates main non-free contrib
deb http://mirrors.163.com/debian/ stretch-backports main non-free contrib
deb-src http://mirrors.163.com/debian/ stretch main non-free contrib
deb-src http://mirrors.163.com/debian/ stretch-updates main non-free contrib
deb-src http://mirrors.163.com/debian/ stretch-backports main non-free contrib
deb http://mirrors.163.com/debian-security/ stretch/updates main non-free contrib
deb-src http://mirrors.163.com/debian-security/ stretch/updates main non-free contrib
EOF

# 安装各类依赖
apt-get update && \
apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev libzstd-dev curl && \
docker-php-ext-configure gd \
    --with-jpeg=/usr/include/ \
    --with-webp=/usr/include/webp/ \
    --with-freetype=/usr/include/freetype/ && \
docker-php-ext-install -j$(nproc) gd pdo_mysql bcmath gettext sockets opcache redis && \
pecl install igbinary lzf zstd && \
docker-php-ext-enable igbinary lzf zstd

touch /usr/local/etc/php/conf.d/uploads.ini

cat > /usr/local/etc/php/conf.d/uploads.ini <<EOF
file_uploads=On
memory_limit=60M
upload_max_filesize=60M
post_max_size=60M
max_execution_time=600
EOF