R_ID=${1:-'0x522676091d237E4ceA0e0DB97Eaeb8206d976f49'}
R_ATTR=${2:-'96'} 
 
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"id":"'${R_ID}'","attr":"'${R_ATTR}'"}' \
  http://203.229.154.79:55446/addattr 
