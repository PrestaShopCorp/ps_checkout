#!/usr/bin/env bash

DOCKER_READY=0;
I=0;
docker network create nw-ckt || true;

while [ ${DOCKER_READY} -ne 1 ]; do

    docker-compose -p presta_ckt logs | grep "Almost ! Starting web server now" &> /dev/null;

    if [ $? == 0 ]; then
        echo " *** Containers are ready *** ";
        DOCKER_READY=1;
    else
        echo " *** Waiting containers are ready *** ";
        sleep 5
    fi

    I=$((I + 1));

    if [ $I = 30 ];then
        echo " *** /!\ Error /!\ *** ";
        exit 1;
    fi

done
