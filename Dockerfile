FROM php:8.0.3-fpm
#
WORKDIR '/var/www'

RUN apt-get update;
RUN apt-get -y install telnet;
RUN apt-get -y install vim;
#
#COPY . .

