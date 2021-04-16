ROLE=${1:-"P"}

curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"role":"'${ROLE}'"}' \
  https://localhost:55443/msp/authRequest
