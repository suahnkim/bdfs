CCID=${1:-"QmZguJnNSCqcnHGN4KYtXDoJVDYxkZHDJUzCxpS972XT4q1"}
VERSION=${2:-'QmbCfF6Z2tkAFndxXwjrkfVGaqntRfoyCQcGEGq97fWUk3'}
 
./forTest-Login-Owner.sh
curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"ccid":"'${CCID}'","version":"'${VERSION}'" }' \
  https://localhost:55443/product/isreceive
