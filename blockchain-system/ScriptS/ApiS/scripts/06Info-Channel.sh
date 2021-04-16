CHANNEL_ID=${1:-'975373305388079500009438164787053178000015248523997445423000196815085410911900'}

./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"channelId":"'${CHANNEL_ID}'"}' \
  https://localhost:55443/info/channel
