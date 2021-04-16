ACCOUNTID=${1:-'e8a524218524edc9af8a921aef70f0fa4fad7fb5'}
PASSWORD=${2:-'p@ssw0rd'}

curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"accountId":"'${ACCOUNTID}'", "password":"'${PASSWORD}'"}' \
  https://localhost:55443/account/export
