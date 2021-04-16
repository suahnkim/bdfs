APPROVALS=${1:-'true'}

curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"approvals":"['${APPROVALS}']"}' \
  https://localhost:55443/msp/approve
