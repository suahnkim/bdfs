PRIVATE_KEY=${1:-'0x4e97e81d425966c00422a8fc5602382da74862a239e941b2777ab0cb968d115b'}
PRIVATE_KEY2=${2:-'0x201db58472a0409c565818bce84813c7125090f05be53a51ce2affd5bdbdcd6c'}
PRIVATE_KEY3=${3:-'0xb7848396e86a4b5c74d52c4800863727f0b5fc4aa46f6de4f27b8c023f7b44b5'}
PRIVATE_KEY4=${4:-'0xf77988ba7f3c350584e9057c9b59c47cd942d72d85ef037f8874f7ea60374efe'}
PRIVATE_KEY5=${5:-'0xcff0e0e2c19066af865adfb39dd6fb8126149160864408b53067c7992354186e'}
PRIVATE_KEY6=${6:-'0xd482b8cfd8b13bfaace7ed1f5359b0b09ec979edd7381043bf119abfdced1148'}

PASSWORD=${7:-'p@ssw0rd'}

curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"privateKey":"'${PRIVATE_KEY}'", "password":"'${PASSWORD}'"}' \
  http://localhost:55442/account/import

  curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"privateKey":"'${PRIVATE_KEY2}'", "password":"'${PASSWORD}'"}' \
  http://localhost:55442/account/import

  curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"privateKey":"'${PRIVATE_KEY3}'", "password":"'${PASSWORD}'"}' \
  http://localhost:55442/account/import


    curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"privateKey":"'${PRIVATE_KEY4}'", "password":"'${PASSWORD}'"}' \
  http://localhost:55442/account/import

    curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"privateKey":"'${PRIVATE_KEY5}'", "password":"'${PASSWORD}'"}' \
  http://localhost:55442/account/import

      curl --header "Content-Type: application/json" \
  --request POST \
  --insecure \
  --data '{"privateKey":"'${PRIVATE_KEY6}'", "password":"'${PASSWORD}'"}' \
  http://localhost:55442/account/import