MSG_ID=${1:-'1234567890!@#$%^&*()abcdef'}

./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"inData":"'${MSG_ID}'"}' \
  https://localhost:55443/dsa/sign
