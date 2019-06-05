FROM prestashop/prestashop:1.7.5.1

LABEL maintainer="David PIATEK <david.piatek@prestashop.com>"

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true

RUN set -xe \
    && runtimeDeps=" \
      gnupg \
      libgconf-2-4 \
      wget --no-install-recommends \
      unzip \
      git \
      nano \
      vim \
    " \
    && apt-get update \
	  && apt-get install -yq $runtimeDeps \
    && wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add - \
    && sh -c 'echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list' \
    && apt-get update \
    && apt-get install -y google-chrome-unstable --no-install-recommends \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /src/*.deb

RUN curl -sL https://deb.nodesource.com/setup_11.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm yarn

RUN curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer self-update \
    && composer global require "hirak/prestissimo:^0.3" \
    && rm -rf composer-setup.php
    
RUN apt-get update && \
  apt-get install -yq gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 \
  libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 \
  libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 \
  libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 \
  fonts-ipafont-gothic fonts-wqy-zenhei fonts-thai-tlwg fonts-kacst ttf-freefont \
  ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget && \
  wget https://github.com/Yelp/dumb-init/releases/download/v1.2.1/dumb-init_1.2.1_amd64.deb && \
  dpkg -i dumb-init_*.deb && rm -f dumb-init_*.deb && \
  apt-get clean && apt-get autoremove -y && rm -rf /var/lib/apt/lists/*

RUN touch /upgrade.tm

EXPOSE 9223
