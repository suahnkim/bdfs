CCID=${1:-'QmXxn8k7CppsFfi58XyR8vh2XDTkunKNz7JzwzVEuKXg00'}
VERSION=${2:-'QmXxn8k7CppsFfi58XyR8vh2XDTkunKNz7JzwzVEuKXg00'}
PRICE=${3:-'5000'}

./forTest-Login-Distributor.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"ccid":"'${CCID}'", "version":"'${VERSION}'", "price":"'${PRICE}'"}' \
  https://localhost:55443/register/product
