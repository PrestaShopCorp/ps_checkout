#!/usr/bin/env bash

FILEPATH=$1
ENV=$2

gcloud container clusters get-credentials --project="prestashop-ready-$ENV" --zone="europe-west1-d" "prestashop-ready-cluster"

if [[ "$ENV" == "prod" ]]; then
    NAME=$(kubectl get pods --namespace=$ENV-shops -l shop=dep-mugshot -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')
elif [[ "$ENV" == "qa" ]]; then
    NAME=$(kubectl get pods --namespace=$ENV-shops -l shop=dep-checkout -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')
else
    echo "BAD ENVIRONMENT"
    exit 1
fi

kubectl cp /workspace/$FILEPATH $ENV-shops/$NAME:/
kubectl exec -t --namespace=$ENV-shops $NAME -- bash -c \
    "
    /presthost/core/bin/console  prestashop:module uninstall ps_checkout || true;	
    sudo -u presthost -H bash -c \"unzip -o /$FILEPATH -d /presthost/userland/modules > /dev/null 2>&1;\";
    rm -f /${FILEPATH};
    /presthost/core/bin/console  prestashop:module install ps_checkout;
    "

if [[ "$ENV" == "prod" ]]; then
    kubectl exec -t --namespace=$ENV-shops $NAME -- bash -c "sudo -u presthost -H bash -c 'rm -rf /presthost/core/modules/ps_checkout/maaslandConf.json';"
fi
