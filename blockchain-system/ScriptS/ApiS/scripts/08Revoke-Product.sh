PRODUCT_ID=${1:-'30987429425609118453198015398717099135470036504607909407190763572917841127740'}

./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"productId":"'${PRODUCT_ID}'"}' \
  https://localhost:55443/revoke/product
