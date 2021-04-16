# 프로젝트 설명 
 - geth 
   go언어로 작성한 이더리움 블록체인 
   
 - LoomNetwork
   LoomNetwork 블록체인 

 - TruffLeGateWay
   Gateway, 이더리움과 LoomNetwork 사이에 이더 전송을 중계하는 시스템
 
 - WebCnt 
   웹을 이용하여 이더 전송을 테스트할 수 있는 웹 (미사용) 
 
 - TruffLeBToken
   solidity로 작성된 스마트컨트랙트 
     
 - TstBToken
   TruffLeBToken에서 작성한 스마트컨트랙트를 자동으로 테스트하기 위한 테스트프로젝트 
 
 - Identity 
   사용자의 권한을 설정하기 위한 권한관리 시스템 (미사용) 
   Scripts/Identity로 이관 
   
 - Verifier 
   마이크로트랜잭션의 정산 및 채널 종료를 처리해주는 시스템 
   
 - TruffLeBIdentity 
   사용자의 권한관리 API 
   
 - Scripts/ApiS 
   Chain Link API 
  
 - Scripts/Identity 
   사용자의 권한을 설정하기 위한 권한관리 시스템
   대용량 블록체인에서 19+에 사용   

 - Scripts/Listener 
   event listener api 


# nvm 설치(ubuntu)
  ```bash
  $ sudo apt-get install build-essential libssl-dev
  $ curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.33.4/install.sh | bash
  $ export NVM_DIR="$HOME/.nvm"
  $ [ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"
  $ nvm install v8.9
  $ nvm alias default v8.9 #set default to v8.9
  $ npm install --global yarn
  $ sudo apt-get install python
  $ npm install -g truffle
  $ npm install -g express-generator
  $ npm install -g forever
  $ npm install -g nodemon
  ```

# nvm 설치(windows)
  ```bash
  $ nvm install v6.9.5
  $ nvm install v8.4
  $ nvm use v8.4
  $ npm install --global yarn
  $ npm install --global --production windows-build-tools # 관리자 권한으로 실행
  $ npm config set python C:\python27
  ```

# MetaMask 설치
- https://metamask.io/

# 테스트용 이더 발급
- http://rinkeby-faucet.com # 0.001 eth
  ```
  ex) 0xD53000e41163A892B4d83b19A2fEC184677a1272
  ```
- https://faucet.rinkeby.io/ # 최대 18.75 eth / 3days
  ```
  ex) https://www.facebook.com/laewook/posts/2084353508299956
  ```

# 프로젝트 코드 생성
- https://infura.io

# golang 설치
  ```bash
  $ vim ~/.bashrc
  ```
  ```
  export GOPATH="$HOME/gopath"
  export GOROOT="/opt/go"
  PATH=$GOROOT/bin:$GOPATH/bin:$PATH
  ```
  ```bash
  $ source ~/.bashrc
  $ export GO_VER=1.11.1
  $ export GO_URL=https://storage.googleapis.com/golang/go${GO_VER}.linux-amd64.tar.gz
  $ sudo mkdir -p $GOROOT
  $ sudo mkdir -p $GOPATH
  $ sudo curl -sL $GO_URL | (cd $GOROOT && sudo tar --strip-components 1 -xz)
  ```

# go-ethereum 설치
  ```bash
  $ d $GOPATH
  $ git clone https://github.com/ethereum/go-ethereum.git
  $ cd go-ethereum
  $ make geth
  $ vim ~/.bashrc
  ```
  ```
  PATH=$GOPATH/go-ethereum/build/bin/:$PATH
  ```
  ```bash
  $ source ~/.bashrc
  ```

# 프로젝트 초기화
  ```bash
  $./FirstNetwork.sh setup
  ```

# Loom Network 설치
  ```bash
  $ curl https://raw.githubusercontent.com/loomnetwork/loom-sdk-documentation/master/scripts/get_loom.sh | sh
  $ ./loom genkey -k priv_key -a pub_key > ./loc_addr
  $ ./loom init
  $ ./loom run
  ```

# Loom 샘플코드 실행
  ```bash
  $ git clone https://github.com/loomnetwork/truffle-dappchain-example
  $ cd truffle-dappchain-example
  $ cp ../priv_key extdev_private_key
  $ yarn install
  $ yarn deploy
  ```
