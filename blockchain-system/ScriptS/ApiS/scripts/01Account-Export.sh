ACCOUNTID=${1:-'0xcdc9f4bce1e04fa0cf822b28f5013ded2c2e6cfd'}
PASSWORD=${2:-'p@ssw0rd'}

curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"accountId":"'${ACCOUNTID}'", "password":"'${PASSWORD}'"}' \
  https://localhost:55443/account/export
