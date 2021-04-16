DATA_ID=${1:-'71831583834652230326771334281788537427956214901663009475147656057632162819773'}
FLAG=${1:-'true'}


./forTest-Login-Owner.sh
set -x
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"dataid":"'${DATA_ID}'", "delete_all_products":'${FLAG}' }' \
  https://localhost:55443/revoke/data
set +x
