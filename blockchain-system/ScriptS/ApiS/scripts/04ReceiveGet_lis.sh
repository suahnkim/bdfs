CCID=${1:-"QmXxn8k7CppsFfi58XyR8vh2XDTkunKNz7JzwzVEuKXgSk04"}
VERSION=${2:-'QmPQVfWakEZpCWyuekvwgoz2mWzgSU6ZTQXsirqycJ2xJF04'}
 
./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"ccid":"'${CCID}'","version":"'${VERSION}'" }' \
  http://localhost:55441/product/isreceive
