기억해 둘 것
  1) assembly
    - solidity에서 EVM의 스토리지 및 메모리에 직접 접근하여 처리
    - 입력값을 받고 다른 컨트랙트를 호출한 후 반환받은 결과값을 다시 반환하는 식으로도 운용가능

  2) forwarder
    - solidity에서 assembly의 delegatecall을 이용하여 다른 컨트랙트를 호출하여 얻은 결과를 다시 반환함
    - 같은 컨트랙트를 사용자 혹은 트랜잭션 단위로 디플로이해야하는 경우 용이함

  3) delete
    - solidity에서 mapping된 값을 지울 때 "delete map[key]";
    - erc725 - KeyManager.sol:63

  4) calldata
    - solidity에서 컨트랙트의 함수가 '인코딩된 입력값 데이터'를 입력값으로 받을 때 변수 타입을 "bytes calldata"로 지정
    - erc725 - Identity.sol:67

  5) revert()
    - solidity에서 어떤 이유로 함수를 fail 시켜야할 때, revert()를 호출하면 컨트랙트 호출자의 가스 소모를 방지할 수 있음
    - erc725 - Identity.solL57

  6) selector
    - solidity에서 함수를 식별할 때 사용하는 4 바이트 정수
    - 소스코드 내에서 this.MyFunction.selector로 확인 가능
    - msg.data의 첫 4바이트
    - erc725-735 - ERC165.sol:31

  7) encodePacked
    - solidity 내부에서 abi.encodedPacked 처리를 한 후 keccak256을 계산하면, web3.utils의 keccak256과 같은 값이 나온다.
    - erc725-735 - ChangeManager.sol:207

  8) recover
    - solidity에서 서명을 검증하고 서명자가 누구인지 찾는 방법
    - keccak256(abi.encodePacked(ETH_PREFIX, toSign)).recover(signature)
    - ETH_PREFIX: bytes constant internal ETH_PREFIX = "\x19Ethereum Signed Message:\n32";
    - signature: web3.eth.sign의 결과값
    - erc725-735, ClaimManager.sol, getSignatureAddress()

  8-1) ecrecover
    - recover와 동일 ()
    - address signer = ecrecover(hash, sigV, sigR, sigS);
    - ethr-did-registry, EthereumDIDRegistry.sol, checkSignature()

  9) deploy
    - truffle에서 컨트랙트의 인스턴스를 얻는 방법은 다음 3가지가 있다
      > new() : 컨트랙트를 새로 디플로이하고, 해당 인스턴스를 얻는다.
      > deployed() : migration때 디플로이했던 통합 컨트랙트의 인스턴스를 얻는다.
      > at() : 특정 주소로 디플로이된 컨트랙트의 인스턴스를 얻는다(???)

  10) selfdestruct(address)
    - solidity 내부에서 호출시 해당 컨트랙트가 삭제됨
    - 컨트랙트의 코드가 컨트랙트 주소에 저장되어 있는데, 이 코드가 삭제됨
    - 컨트랙트의 balance는 입력받은 address로 옮겨짐
    - erc725-735, Destructible.sol

  11) multi signature
    - 보통의 컨트랙트는 함수 호출에 제약이 없다
    - 만약 여러명의 관리자가 존재하여 정해진 수 이상의 관리자가 승인해야만 수행되는 함수를 만들고 싶을 때 threshold, execute, approve를 이용한다.
      > modifier
        -- 호출할 함수에 modifier 설정
        -- 컨트랙트 내부 호출일 경우 통과
        -- threshold가 1일 경우 통과
        -- 나머지는 실패
        => threshold가 1이라면 일반적인 호출로 함수를 사용할 수 있지만, 2 이상이라면 일반적인 호출이 불가능해진다.
      > execute
        -- 호출할 컨트랙트의 주소, 함수 및 입력값을 가진 데이터, 전송할 이더를 받아 입력된 컨트랙트의 함수를 호출하는 execute 구현
        -- execute 호출시 입력값의 컨트랙트 주소를 자체 주소로 지정
        -- threshold에서 1을 뺌
        -- threshold != 0 => 입력값들과 threshold를 구조체에 저장 approve 생성(id로 관리)
        -- threshold == 0 => 컨트랙트 함수 호출
      > approve
        -- execute로 생성된 approve에 승인한다면 저장된 구조체의 threshold에서 1을 뺌
        -- threshold != 0 => threshold를 갱신하고 리턴
        -- threshold == 0 => 컨트랙트 함수 호출
    - 즉, 최초 발의자가 execute를 실행하면 자신 몫의 threshold를 뺀 상태로 구조체로 저장
    - 다른 관리자가 승인할 때마다 threshold를 1씩 차감
    - threshold == 0이 되면 컨트랙트 호출 실행
    - erc725-735, MultiSig.sol

  12) special variables
    - 다음은 solidity 내부에서 특정 값들을 나타낸다.
      > block.blockhash(uint blockNumber): 주어진 블록의 블록 해시
      > block.coinbase: 현재 블록의 마이너 주소
      > block.difficulty: 현재 블록 채굴의 난이도
      > block.gaslimit: 현재 블록의 gas limit
      > block.number: 현재 블록의 번호
      > block.timestamp: 현재 블록의 타임스탬프
      > getleft: 남은 가스
      > msg.data: 호출 데이터
      > msg.gas: 남은 가스(deprecated)
      > msg.sender: 메시지 전송자
      > msg.sig: msg.data의 첫 4바이트 == 함수 식별자
      > msg.value: 메시지와 같이 전송된 이더(wei)
      > now: 현재 블록의 타임스탬프(alias for block.timestamp)
      > tx.gasprice: 트랜잭션의 gas price
      > tx.origin: 트랜잭션의 전송자
    - https://solidity.readthedocs.io/en/v0.4.24/units-and-global-variables.html

  13) 다음의 해시값은 같다.
    - node에서:
        const data = dataBuffer.from("changeOwner").toString("hex") + stripHexPrefix(newOwner)
        const dataToSign = "1900" + stripHexPrefix(컨트랙트 주소) + stripHexPrefix(identity) + data
        const hash = Buffer.from(sha3.buffer(Buffer.from(dataToSign, "hex")))
    - solidity에서:
        bytes32 hash = keccak256(byte(0x19), byte(0), this, identity, "changeOwner", newOwner);
    - ethr-did-registry, test/ethereum_did_registry.js, signData()
    - ethr-did-registry, EthereumDIDRegistry.sol, changeOwnerSigned





2. self soverine
 -- claim
  1) 개인정보 소유자 A와 claim issuer B는 각각 Identity 컨트랙트를 디플로이한다.
  2) A는 자신의 개인정보를 입증받기 위해 B에게 자신의 Identity 컨트랙트 주소와 개인정보를 건내준다.
  3) B는 A로부터 받은 정보들을 검증하고, 자신의 서명키로 서명한다.
    - 이 때, B의 Identity 디플로이시 설정된 claim 키로 서명한다.
  4) B는 A의 Identity 컨트랙트에 claim을 추가하기 위해 다음 행동을 한다.
    - A의 Identity의 addClaim 함수를 통해 abi를 얻는다.
    - B의 Identity의 execute를 통해 위에서 얻은 abi를 실행한다.
  5) A는 추가 대기 claim 중 일부를 approve하여 자신의 claim에 추가한다.

 -- verify
 -- ???
  1) 자신이 디플로이한 Identity 컨트랙트의 주소와 claim의 발급 및 관리는 이해가 되지만, Identity 컨트랙트와 실제 사용할 주소와의 관계를 모르겠다.


 -- erc
  1) erc 725
  2) erc 725 + erc 735
  3) erc 1056
  4) erc 1484
