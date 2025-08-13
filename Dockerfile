ARG PS_INSTANCE
ARG MODULE_NAME

FROM prestashop/prestashop:$PS_INSTANCE

# GD configuration differs for PHP < 7.4.0 and > 7.4.0
RUN if [ "$PHP_VERSION" = "$(printf "$PHP_VERSION\\n7.4.0" | sort -V | head -n1)" ] ; then \
      docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/; \
    else \
      docker-php-ext-configure gd --with-freetype --with-jpeg; \
    fi && \
    docker-php-ext-install -j$(nproc) gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Conditional Xdebug installation based on INSTALL_XDEBUG
ARG INSTALL_XDEBUG
RUN if [ $INSTALL_XDEBUG = 1 ]; then \
      pecl install xdebug-3.1.6 \ && docker-php-ext-enable xdebug; \
    fi

#for <php8
#RUN pecl channel-update pecl.php.net
#RUN pecl install xdebug-2.9.8 \
#    && docker-php-ext-enable xdebug

EXPOSE 80
