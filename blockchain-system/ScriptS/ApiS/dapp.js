/*****************************************************************
*                           Chainlink API 
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5 
* @module ChainLink API / dapp  
* @history : 
*****************************************************************/

const fs = require('fs');
const path = require('path');
var Env = JSON.parse(fs.readFileSync(getRumHome() + '/conf/.env.json', 'utf8'))

const Web3 = require('web3');
const Util = require('ethereumjs-util')
const BN = require('bn.js')
const ECurve = require('ecurve')
const BI = require('bigi')
var axios = require('axios')
var Log4JS = require('log4js')

var Logger
var Logger = Log4JS.getLogger('DApp')

const jsonBMSP = require('../../TruffLeBToken/build/contracts/BMSP.json')
const jsonBToken = require('../../TruffLeBToken/build/contracts/BToken.json')
const jsonBChannel = require('../../TruffLeBToken/build/contracts/BChannel.json')
const jsonBconfig = require('../../TruffLeBToken/build/contracts/Bconfig.json')
const jsonBIdentity = require('../../TruffLeBIdentity/build/contracts/BIdentity.json')
const jsonBProduct = require('../../TruffLeBToken/build/contracts/BProduct.json')

//const dappGatewayAddress = require('../../WebCLnt/src/gateway_dappchain_address_extdev-plasma-us1.json')
const dappGatewayAddress = require('../../WebCLnt/src/gateway_dappchain_address_local.json')


function sleep(ms) {
  return new Promise(resolve => { setTimeout(resolve, ms) })
}

/**
 * nonce 중복을 방지하기 위해서는 transaction이 중복 실행되면 않됨
 * 중복실행이 되면 invalid nonce가 발생함
 */
var isworkingFlag = false;
const workingWaitTime = 20;
async function workingStart() {
  let workingWaitTimeBuf = 0;
  while (1) {
    workingWaitTimeBuf++;
    if (isworkingFlag == false || workingWaitTimeBuf == workingWaitTime) {
      break;
    } else {
      Logger.debug('Transation duplicate. function waiting! pid=' + process.pid + ', wait time=' + workingWaitTimeBuf);
      await sleep(1000);
    }
  }
  isworkingFlag = true;
  if (workingWaitTimeBuf == workingWaitTime) {
    isworkingFlag = false;
    return false;
  }
  return true;
}
async function workingEnd() {
  isworkingFlag = false;
}


/**
 * 프로그램이 실행한 폴더 위치를 찾는다. 
 * 참고: 환경정보파일을 읽어야 함으로 실제 실행된 위치가 중요  
 */
function getRumHome() {
  if (path.win32.basename(process.argv[0]) == "node" || path.win32.basename(process.argv[0]) == "node.exe") {
    return ".";
  } else {
    return path.dirname(process.argv[0]);
  }
}


const {
  web3Signer
} = require('./web3Signer.js')
var Nacl = require('tweetnacl')
var homePath = ""

const {
  NonceTxMiddleware,
  SignedTxMiddleware,
  Client,
  Address,
  LocalAddress,
  LoomProvider,
  CryptoUtils,
  Contracts,
  Web3Signer
} = require('loom-js/dist');

var CLient = null;

/**
 * DAPP 초기화 함수 
 */
