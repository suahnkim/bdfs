CCID=${1:-"QmXxn8k7CppsFfi58XyR8vh2XDTkunKNz7JzwzVEuKXgSk04"}
VERSION=${2:-'QmPQVfWakEZpCWyuekvwgoz2mWzgSU6ZTQXsirqycJ2xJF04'}
FLAG=${3:-'1'}
 
curl --header "Content-Type: application/json" \
  --request POST \
  --data '{"ccid":"'${CCID}'","version":"'${VERSION}'","sflag":"'${FLAG}'" }' \
  http://localhost:55441/product/setSearchNode
