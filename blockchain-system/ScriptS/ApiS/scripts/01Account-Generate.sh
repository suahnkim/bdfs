PASSWORD=${1:-'p@ssw0rd'}

curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"password":"'${PASSWORD}'"}' \
  https://localhost:55443/account/generate
