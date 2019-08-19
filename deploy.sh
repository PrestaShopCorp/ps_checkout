#!/usr/bin/env bash

FILEPATH=$1
ENV=$2
SHOP=$3

gcloud container clusters get-credentials --project="prestashop-ready-$ENV" --zone="europe-west1-d" "prestashop-ready-cluster"

function deploy() {
    echo "** Deploy on : " $1 " ***"
    kubectl cp /workspace/$FILEPATH $ENV-shops/$1:/
    kubectl exec -t --namespace=$ENV-shops $1 -- bash -c \
        "
        sudo -u presthost -H bash -c \"mkdir -p /presthost/core/img/tmp &>/dev/null;\";
        sudo -u presthost -H bash -c \"unzip -o /$FILEPATH -d /presthost/userland/modules > /dev/null 2>&1;\";
        rm -f /${FILEPATH};
        sudo -u presthost --preserve-env -H bash -c '/presthost/core/bin/console  prestashop:module install ps_checkout';
        "
}

deploy $(kubectl get pods --namespace=$ENV-shops -l shop=dep-$SHOP -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')
