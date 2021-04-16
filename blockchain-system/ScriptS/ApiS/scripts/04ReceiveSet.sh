CCID=${1:-"QmXxn8k7CppsFfi58XyR8vh2XDTkunKNz7JzwzVEuKXgSk03"}
VERSION=${2:-'QmPQVfWakEZpCWyuekvwgoz2mWzgSU6ZTQXsirqycJ2xJF03'}
SFLAG=${3:-'0'}
TFLAG=${3:-'0'}

./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"ccid":"'${CCID}'","version":"'${VERSION}'","sflag":"'${SFLAG}'" ,"tflag":"'${TFLAG}'"}' \
  https://localhost:55443/product/setreceive 
