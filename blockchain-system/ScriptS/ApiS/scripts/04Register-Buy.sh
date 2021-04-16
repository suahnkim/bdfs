PRODUCT_ID=${1:-'82312741010108891687717692408580812064205995376646157580528765383642379397047'}

./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"productId":"'${PRODUCT_ID}'"}' \
  https://localhost:55443/register/buy
