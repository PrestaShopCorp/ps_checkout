#!/usr/bin/env bash

FILEPATH=$1

gcloud container clusters get-credentials --project="prestashop-ready-qa" --zone="europe-west1-d" "prestashop-ready-cluster"

NAME=$(kubectl get pods --namespace=qa-shops -l shop=dep-checkout -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')

kubectl cp /workspace/$FILEPATH qa-shops/$NAME:/
kubectl exec -t --namespace=qa-shops $NAME -- bash -c \
    '
    sudo -u presthost -H bash -c "unzip -o /$FILEPATH -d /presthost/userland/modules > /dev/null 2>&1;";
    rm -f /'"${FILEPATH}"';
    '
