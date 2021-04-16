SIGN_VALUE=${1:-'+NbVEW2l8qWHfOoD/H73+wCGKoJIVoLDvPVuW9TtAgGhqHm3QqpJaxytSnwiBpKnP0tNpy6JC8AZT8Ideo0wDSIxMjM0NTY3ODkwIUAjJCVeJiooKWFiY2RlZiI='}
PUB_VALUE=${2:-'HxmvtuLjFdRXlXtxiSObgdz0Gj321ULXeuKkSOY/6C4='}
SIGN_DATA=${3:-'{\"sign\":\"+NbVEW2l8qWHfOoD/H73+wCGKoJIVoLDvPVuW9TtAgGhqHm3QqpJaxytSnwiBpKnP0tNpy6JC8AZT8Ideo0wDSIxMjM0NTY3ODkwIUAjJCVeJiooKWFiY2RlZiI=\",\"pubKey\":\"HxmvtuLjFdRXlXtxiSObgdz0Gj321ULXeuKkSOY/6C4=\"}'}
./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"signature":"'${SIGN_VALUE}'", "publicKey":"'${PUB_VALUE}'"}' \
  https://localhost:55443/dsa/verify

  curl --header "Content-Type: application/json" \
    --request POST \
    --insecure \
    --data '{"signature":"'${SIGN_DATA}'"}' \
    https://localhost:55443/dsa/verify2
