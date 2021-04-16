FILE_ID=${1:-'10649098441193233919498884699726158189517127532267943159478977733809997021835'}

./forTest-Login-Contents-Provider.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"fileId":"'${FILE_ID}'"}' \
  https://localhost:55443/info/file
