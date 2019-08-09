#!/usr/bin/env bash

FILEPATH=$1
ENV=$2

gcloud container clusters get-credentials --project="prestashop-ready-$ENV" --zone="europe-west1-d" "prestashop-ready-cluster"

function deploy() {
    echo "** Deploy on : " $1 " ***"
    kubectl cp /workspace/$FILEPATH $ENV-shops/$1:/
    kubectl exec -t --namespace=$ENV-shops $1 -- bash -c \
        "
        sudo -u presthost --preserve-env -H bash -c '/presthost/core/bin/console prestashop:module uninstall ps_checkout || true';
        sudo -u presthost -H bash -c \"unzip -o /$FILEPATH -d /presthost/userland/modules > /dev/null 2>&1;\";
        rm -f /${FILEPATH};
        sudo -u presthost --preserve-env -H bash -c '/presthost/core/bin/console  prestashop:module install ps_checkout';
        "

    if [[ "$ENV" == "prod" ]]; then
        kubectl exec -t --namespace=$ENV-shops $1 -- bash -c "sudo -u presthost -H bash -c 'rm -rf /presthost/core/modules/ps_checkout/maaslandConf.json';"
    fi
}

if [[ "$ENV" == "prod" ]]; then
#    deploy $(kubectl get pods --namespace=$ENV-shops -l shop=dep-mugshot -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')
#    deploy $(kubectl get pods --namespace=$ENV-shops -l shop=dep-kjffkjjhjgklgjh -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')

    # customer shops
#    deploy $(kubectl get pods --namespace=$ENV-shops -l shop=dep-art-et-signaletique -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')
#    deploy $(kubectl get pods --namespace=$ENV-shops -l shop=dep-la-vie-tout-en-bio -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')
#    deploy $(kubectl get pods --namespace=$ENV-shops -l shop=dep-britneystore -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')
    deploy $(kubectl get pods --namespace=$ENV-shops -l shop=dep-ah5-sailing -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')
    deploy $(kubectl get pods --namespace=$ENV-shops -l shop=dep-sweetaxo -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')
    deploy $(kubectl get pods --namespace=$ENV-shops -l shop=dep-leeroycustomshop -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')
elif [[ "$ENV" == "qa" ]]; then
    deploy $(kubectl get pods --namespace=$ENV-shops -l shop=dep-checkout -o go-template --template '{{range .items}}{{.metadata.name}}{{"\n"}}{{end}}')
else
    echo "BAD ENVIRONMENT"
    exit 1
fi


