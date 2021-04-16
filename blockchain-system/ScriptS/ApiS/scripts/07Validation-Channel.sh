CHANNEL_ID=${1:-'47830328431931200464256688513003710522156896697660429087147726166605728736708'}

./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"channelId":"'${CHANNEL_ID}'"}' \
  https://localhost:55443/validation/channel
