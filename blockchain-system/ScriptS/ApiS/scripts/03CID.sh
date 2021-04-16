TARGET=${1:-"9bcecd9085fae8fa787ac3f3bd3c2f25a90e0610"}

./forTest-Login-Packager.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"targetID":"'${TARGET}'"}' \
  https://localhost:55443/cid
