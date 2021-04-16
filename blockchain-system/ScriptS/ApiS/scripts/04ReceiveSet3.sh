CCID=${1:-"QmXxn8k7CppsFfi58XyR8vh2XDTkunKNz7JzwzVEuKXgSk03"}
VERSION=${2:-'QmPQVfWakEZpCWyuekvwgoz2mWzgSU6ZTQXsirqycJ2xJF03'}
FLAG=${3:-'1'}

./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"ccid":"'${CCID}'","version":"'${VERSION}'","tflag":"'${FLAG}'" }' \
  https://localhost:55443/product/setStorageNode 
