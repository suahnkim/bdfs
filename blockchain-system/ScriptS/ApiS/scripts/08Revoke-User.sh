TARGET=${1:-'c7cf04aa9a7a6d548e6d1dac8f7401f4a36ad32b'}
ROLE=${2:-'D'}
DD=${3:-'false'}
DP=${4:-'false'}
./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"targetId":"'${TARGET}'", "role":"'${ROLE}'","delete_all_datas":'${DD}',"delete_all_products":'${DP}'}' \
  https://localhost:55443/revoke/user