module.exports = class DappInit_ {
  static async createAsync(b64_private_key) {
    Logger.debug('createAsync start:')

    const PrivateKey = CryptoUtils.B64ToUint8Array(b64_private_key);
    const PubLicKey = CryptoUtils.publicKeyFromPrivateKey(PrivateKey)

    if (CLient != null) {
      CLient.disconnect();
      Logger.debug("previous session close! ")
    }

    CLient = new Client(
      Env.chain_id,
      Env.write_url,
      Env.read_url
    )

    CLient.on('error', msg => {
      console.error(msg)
    })

    const WWW3 = new Web3(new LoomProvider(CLient, PrivateKey))

    CLient.txMiddleware = [
      new NonceTxMiddleware(PubLicKey, CLient),
      new SignedTxMiddleware(PrivateKey)
    ]

    const LoomAddress = new Address(CLient.chainId, LocalAddress.fromPublicKey(PubLicKey))

    const AddressMapper = await Contracts.AddressMapper.createAsync(
      CLient,
      new Address(CLient.chainId, LocalAddress.fromPublicKey(PubLicKey))
    )

    const EthCoin = await Contracts.EthCoin.createAsync(
      CLient,
      new Address(CLient.chainId, LocalAddress.fromPublicKey(PubLicKey))
    )

    const TransferGateway = await Contracts.TransferGateway.createAsync(
      CLient,
      new Address(CLient.chainId, LocalAddress.fromPublicKey(PubLicKey))
    )

    const NetworkID = Object.keys(jsonBChannel.networks)[0]
    const Addr = LocalAddress.fromPublicKey(PubLicKey).toString()
    const BProduct = new WWW3.eth.Contract(
      jsonBProduct.abi,
      jsonBProduct.networks[NetworkID].address, {
      Addr
    }
    )
    const BconfigCon = new WWW3.eth.Contract(
      jsonBconfig.abi,
      jsonBconfig.networks[NetworkID].address, {
      Addr
    }
    )

    const BMSPCon = new WWW3.eth.Contract(
      jsonBMSP.abi,
      jsonBMSP.networks[NetworkID].address, {
      Addr
    }
    )

    const BTokenCon = new WWW3.eth.Contract(
      jsonBToken.abi,
      jsonBToken.networks[NetworkID].address, {
      Addr
    }
    )

    const BChannelCon = new WWW3.eth.Contract(
      jsonBChannel.abi,
      jsonBChannel.networks[NetworkID].address, {
      Addr
    }
    )

    const BIdentityCon = new WWW3.eth.Contract(
      jsonBIdentity.abi,
      jsonBIdentity.networks[NetworkID].address, {
      Addr
    }
    )

    return new DappInit_(WWW3, PrivateKey, PubLicKey, CLient, AddressMapper, EthCoin, TransferGateway, Addr, BconfigCon, BMSPCon, BTokenCon, BChannelCon, BIdentityCon, BProduct)
  }

  /**
   * DAPP 생성자 
   */
  constructor(www3, private_key, pubLic_key, cLient, address_mapper, eth_coin, transfer_gateway, addr, bconfig_con, bmsp_con, btoken_con, bchannel_con, bidentity_con, bproduct_con) {
    this._Web3 = www3
    this._PrivateKey = private_key
    this._PubLicKey = pubLic_key
    this._CLient = cLient
    this._AddressMapper = address_mapper
    this._EthCoin = eth_coin
    this._TransferGateway = transfer_gateway
    this._Address = addr
    this._Bconfig = bconfig_con
    this._BMSP = bmsp_con
    this._BToken = btoken_con
    this._BChannel = bchannel_con
    this._BIdentity = bidentity_con
    this._BProduct = bproduct_con

    this._TransferGateway.on(Contracts.TransferGateway.EVENT_TOKEN_WITHDRAWAL, event => {
      if (this._OnTokenWithdrawaL) {
        this._OnTokenWithdrawaL(event)
      }
    })
  }

  /**
   * 블록체인 통신 세션 종료 
   */
  async SessinClose() {
    try {
      this._CLient.disconnect();
    } catch (e) {
      Logger.error("session close:" + e.message)
      return null
    }
  }

  /** 
   * 로거 설정 
   * app.js로 부터 로거를 전달받음 
   */
  static async setLogger(log) {
    Logger = log
  }

  /** 
   * 홈디렉토리 설정 
   * app.js로 부터 홈디렉토리를 전달받음 
   */
  static async setHomeDir(homedir) {
    homePath = homedir
  }

  /**
   * 사용자의 private key, public key, address 등을 반환한다. 
   */
  GetPrivateKey() {
    return this._PrivateKey
  }

  GetPubLicKey() {
    return this._PubLicKey
  }

  GetCLient() {
    return this._CLient
  }

  GetAddress() {
    return this._Address
  }

  GetAccount() {
    return LocalAddress.fromPublicKey(this._PubLicKey).toString()
  }

  async GetAddressMappingAsync(eth_address) {
    try {
      const From = new Address('eth', LocalAddress.fromHexString(eth_address))
      return await this._AddressMapper.getMappingAsync(From)
    } catch (_) {
      return null
    }
  }

  /**
   * 입력된 전자지갑을 이용하여 사용자의 계정ID와 dapp address를 읽는다. 
   * @param {*} wallet 
   */
  async SignAsync(wallet) {
    const From = new Address('eth', LocalAddress.fromHexString(wallet.getAddressString()))
    const To = new Address(this._CLient.chainId, LocalAddress.fromPublicKey(this._PubLicKey))
    const WWW3Signer = new web3Signer(wallet.getPrivateKey())

    if (await this._AddressMapper.hasMappingAsync(From)) {
      const mappingInfo = await this._AddressMapper.getMappingAsync(From)
      const ethAddress = CryptoUtils.bytesToHexAddr(mappingInfo.from.local.bytes)
      const dappAddress = CryptoUtils.bytesToHexAddr(mappingInfo.to.local.bytes)
      return {
        ethAddress: ethAddress,
        dappAddress: dappAddress
      }
    }
    await this._AddressMapper.addIdentityMappingAsync(From, To, WWW3Signer)
    return {
      ethAddress: wallet.getAddressString(),
      dappAddress: Util.bufferToHex(LocalAddress.fromPublicKey(this._PubLicKey).bytes)
    }
  }

  async ApproveAsync(amount) {
    return await this._EthCoin.approveAsync(
      new Address(
        this._CLient.chainId,
        LocalAddress.fromHexString(dappGatewayAddress.address)
      ),
      new BN(amount)
    )
  }

  async GetBaLanceAsync() {
    const UserAddress = new Address(this._CLient.chainId, LocalAddress.fromPublicKey(this._PubLicKey))
    return await this._EthCoin.getBalanceOfAsync(UserAddress)
  }

  // dapp_gateway
  OnTokenWithdrawaL(fn) {
    this._OnTokenWithdrawaL = fn
  }

  async WithdrawEthAsync(amount) {
    await this._TransferGateway.withdrawETHAsync(
      new BN(amount),
      new Address(
        this._CLient.chainId,
        LocalAddress.fromHexString(dappGatewayAddress.address)
      )
    )
  }

  async WithdrawaLReceiptAsync(address) {
    return await this._TransferGateway.withdrawalReceiptAsync(
      new Address(this._CLient.chainId, LocalAddress.fromHexString(address))
    )
  }

  //----------------------------------------------------------------- msp ---------------------------------------------------------------//

  /**
   * owner를 지정한다. 
   * @param {string} target owner account id 
   */
  async appointManager(target) {
    await workingStart();
    try {
      const From = new Address('eth', LocalAddress.fromHexString('0x' + target))

      if (!(await this._AddressMapper.hasMappingAsync(From))) {
        Logger.error("not dapp user")
        return false
      }
      const mappingInfo = await this._AddressMapper.getMappingAsync(From)
      const dappAddress = CryptoUtils.bytesToHex(mappingInfo.to.local.bytes)
      await this._BMSP.methods.appointManager(dappAddress.toLowerCase())
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
    } finally {
      await workingEnd();
    }
  }

  /**
   * 사용자의 권한을 요청한다. 
   * @param {int} role 요청 권한 
        1: 'Packager'
        2: 'ContentsProvider'
        4: 'StorageProvider'
        8: 'Distributor'
   */
  async requestEnroll(role) {
    await workingStart();
    try {
      await this._BMSP.methods.requestEnroll(role)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
    } finally {
      await workingEnd();
    }
  }

  /**
   * 요청된 권한을 얻어온다.
   * @Response {json} 요청자 및 요청권한 
   */
  async getRequests() {
    await workingStart();
    try {
      var detailsArray = []
      const roles = {
        1: 'Packager',
        2: 'ContentsProvider',
        4: 'StorageProvider',
        8: 'Distributor'
      }

      const nextIndex = await this._BMSP.methods.getNextIndex().call({
        from: this._Address
      })

      const requestLength = await this._BMSP.methods.getRequestLength().call({
        from: this._Address
      })

      for (var i = parseInt(nextIndex); i < parseInt(requestLength); i++) {
        var obj = {
          index: i
        }
        var details = await this._BMSP.methods.getRequestDetails(i).call({
          from: this._Address
        })
        obj.requester = details.requester
        obj.role = roles[details.role]
        detailsArray.push(obj)
      }
      return detailsArray
    } finally {
      await workingEnd();
    }
  }

  /**
   * 요청권한을 승인한다. 
   * 주의: owner만 수행가능 
   * @param {*} approvals  승인목록 
   */
  async approveRole(approvals) {
    await workingStart();
    try {
      await this._BMSP.methods.approveRole(approvals)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
    } finally {
      await workingEnd();
    }
  }

  /**
   * 
   * @param {string} target 검증하고자하는 사용자 계정 ID 
   * @param {string} role 검증하고자 하는 권한 
        1: 'Packager'
        2: 'ContentsProvider'
        4: 'StorageProvider'
        8: 'Distributor'
   * @returns {*} 검증 결과 json 
   */
  async verifyRole(target, role) {
    const From = new Address('eth', LocalAddress.fromHexString('0x' + target))
    if (!(await this._AddressMapper.hasMappingAsync(From))) {
      Logger.error("not dapp user")
      return false
    }
    //  var isRevoke = role == 2 ? true : false;
    var isRevoke = false
    const mappingInfo = await this._AddressMapper.getMappingAsync(From)
    const dappAddress = CryptoUtils.bytesToHex(mappingInfo.to.local.bytes)
    return await this._BMSP.methods.verifyRole(dappAddress.toLowerCase(), role, isRevoke).call({
      from: this._Address
    })
  }
  //-------------------------------------------------------------------------------------------------------------------------------------//

  //-------------------------------------------------------------- get cid --------------------------------------------------------------//

  /**
   * 콘텐츠의 CID를 얻는다. 
   * @param {string} target 요청자의 사용자계정ID 
   * @returns {String} 발급된 CID 
   */
  async getCID(target) {
    await workingStart();
    try {
      const From = new Address('eth', LocalAddress.fromHexString('0x' + target))
      if (!(await this._AddressMapper.hasMappingAsync(From))) {
        Logger.error("not dapp user")
        return -1
      }
      const mappingInfo = await this._AddressMapper.getMappingAsync(From)
      const dappAddress = CryptoUtils.bytesToHex(mappingInfo.to.local.bytes)
      const tx = await this._BToken.methods.getCID(dappAddress)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
      return tx.events.NewID.returnValues.Id
    } finally {
      await workingEnd();
    }
  }
  //-------------------------------------------------------------------------------------------------------------------------------------//

  //-------------------------------------------------------- invoke transaction ---------------------------------------------------------//

  /**
   * 콘텐츠 정보를 등록한다. 
   * @param {string} cid 콘텐츠의 CID
   * @param {string} ccid 복합콘텐츠 CCID
   * @param {string} version 복합콘텐츠 버전
   * @param {int} fee 콘텐츠 저작권료 
   * @param {string} fileHashes 파일목록 
   * @param {string} _chunks 파일 청크수
   * @param {string} info 콘텐츠 정보 
   * @param {string} targetDist 지정된 배포자 계정ID 
   * @param {string} targetUser 지정된 사용자 계정ID
   * @param {string} ad 사용권한 0:19세이상, 1:사용그룹제한, 2.추가정보 => max 5
   * @returns {json} 등록결과 json 
   */
  async registerData(cid, ccid, version, fee, fileHashes, _chunks, info, targetDist, targetUser, ad) {
    await workingStart();
    try {
      if (targetDist.length == 0) targetDist = [0]
      if (targetUser.length == 0) targetUser = [0]
      await this._BToken.methods.registerData(cid, ccid, version, fee, fileHashes, _chunks)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("registerData receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })

      // Logger.debug(" before ccid" +ccid )
      // Logger.debug(" before version" + version )
      // Logger.debug(" before info" + info )
      // Logger.debug(" before targetDist" + JSON.stringify(targetDist) )
      // Logger.debug(" before targetUser" + JSON.stringify(targetUser) ) 
      // Logger.debug(" before registerDataAttr" + JSON.stringify(ad) )
      const tx = await this._BToken.methods.registerDataAttr(0, ccid, version, info, targetDist, targetUser, ad)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("registerDataAttr receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
      return tx.events.NewID.returnValues.Id
    } finally {
      await workingEnd();
    }

  }

  /**
   * 콘텐츠 정보를 수정한다. 
   * @param {string} dataId registerData()를 통해 발급받은 등록ID 
   * @param {string} info 콘텐츠 정보 
   * @param {string} targetDist 지정된 배포자 계정ID 
   * @param {string} targetUser 지정된 사용자 계정ID
   * @param {string} ad 사용권한 0:19세이상, 1:사용그룹제한, 2.추가정보 => max 5
   * @returns {json} 등록결과 json 
   */
  async modifyData(dataId, info, targetDist, targetUser, ad) {
    await workingStart();
    try {
      if (targetDist.length == 0) targetDist = [0]
      if (targetUser.length == 0) targetUser = [0]

      const tx = await this._BToken.methods.registerDataAttr(dataId, "", "", info, targetDist, targetUser, ad)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("registerDataAttr receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
      return tx.events.NewID.returnValues.Id
    } finally {
      await workingEnd();
    }

  }

  /**
   * 상품정보를 등록한다. 
   * @param {string} ccid 복합콘텐츠 CCID
   * @param {string} version 복합콘텐츠 버전 
   * @param {int} price 상품가격 
   */
  async registerProduct(ccid, version, price) {
    await workingStart();
    try {
      const tx = await this._BToken.methods.registerProduct(ccid, version, price)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
      return tx.events.NewID.returnValues.Id
    } finally {
      await workingEnd();
    }
  }

  /**
   * 상품을 구매한다. 
   * @param {*} productId registerProduct()를 통해 얻은 상품ID 
   */
  async buyProduct(productId) {
    await workingStart();
    try {
      const productPrice = (await this.getProductDetails(productId))[2]
      const tx = await this._BToken.methods.buyProduct(productId)
        .send({
          from: this._Address,
          value: productPrice
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
      return tx.events.NewID.returnValues.Id
    } finally {
      await workingEnd();
    }
  }

  /**
   * 구매ID를 이용하여 파일을 다운로드 받을 수 있는지 검사 
   * @param {*} tokenId buyProduct()를 통해얻은 구매ID 
   */
  async chkTokenForChannelOpen(tokenId) {
    await workingStart();
    try {
      const rst = await this._BMSP.methods.verifyRole(this._Address, 4, false).call({
        from: this._Address
      })
      if (!rst) {
        let tokenInfo = await this._BToken.methods.getTokenDetails(tokenId).call({
          from: this._Address
        })
        // Logger.debug("owner:" + tokenInfo[0].toLowerCase() + ",_Address:" + this._Address.toLowerCase())
        if (tokenInfo[0].toLowerCase() == this._Address.toLowerCase()) {
          return true
        } else {
          return false
        }
      }
      return true;
    } finally {
      await workingEnd();
    }
  }

  /**
   * 파일을 다운로드하기위해서 채널을 OPEN한다. 
   * @param {*} tokenId 구매ID
   * @param {*} key 수신자의 public key 
   * @param {*} chunkTotNo 전체 청크갯수 
   */
  async channelOpen(tokenId, key, chunkTotNo) {
    await workingStart();
    try {
      var deposit = 0
      var total = 0
      const rst = await this._BMSP.methods.verifyRole(this._Address, 4, false).call({
        from: this._Address
      })
      if (!rst) {
        if (chunkTotNo <= 0) {
          deposit = (await this.getDepositNCollateral(tokenId))
        } else {
          deposit = (await this.getDepositNCollateral2(chunkTotNo))
        }
        total = parseInt(deposit[0]) + parseInt(deposit[1])
      }
      const tx = await this._BChannel.methods.channelOpen(tokenId, key, chunkTotNo)
        .send({
          from: this._Address
          , value: total
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
      return tx.events.NewID.returnValues.Id
    } finally {
      await workingEnd();
    }
  }

  /**
   * 파일다운로드가 완료되어 채널을 닫는다. 
   * @param {*} channelId open된 채널 ID 
   * @param {*} senders 수신자 목록 
   * @param {*} chunks 청크갯수 목록 
   * @param {*} murkleroot 전송영수증의 머클루트 
   */
  async channelOff(channelId, senders, chunks, murkleroot) {
    await workingStart();
    try {
      await this._BChannel.methods.settleChannel(murkleroot, channelId, senders, chunks)
        .send({
          from: this._Address,
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })

    } finally {
      await workingEnd();
    }
  }

  //-------------------------------------------------------------------------------------------------------------------------------------//

  //--------------------------------------------------------------- list ----------------------------------------------------------------//

  /**
   * 사용자가 등록한 콘텐츠, 상품, 구매내역을 반환한다. 
   * @param {*} flag  
   */
  async getList(flag) {
    await workingStart();
    try {
      return this._BToken.methods.getList(flag).call({
        from: this._Address
      })
    } finally {
      await workingEnd();
    }
  }

  /**
   *  콘텐츠, 상품, 구매내역을 반환한다. 
   * @param {String } dataId 데이터 ID 
   */
  async _BToken(dataId) {
    return this._BChannel.methods.getFileList(dataId).call({
      from: this._Address
    })
  }

  /**
   * 콘텐츠, 상품, 구매내역을 반환한다. 
   * @param {String } dataId 데이터 ID 
   */
  async listFileWithDataId(dataId) {
    return this._BToken.methods.getFileList(dataId).call({
      from: this._Address
    })
  }

  /**
   * 콘텐츠, 상품, 구매내역을 반환한다. 
   * @param {String} ccid 복합콘텐츠 CCID 
   * @param {String} version 복합콘텐츠 version 
   */
  async listFileWithCCIDNVersion(ccid, version) {
    return this._BToken.methods.getFileList(ccid, version).call({
      from: this._Address
    })
  }
  //-------------------------------------------------------------------------------------------------------------------------------------//

  //-------------------------------------------------------------- details --------------------------------------------------------------//

  /**
   * 콘텐츠 기본정보를 얻는다.
   * @param {string} dataId 데이터 ID 
   */
  async getDataDetailsWithId(dataId) {
    return this._BToken.methods.getDataDetails(dataId).call({
      from: this._Address
    })
  }

  /**
   * 콘텐츠 기본정보를 얻는다.
   * @param {string} ccid 복합콘텐츠 CCID 
   * @param {string} version 복합콘텐츠 version 
   */
  async getDataDetailsWithCCIDNVersion(ccid, version) {
    return this._BToken.methods.getDataDetails(ccid, version).call({
      from: this._Address
    })
  }
  /**
   * 콘텐츠 추가정보를 얻는다. 
   * @param {string} dataId 데이터 ID
   */
  async getDataAtDetailsID(dataId) {
    return this._BToken.methods.getDataAtDetails(dataId).call({
      from: this._Address
    })
  }

  /**
   * 콘텐츠 추가정보를 얻는다. 
   * @param {string} ccid 복합콘텐츠 CCID 
   * @param {string} version 복합콘텐츠 version 
   */
  async getDataAtDetailsWithCCIDNVersion(ccid, version) {
    return this._BToken.methods.getDataAtDetails(ccid, version).call({
      from: this._Address
    })
  }

  async getFileDetailsWithId(fileId) {
    return this._BToken.methods.getFileDetails1(fileId).call({
      from: this._Address
    })
  }

  async getFileDetailsWithHash(hash) {
    return this._BToken.methods.getFileDetails2(hash).call({
      from: this._Address
    })
  }

  /**
   * 상품정보를 얻는다. 
   * @param {string} productId 상품ID 
   */
  async getProductDetails(productId) {
    return this._BToken.methods.getProductDetails(productId).call({
      from: this._Address
    })
  }

  /**
   * 구매정보를 얻는다 
   * @param {string} tokenId 구매ID
   */
  async getTokenDetails(tokenId) {
    return this._BToken.methods.getTokenDetails(tokenId).call({
      from: this._Address
    })
  }

  /**
   * 전송비와 보증금 액수를 확인한다. 
   * @param {string} tokenId 구매ID
   */
  async getDepositNCollateral(tokenId) {
    return this._BChannel.methods.getDepositNCollateral(tokenId).call({
      from: this._Address
    })
  }

  /**
   * 전송비와 보증금 액수를 확인한다. 
   * @param {string} totChunks 총 청크수
   */
  async getDepositNCollateral2(totChunks) {
    return this._BChannel.methods.getDepositNCollateral2(totChunks).call({
      from: this._Address
    })
  }

  /**
   * 전송 채널 정보를 얻는다. 
   * @param {string} channelId 채널ID 
   */
  async getChannelDetails(channelId) {
    return this._BChannel.methods.getChannelDetails(channelId).call({
      from: this._Address
    })
  }

  /**
   * 채널 상태를 체크한다. 
   * @param {string} target  수신자 ID 
   * @param {string} cid cid 
   */
  async checkValidToken(target, cid) {
    await workingStart();
    try {
      const From = new Address('eth', LocalAddress.fromHexString('0x' + target))
      if (!(await this._AddressMapper.hasMappingAsync(From))) {
        Logger.error("not dapp user")
        return false
      }

      const mappingInfo = await this._AddressMapper.getMappingAsync(From)
      const dappAddress = CryptoUtils.bytesToHex(mappingInfo.to.local.bytes)
      return this._BChannel.methods.checkValidToken(dappAddress, cid).call({
        from: this._Address
      })
    } finally {
      await workingEnd();
    }
  }
  //-------------------------------------------------------------------------------------------------------------------------------------//

  //--------------------------------------------------------------- revoke --------------------------------------------------------------//
  /**
   * 사용자 권한을 제거한다. 
   * @param {string} target  사용자 ID
   * @param {string} role 사용자 역활 
   *     packager 1,  contents provider 2,  storage provider 4 , distributor 8  
   * @param {bool} dD 데이터 차단 여부  차단 true, 차단하지않음 false 
   * @param {bool} dP 상품차단여부 차단 true, 차단하지않음 false 
   */
  async revokeUser(target, role, dD, dP) {
    await workingStart();
    try {
      const From = new Address('eth', LocalAddress.fromHexString('0x' + target))
      if (!(await this._AddressMapper.hasMappingAsync(From))) {
        Logger.error("not dapp user")
        return false
      }

      const mappingInfo = await this._AddressMapper.getMappingAsync(From)
      const dappAddress = CryptoUtils.bytesToHex(mappingInfo.to.local.bytes)
      await this._BMSP.methods.revokeUser(dappAddress, role, dD, dP)
        .send({
          from: this._Address,
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
    } finally {
      await workingEnd();
    }
  }

  /**
   * 등록된 콘텐츠 정보를 삭제한다. 
   * @param {string} dataId  데이터 ID
   * @param {bool} deleteAll 콘텐츠를 사용한 모든 상품의 차단여부:  차단 true, 차단하지않음 false
   */
  async revokeData(dataId, deleteAll) {
    await workingStart();
    try {
      await this._BMSP.methods.revokeData(dataId, deleteAll)
        .send({
          from: this._Address,
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
    } finally {
      await workingEnd();
    }
  }

  /**
   * 등록된 상품을 차단한다. 
   * @param {string} productId 상품ID 
   */
  async revokeProduct(productId) {
    await workingStart();
    try {
      await this._BMSP.methods.revokeProduct(productId)
        .send({
          from: this._Address,
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
    } finally {
      await workingEnd();
    }
  }
  //-------------------------------------------------------------------------------------------------------------------------------------//

  //---------------------------------------------------------------- sign ---------------------------------------------------------------//

  /**
   * 전자서명 생성 (전자서명+원문 형식)
   * @param {string} msg 입력데이터 json 
   */
  async signReceipt(msg) {
    const Msg = Buffer.from(JSON.stringify(msg))
    const sign = CryptoUtils.Uint8ArrayToB64(Nacl.sign(Msg, this._PrivateKey))
    const pubKey = CryptoUtils.Uint8ArrayToB64(Util.toBuffer(CryptoUtils.bytesToHexAddr(this._PubLicKey)))
    return {
      sign,
      pubKey
    }
  }

  /**
   * 전자서명 검사 (전자서명+원문 형식)
   * @param {string} signB64 전자서명값
   * @param {string} publicKeyB64 공개키값 
   */
  async verifyReceipt(signB64, publicKeyB64) {
    const sign = CryptoUtils.B64ToUint8Array(signB64)
    const publicKey = CryptoUtils.B64ToUint8Array(publicKeyB64)

    const msgBytes = Nacl.sign.open(sign, publicKey)
    if (msgBytes == null) {
      return null
    }
    const msg = JSON.parse(Buffer.from(msgBytes.buffer, msgBytes.byteOffset, msgBytes.byteLength).toString())
    return msg
  }

  /**
   * API에서 로드한 키쌍의 공개키를 반환한다. 
   */
  async getPubKey() {
    return CryptoUtils.Uint8ArrayToB64(Util.toBuffer(CryptoUtils.bytesToHexAddr(this._PubLicKey)))
  }

  /**
   * 전자서명값을 생성한다. (전자서명과 원문이 분리된 형식)
   * @param {string} msg 입력데이터   
   */
  async getSignVal(msg) {
    const Msg = Buffer.from(JSON.stringify(msg))
    const sign = CryptoUtils.Uint8ArrayToB64(Nacl.sign.detached(Msg, this._PrivateKey))
    return sign
  }

  /**
   * 전자서명을 생성한다. (전자서명과 원문이 분리된 형식)
   * @param {string} msg 입력데이터    
   */
  async signReceipt_d(msg) {
    const Msg = Buffer.from(JSON.stringify(msg))
    const sign = CryptoUtils.Uint8ArrayToB64(Nacl.sign.detached(Msg, this._PrivateKey))
    const pubKey = CryptoUtils.Uint8ArrayToB64(Util.toBuffer(CryptoUtils.bytesToHexAddr(this._PubLicKey)))
    return {
      sign,
      pubKey
    }
  }
 
  /**
   * 전자서명을 검사한다. (전자서명과 원문이 분리된 형식)
   * @param {string} msg 입력데이터
   * @param {string} signB64 전자서명값
   * @param {string} publicKeyB64 공개키값
   */
  async verifyReceipt_d(msg, signB64, publicKeyB64) {
    const Msg = Buffer.from(JSON.stringify(msg))
    const sign = CryptoUtils.B64ToUint8Array(signB64)
    const publicKey = CryptoUtils.B64ToUint8Array(publicKeyB64)
    const rest = Nacl.sign.detached.verify(Msg, sign, publicKey)
    return rest
  }
  //-------------------------------------------------------------------------------------------------------------------------------------//


  //------------------------------------------- identity -------------------------------------------//
  /**
   * smart contract owner를 지정한다. 최초 셋팅시 수행 
   * @param {string} ethAddress 사용자 주소
   */
  async enrollIssuer(ethAddress) {
    await workingStart();
    try {
      const EthAddress = new Address('eth', LocalAddress.fromHexString(ethAddress))
      var DappAddress
      if (await this._AddressMapper.hasMappingAsync(EthAddress)) {
        const mappingInfo = await this._AddressMapper.getMappingAsync(EthAddress)
        DappAddress = CryptoUtils.bytesToHexAddr(mappingInfo.to.local.bytes)
      } else {
        Logger.error("unmapped address")
        return
      }

      let transaction = await this._BIdentity.methods.enrollIssuer(DappAddress, ethAddress)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
      return transaction.events.NewIssuer.returnValues.issuer
    } finally {
      await workingEnd();
    }
  }

  async getAddressKey(address) {
    // console.log("address: " + address)
    var ecparams = ECurve.getCurveByName('secp256k1')
    var convert = ecparams.G.multiply(BI.fromBuffer(new Buffer(address.substring(2), 'hex')))
    return Buffer.concat([convert.affineX.toBuffer(32), convert.affineY.toBuffer(32)])
  }

  async getDataHash(addressKey, data) {
    return Util.keccak256(addressKey + JSON.stringify(data))
  }

  async requestAdd(addressKey, dataHash, privateKey) {
    await workingStart();
    try {
      var ecSign = Util.ecsign(dataHash, privateKey)
      var signature = Util.bufferToHex(ecSign.r) + Util.bufferToHex(ecSign.s).substr(2) + Util.bufferToHex(ecSign.v).substr(2)
      const transaction = await this._BIdentity.methods.requestAdd('0x' + addressKey.toString('hex'), '0x' + dataHash.toString('hex'), signature)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
      return transaction.events.RequestAdd.returnValues.requestKey
    } finally {
      await workingEnd();
    }
  }

  async approveAdd(dataHash, requestKey, isApprove) {
    await workingStart();
    try {
      const transaction = await this._BIdentity.methods.approveAdd('0x' + dataHash.toString('hex'), requestKey, isApprove)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("error occured: " + error)
        })
    } finally {
      await workingEnd();
    }
  }

  async getSignature(addressKey, dataHash) {
    await workingStart();
    try {
      return await this._BIdentity.methods.getSignature('0x' + addressKey.toString('hex'), '0x' + dataHash.toString('hex'))
        .call({
          from: this._Address
        })
    } finally {
      await workingEnd();
    }
  }
  //------------------------------------------------------------------------------------------------//

  //------------------------------------------- verifier -------------------------------------------//

  /**
   * 전송영수증을 Verifier에게 전송한다. 
   * @param {string} channel_id 채널ID
   * @param {string} receipt 수신자 ID 
   */
  async sendAggregatedReceipt(channel_id, receipt) {
    await workingStart();
    try {
      //전송영수증 수신자 공개키 얻기
      const channelInfo = await this._BChannel.methods.getChannelDetails(channel_id).call({
        from: this._Address
      })
      const r_pubKey = channelInfo[2]

      Logger.debug("receipt verify r_pubKey:" + r_pubKey)
      Logger.debug("receipt verify receipt:" + receipt)

      //전송영수증 전자서명검사
      const signData = CryptoUtils.B64ToUint8Array(receipt)
      const publicKey = CryptoUtils.B64ToUint8Array(r_pubKey)
      const msgBytes = Nacl.sign.open(signData, publicKey)

      if (msgBytes == null) {
        Logger.error("sign verify error.")
        return { resultCode: 500, resultMessage: "sign verify error.", state: 'fail' }
      }

      const orgReceipt = JSON.parse(Buffer.from(msgBytes.buffer, msgBytes.byteOffset, msgBytes.byteLength).toString())
      Logger.debug("sign verify ok:" + orgReceipt)

      //전송영수증 전자서명
      const msg = Buffer.from(receipt)
      const sign = CryptoUtils.Uint8ArrayToB64(Nacl.sign.detached(msg, this._PrivateKey))
      const public_key = CryptoUtils.Uint8ArrayToB64(Util.toBuffer(CryptoUtils.bytesToHexAddr(this._PubLicKey)))
      Logger.debug("sender sign:" + sign + ",public_key:" + public_key)

      const vInfo = await this.getConfigData()
      const verifier_url = vInfo[0] + ':' + vInfo[1] + '/receiveReceipt'
      // Logger.debug("verifier_url:" + verifier_url)
      // Logger.debug("orgReceipt.From:" + orgReceipt.From)
      // Logger.debug("orgReceipt.To:" + orgReceipt.To)
      // Logger.debug("orgReceipt.File:" + orgReceipt.File)

      const response = await axios({
        method: 'post',
        url: verifier_url,
        data: {
          channel_id,
          'from_id': orgReceipt.From,
          'to_id': orgReceipt.To,
          'file_id': orgReceipt.File,
          'chunks': orgReceipt.Chunks,
          'receipt': receipt,
          's_sign': sign,
          's_pubkey': public_key
        }
      })

      if (response.status == 200) {
        if (response.data.resultCode == 0) {
          Logger.debug("receipt send ok ")
          return { resultCode: 0, state: 'succeed' }
        } else {
          Logger.error("receipt send error =" + response.data.result)
          return { resultCode: 500, resultMessage: response.data.result, state: 'fail' }
        }
      } else {
        Logger.error("receipt send error, status=" + response.status + ", err=" + response.statusText)
        return { resultCode: 500, resultMessage: response.statusText, state: 'fail' }
      }
    } finally {
      await workingEnd();
    }
  }
  //------------------------------------------------------------------------------------------------//

  /**
   * 사용환경 정보 셋팅 
   * @param {string} inurl verifier url
   * @param {int} inport verifier port 
   * @param {int} inopenPeriod 채널 open 기간 
   * @param {int} incollection 전송영수증 취합 갯수
   * @param {int} chunkPrice 청크 당 전송비 가격 
   * @param {int} depositRatio 전송비 담보금 비율 
   * @param {int} timeoutMili time out 시간 
   */
  async setConfigData(inurl, inport, inopenPeriod, incollection, chunkPrice, depositRatio, timeoutMili) {
    await workingStart();
    try {
      var tx = await this._BChannel.methods.setConfig(chunkPrice, depositRatio, timeoutMili)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("setConfig error occured: " + error)
        })

      tx = await this._Bconfig.methods.setBconfig(inurl, inport, inopenPeriod, incollection)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("setBconfig error occured: " + error)
        })
    } finally {
      await workingEnd();
    }
  }

  /**
   * 사용환경변수를 가져온다. 
   */
  async getConfigData() {
    return this._Bconfig.methods.getBconfig().call({
      from: this._Address
    })
  }

  /**
   * 스토리지 노드/검색 노드에서 데이터 수신여부를 체크한다. 
   * @param {string} ccid 복합콘텐츠 CCID
   * @param {string} version 복합콘텐츠 버전 
   * @param {bool} sflag 스토리지 노드 수신 여부 
   * @param {bool} tflag 검색노드 수신여부 
   */
  async setReceiveData(ccid, version, sflag, tflag) {
    await workingStart();
    try {
      let tx = await this._BProduct.methods.setReceive(ccid, version, sflag, tflag)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("setReceiveData error occured: " + error)
        })
    } finally {
      await workingEnd();
    }
  }

  /**
   * 검색노드 수신여부 설정 
   * @param {string} ccid 복합콘텐츠 CCID
   * @param {string} version 복합콘텐츠 버전
   * @param {bool} flag 검색노드 수신여부 
   */
  async setSearchReceiveData(ccid, version, flag) {
    await workingStart();
    try {
      let tx = await this._BProduct.methods.setSearchReceive(ccid, version, flag)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("setSearchReceiveData error occured: " + error)
        })
    } finally {
      await workingEnd();
    }
  }

  /**
   * 스토리지 노드 수신여부 설정 
   * @param {*} ccid 복합콘텐츠 CCID
   * @param {*} version 복합콘텐츠 버전
   * @param {*} flag 스토리지 노드 수신여부 
   */
  async setStorageReceiveData(ccid, version, flag) {
    await workingStart();
    try {
      let tx = await this._BProduct.methods.setStorageReceive(ccid, version, flag)
        .send({
          from: this._Address
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("setStorageReceiveData error occured: " + error)
        })
    } finally {
      await workingEnd();
    }
  }

   /**
   * 검색/스토리지 노드 수신여부 확인
   * @param {*} ccid 복합콘텐츠 CCID
   * @param {*} version 복합콘텐츠 버전
   * @param {*} flag 스토리지 노드 수신여부 
   */
  async isReceiveData(ccid, version) {
    await workingStart();
    try {
      return this._BProduct.methods.isReceive(ccid, version).call({
        from: this._Address
      })
    } finally {
      await workingEnd();
    }
  }
  //------------------------------------------------------------------------------------------------//
}