ADDRESS=${1:-'0x1ee77618b9e4f7651381e2ede71b0d389f27a5c6'}
PASSWORD=${2:-'p@ssw0rd'}
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"accountId":"'${ADDRESS}'", "password":"'${PASSWORD}'"}' \
  https://localhost:55443/account/login
