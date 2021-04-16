PURCHASE_ID=${1:-'8197865663087618567013895231681258352540872151796135733011467076734620397455'}

./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"purchaseId":"'${PURCHASE_ID}'"}' \
  https://localhost:55443/info/token
