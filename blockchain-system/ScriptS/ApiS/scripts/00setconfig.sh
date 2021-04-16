VURL=${1:-'http://203.229.154.79'}
VPORT=${2:-'55444'}
PERIOD=${3:-'10'}
COLLECTION=${4:-'100'}

./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"verifierUrl":"'${VURL}'","verifierPort":"'${VPORT}'","channelOpenPeriod":"'${PERIOD}'","receiptCollection":"'${COLLECTION}'" }' \
  https://localhost:55443/config/setconfig
