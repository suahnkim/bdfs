TARGET=${1:-'9bcecd9085fae8fa787ac3f3bd3c2f25a90e0610'}
ROLE=${2:-'CP'}

curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"target":"'${TARGET}'", "role":"'${ROLE}'"}' \
  https://localhost:55443/msp/verify
