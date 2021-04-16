PURCHASE_ID=${1:-'0'}
PUBLIC_KEY=${2:-'arbitrary_type_of_public_key'}
DOWN_CHUNK_LIST=${3:-'D:/MyWork/onchain/chunk.dat'}

curl --header "Content-Type: application/json" \
  --request POST \
    --insecure \
  --data '{"purchaseId":"'${PURCHASE_ID}'", "publicKey":"'${PUBLIC_KEY}'", "downChunkList":"'${DOWN_CHUNK_LIST}'"}' \
  https://localhost:55443/register/channelOpen
