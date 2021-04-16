ADDRESS=${1:-'0xcdc9f4bce1e04fa0cf822b28f5013ded2c2e6cfd'}

curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"deleteId":"'${ADDRESS}'"}' \
  https://localhost:55443/account/remove
