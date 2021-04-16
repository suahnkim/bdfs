DATA_ID=${1:-'99558218386913658901519683673077561320853223811206486392783235958515381511761'}

./forTest-Login-Contents-Provider.sh
curl -X POST --insecure  https://localhost:55443/info/data -d dataId=${DATA_ID}

curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"dataId":"'${DATA_ID}'"}' \
  https://localhost:55443/info/data
