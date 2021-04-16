UNIT=${1:-'ether'}
AMOUNT=${2:-'0.05'}
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"unit":"'${UNIT}'", "amount":"'${AMOUNT}'"}' \
https://localhost:55443/send/ethereum
