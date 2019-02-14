FROM notification_push_php

#安装swoole
COPY swoole-src-4.2.13.tar.gz /
RUN cd / && tar -zxvf swoole-src-4.2.13.tar.gz \
	&& ( \
       cd swoole-src-4.2.13 \
       && phpize \
       && ./configure \
          --enable-openssl \
          --enable-mysqlnd \
       && make  && make install \
       ) \
    && docker-php-ext-enable swoole \
	&& cd / && rm -rf swoole-src-4.2.13.tar.gz swoole-src-4.2.13

WORKDIR /

EXPOSE 9555

CMD ["php-fpm"]