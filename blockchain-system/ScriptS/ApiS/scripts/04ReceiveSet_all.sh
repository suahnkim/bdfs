CCID=${1:-"QmXxn8k7CppsFfi58XyR8vh2XDTkunKNz7JzwzVEuKXgSk01"}
VERSION=${2:-'QmPQVfWakEZpCWyuekvwgoz2mWzgSU6ZTQXsirqycJ2xJF01'}
FLAG=${3:-'1'}
 
curl --header "Content-Type: application/json" \
  --request POST \
  --data '{"ccid":"'${CCID}'","version":"'${VERSION}'","tflag":"'${FLAG}'","sflag":"'${FLAG}'"  }' \
  http://localhost:55441/product/setSearchNode
