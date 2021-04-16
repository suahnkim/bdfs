TARGET=${1:-'1ee77618b9e4f7651381e2ede71b0d389f27a5c6'}

curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data "{\"target\":\"${TARGET}\"}" \
  https://localhost:55443/msp/appointManager
