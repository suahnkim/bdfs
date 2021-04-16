#!/bin/bash
SCRIPT=`realpath -s $0`
SCRIPTPATH=`dirname ${SCRIPT}`
SCRIPTNAME=`basename ${SCRIPT}`
cd ${SCRIPTPATH}

# Ports used
webclient_port=8080
hotwalletserver_port=3000
ganache_port=8545
geth_port_1=8545
geth_port_2=8546
loom_port_1=46657
loom_port_2=46658

function check_file_exists {
  if [ -f $1 ]; then
    echo 1
  else
    echo 0
  fi
}

function check_directory_exists {
  if [ -d $1 ]; then
    echo 1
  else
    echo 0
  fi
}

function check_port {
  if (nc -z localhost $1); then
    echo 1
  else
    echo 0
  fi
}

function is_setup_already {
  if [ $(check_directory_exists ./WebCLnt/node_modules) = 1 ] &&
     [ $(check_directory_exists ./HotWaLLetSrv/node_modules) = 1 ] &&
     [ $(check_directory_exists ./TruffLeBToken/node_modules) = 1 ] &&
     [ $(check_directory_exists ./TruffLeGateWay/node_modules) = 1 ] &&
     [ $(check_file_exists ./LoomNetwork/loom) = 1 ] &&
     [ $(check_directory_exists ./TstBToken/node_modules) = 1 ] &&
     [ $(check_directory_exists ./ScriptS/SampLeS/node_modules) = 1 ] &&
     [ $(check_directory_exists ./ScriptS/Listener/node_modules) = 1 ] &&
     [ $(check_directory_exists ./ScriptS/ApiS/node_modules) = 1 ]; then
    echo 1
  else
    echo 0
  fi
}

# Setup function does the first work of download node_packages and loom binary
function setup {
  cd ./WebCLnt
  echo "install web client"
  yarn
  cd ../HotWaLLetSrv
  echo "install hot wallet server"
  yarn
  cd ../TruffLeBToken
  echo "install truffle btoken"
  yarn
  cd ../TruffLeGateWay
  echo "install truffle gateway"
  yarn
  if [ $(check_directory_exists ../LoomNetwork) = 0 ]; then
    mkdir ../LoomNetwork
  fi
  cd ../LoomNetwork
  echo "install loom network"
  curl https://raw.githubusercontent.com/loomnetwork/loom-sdk-documentation/master/scripts/get_loom.sh | sh
  ./loom init -f
  cd ../TstBToken
  echo "install testcase"
  yarn
  cd ../
  cd ./ScriptS/SampLeS
  echo "install sample scripts"
  yarn
  cd ../../
  cd ./ScriptS/Listener
  echo "install listener scripts"
  yarn
  cd ../../
  cd ./ScriptS/ApiS
  echo "install apis scripts"
  yarn
  cd ../../
}

function start_webclient {
    echo "running web client"
    cd ./WebCLnt
    yarn serve
    cd ..
}

function start_hotwalletserver {
    echo "running hot wallet server"
    cd ./HotWaLLetSrv
    yarn serve
    cd ..
}

function deploy_truffle_btoken_loom {
  echo "deploy truffle btoken to loom_dapp_chain"
  cd ./TruffLeBToken
  yarn deploy:loom
  cd ..
}

function deploy_truffle_btoken_extdev {
  echo "deploy truffle btoken to extdev_plasma_us1"
  cd ./TruffLeBToken
  node ./mapping_rinkeby_to_extdev.js
  yarn deploy:extdev
  cd ..
}

function deploy_truffle_gateway_ganache {
  echo "deploy truffle gateway to ganache"
  cd ./TruffLeGateWay
  yarn deploy:ganache
  cd ..
}

function deploy_truffle_gateway_geth {
  echo "deploy truffle gateway to geth"
  cd ./TruffLeGateWay
  yarn deploy:geth
  cd ..
}

function deploy_truffle_gateway_rinkeby {
  echo "deploy truffle gateway to rinkeby"
  cd ./TruffLeGateWay
  yarn deploy:rinkeby
  cd ..
}

function start_loomnetwork {
  echo "start loom network"
  cd ./LoomNetwork
  ./loom init -f; cp ./genesis.example.json ./genesis.json; ./loom reset; ./loom run > ./loom.log 2>&1 &
  loom_pid=$!
  echo $loom_pid > loom.pid
  sleep 10
  cat ./loom.log
  cd ..
}

function run_loomnetwork {
  echo "start loom network"
  cd ./LoomNetwork
  ./loom run > ./loom.log 2>&1 &
  loom_pid=$!
  echo $loom_pid > loom.pid
  sleep 10
  cat ./loom.log
  cd ..
}


function start_ganache {
  echo "start ganache"
  cd ./TruffLeGateWay
  #yarn ganache-cli --account \
  #  "0x70f1384b24df3d2cdaca7974552ec28f055812ca5e4da7a0ccd0ac0f8a4a9b00,100000000000000000000000" \
  #  > ./ganache.log 2>&1 &
  yarn ganache-cli > ./ganache.log 2>&1 &
  ganache_pid=$!
  echo $ganache_pid > ganache.pid
  sleep 10
  cat ./ganache.log
  cd ..
}

function run_ganache {
  echo "start ganache"
  cd ./TruffLeGateWay
  #yarn ganache-cli --account \
  #  "0x70f1384b24df3d2cdaca7974552ec28f055812ca5e4da7a0ccd0ac0f8a4a9b00,100000000000000000000000" \
  #  > ./ganache.log 2>&1 &
  yarn ganache-cli > ./ganache.log 2>&1 &
  ganache_pid=$!
  echo $ganache_pid > ganache.pid
  sleep 10
  cat ./ganache.log
  cd ..
}

