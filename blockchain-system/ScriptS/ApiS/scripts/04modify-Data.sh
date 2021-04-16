
DATA_ID=${1:-'15869845464608890373828742231126543269726933639177479734839666354940228947227'}
INFO=${2:-'22222222222222'}

./forTest-Login-Contents-Provider.sh
set -x
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"dataid":"'${DATA_ID}'", "info":"'${INFO}'" }' \
  https://localhost:55443/modify/data
set +x
