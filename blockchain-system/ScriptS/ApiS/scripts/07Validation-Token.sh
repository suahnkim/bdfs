TARGET=${1:-'1ee77618b9e4f7651381e2ede71b0d389f27a5c6'}
CID=${2:-'1'}

./forTest-Login-Packager.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"targetId":"'${TARGET}'", "cid":"'${CID}'"}' \
  https://localhost:55443/validation/token