function start_geth {
  echo "start geth"
  ./geth/cleanup.sh
  ./geth/init.sh user
  ./geth/start.sh
}

# Mapping is necessary to "mirroring" the token on mainnet and dappchain
#function run_mapping {
#  echo "running mapping"
#  cd transfer-gateway-scripts
#  node mapping_crypto_cards.js > /dev/null 2>&1
#  node mapping_game_token.js > /dev/null 2>&1
#  cd ..
#}

case "$1" in
setup)
  echo "------------------------------------------------------------------------------------------"
  echo "installing necessary packages, this can take up to 3 minutes (depending on internet speed)"
  echo "------------------------------------------------------------------------------------------"
  echo
  if [ $(is_setup_already) = 1 ]; then
    echo "setup already ran"
    exit -1
  fi
  setup
  echo
  echo "-------------------------------------"
  echo "done, packages installed with success"
  echo "-------------------------------------"
  ;;
start_webclient)
  if [ $(is_setup_already) = 0 ]; then
    echo "please use the setup command first: ./FirstNetwork.sh setup"
    echo
    exit -1
  fi
  if [ $(check_port $webclient_port) != 0 ]; then
    echo "web client port $webclient_port is already in use"
    echo
    exit -1
  fi
  start_webclient
  ;;
start_hotwalletserver)
    if [ $(is_setup_already) = 0 ]; then
      echo "please use the setup command first: ./FirstNetwork.sh setup"
      echo
      exit -1
    fi
    if [ $(check_port $hotwalletserver_port) != 0 ]; then
      echo "how wallet server port $hotwalletserver_port is already in use"
      echo
      exit -1
    fi
    start_hotwalletserver
    ;;
start_loomnetwork)
  if [ $(is_setup_already) = 0 ]; then
    echo "please use the setup command first: ./FirstNetwork.sh setup"
    echo
    exit -1
  fi
  if [ $(check_port $loom_port_1) != 0 ] || [ $(check_port $loom_port_2) != 0 ]; then
    echo "some port from loom network already in use [$loom_port_1 or $loom_port_2]"
    echo
    exit -1
  fi
  start_loomnetwork
  deploy_truffle_btoken_loom
  tail -f ./LoomNetwork/loom.log
  ;;
run_loomnetwork)
  if [ $(is_setup_already) = 0 ]; then
    echo "please use the setup command first: ./FirstNetwork.sh setup"
    echo
    exit -1
  fi
  if [ $(check_port $loom_port_1) != 0 ] || [ $(check_port $loom_port_2) != 0 ]; then
    echo "some port from loom network already in use [$loom_port_1 or $loom_port_2]"
    echo
    exit -1
  fi
  run_loomnetwork
  tail -f ./LoomNetwork/loom.log
  ;;  
start_ganache)
  if [ $(is_setup_already) = 0 ]; then
    echo "please use the setup command first: ./FirstNetwork.sh setup"
    echo
    exit -1
  fi
  if [ $(check_port $ganache_port) != 0 ]; then
    echo "ganache port $ganache_port is already in use"
    echo
    exit -1
  fi
  start_ganache
  deploy_truffle_gateway_ganache
  tail -f ./TruffLeGateWay/ganache.log
  ;;
run_ganache)
  if [ $(is_setup_already) = 0 ]; then
    echo "please use the setup command first: ./FirstNetwork.sh setup"
    echo
    exit -1
  fi
  if [ $(check_port $ganache_port) != 0 ]; then
    echo "ganache port $ganache_port is already in use"
    echo
    exit -1
  fi
  run_ganache
  tail -f ./TruffLeGateWay/ganache.log
  ;;  
deploy_gateway_ganache)
  if [ $(is_setup_already) = 0 ]; then
    echo "please use the setup command first: ./FirstNetwork.sh setup"
    echo
    exit -1
  fi
  if [ $(check_port $ganache_port) != 0 ]; then
    echo "please start geth command first: ./FirstNetwork.sh start_ganache"
    echo
    exit -1
  fi
  deploy_gateway_ganache
  ;;
start_geth)
  if [ $(is_setup_already) = 0 ]; then
    echo "please use the setup command first: ./FirstNetwork.sh setup"
    echo
    exit -1
  fi
  if [ $(check_port $geth_port_1) != 0 ] || [ $(check_port $geth_port_2) != 0 ]; then
    echo "some port from geth already in use [$geth_port_1 or $geth_port_2]"
    echo
    exit -1
  fi
  start_geth
  ;;
deploy_gateway_geth)
  if [ $(is_setup_already) = 0 ]; then
    echo "please use the setup command first: ./FirstNetwork.sh setup"
    echo
    exit -1
  fi
  if [ $(check_port $geth_port_1) == 0 ] || [ $(check_port $geth_port_2) == 0 ]; then
    echo "please start geth command first: ./FirstNetwork.sh start_geth"
    echo
    exit -1
  fi
  deploy_truffle_gateway_geth
  ;;
deploy_gateway_rinkeby)
  if [ $(is_setup_already) = 0 ]; then
    echo "please use the setup command first: ./FirstNetwork.sh setup"
    echo
    exit -1
  fi
  deploy_truffle_gateway_rinkeby
  ;;
deploy_btoken_extdev)
  if [ $(is_setup_already) = 0 ]; then
    echo "please use the setup command first: ./FirstNetwork.sh setup"
    echo
    exit -1
  fi
  deploy_truffle_btoken_extdev
  ;;
*)
   echo "usage: $0 {setup|start_webclient|start_hotwalletserver|start_loomnetwork|run_loomnetwork|start_ganache|run_ganache|start_geth|deploy_gateway_ganache|deploy_gateway_geth|deploy_gateway_rinkeby|deploy_btoken_extdev}"
esac

exit 0
