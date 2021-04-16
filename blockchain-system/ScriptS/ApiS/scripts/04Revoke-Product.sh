PRODUCT_ID=${1:-'15869845464608890373828742231126543269726933639177479734839666354940228947227'}

./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"productId":"'${PRODUCT_ID}'"}' \
  https://localhost:55443/revoke/product
