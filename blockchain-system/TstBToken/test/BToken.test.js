const {
  assertEventVar,
  printEventVar,
  expectThrow,
} = require('./helpers')
const config = require('../config.json')
const {
  BN
} = web3.utils
const bnChai = require('bn-chai')
const web3Utils = require('web3-utils')
const ethUtil = require('ethereumjs-util')
var Log4JS = require('log4js')
var Logger = Log4JS.getLogger('test_bToken')
Logger.level = config.logging_level

require('chai')
  .use(require('chai-as-promised'))
  .use(bnChai(BN))
  .should()

const BMSP = artifacts.require('./BMSP.sol')
const BToken = artifacts.require('./BToken.sol')
// const BToken2 = artifacts.require('./BToken2.sol')
const BChannel = artifacts.require('./BChannel.sol')

const Role = {
  P: 1,
  CP: 2,
  SP: 4,
  D: 8
}

const TokenState = {
  invalid: 0,
  valid: 1,
  in_progress: 2
}

const ChannelState = {
  invalid: 0,
  open: 1,
  settle: 2
}

const errorMessage = 'VM Exception while processing transaction'

Number.prototype.pad = function(size) {
  var s = String(this)
  while (s.length < (size || 2)) {
    s = "0" + s;
  }
  return s
}

async function assertWithError(promise) {
  try {
    await promise
  } catch (error) {
    Logger.debug(error.message)
    assert.include(error.message, errorMessage)
    return
  }
  assert.fail('Expected error not received');
}

function getId(tx) {
  let evt = tx.logs.find(log => log.event === 'NewID')
  return evt.args['Id']
} 

contract('BToken', accounts => {
  var Msp
  var Token
  var Channel
  const [alice, bob, charlie, dave, eve, me1, me2, me3, me4, me5, me6, me7, me8, me9] = accounts
  const priceByChunks = 100000000000
  const timeout = 86400
  const fileHashes = "QmTFCgF96KfBYQAMTWX823jcVktKcTngD73YiLGt9AYoYcQmTFCgF96KfBYQAMTWX823jcVktKcTngD73YiLGt9AYoYdQmTFCgF96KfBYQAMTWX823jcVktKcTngD73YiLGt9AYoYeQmTFCgF96KfBYQAMTWX823jcVktKcTngD73YiLGt9AYoYf"
  const fileChunks = [1000,2000,3000,4000]
  const totfileChunks = 10000

  describe("msp", () => {
    before(async () => {
      Msp = await BMSP.new({from: alice})
      Token = await BToken.new({from: alice}) 
      Channel = await BChannel.new({from: alice})
      await Msp.setOnlyContract(Token.address, {from: alice})
      await Msp.setOnlyContract(Channel.address, {from: alice})
      await Msp.setOutsideContracts(Token.address, {from: alice})
      await Token.setOnlyContracts(Msp.address, Channel.address, {from: alice})
      await Token.setBMSPCon(Msp.address, {from: alice})
      await Channel.setConfig(100000000000, 10, 10000, {from: alice})
      await Channel.setOutsideContracts(Msp.address, Token.address, {from: alice})
      await Msp.appointManager(alice, {from: alice})
    })

    it('request & approve role: normal', async () => {
      await Msp.requestEnroll(Role.P, {from: bob})
      await Msp.requestEnroll(Role.SP, {from: dave})
      await Msp.requestEnroll(Role.D, {from: eve})

      const nextIndex = await Msp.getNextIndex.call({from: alice})
      const requestLength = await Msp.getRequestLength.call({from: alice})
      for(var i = nextIndex; i < requestLength; i++) {
        const details = await Msp.getRequestDetails.call(i, {from: alice})
        Logger.debug("details[" + i + "]: " + JSON.stringify(details))
      }

      await Msp.approveRole([true, false, false], {from: alice})
      const verifyP = await Msp.verifyRole.call(bob, Role.P, false)
      const verifyCP = await Msp.verifyRole.call(charlie, Role.CP, true)
      const verifySP = await Msp.verifyRole.call(dave, Role.SP, false)
      const verifyD = await Msp.verifyRole.call(eve, Role.D, false)

      assert.isTrue(verifyP)
      assert.isFalse(verifyCP)
      assert.isFalse(verifySP)
      assert.isFalse(verifyD)
    })

    it('request & approve role: request multiple roles', async () => {
      await Msp.requestEnroll(Role.P | Role.SP, {from: bob})

      const nextIndex = await Msp.getNextIndex.call({from: alice})
      const requestLength = await Msp.getRequestLength.call({from: alice})
      for(var i = nextIndex; i < requestLength; i++) {
        const details = await Msp.getRequestDetails.call(i, {from: alice})
        Logger.debug("details[" + i + "]: " + JSON.stringify(details))
      }

      await Msp.approveRole([true], {from: alice})
      let verify = await Msp.verifyRole.call(bob, Role.P | Role.SP, false)
      assert.isTrue(verify)
      verify = await Msp.verifyRole.call(bob, Role.P | Role.SP | Role.D, false)
      assert.isFalse(verify)
      verify = await Msp.verifyRole.call(bob, Role.P, false)
      assert.isTrue(verify)
      verify = await Msp.verifyRole.call(bob, Role.CP, true)
      assert.isFalse(verify)
      verify = await Msp.verifyRole.call(bob, Role.SP, false)
      assert.isTrue(verify)
      verify = await Msp.verifyRole.call(bob, Role.D, false)
      assert.isFalse(verify)
    })

    it('request & approve role: approve by NO contract owner (should be failed)', async () => {
      await Msp.requestEnroll(Role.P, {from: bob})
      const nextIndex = await Msp.getNextIndex.call({from: alice})
      const requestLength = await Msp.getRequestLength.call({from: alice})
      for(var i = nextIndex; i < requestLength; i++) {
        const details = await Msp.getRequestDetails.call(i, {from: alice})
        Logger.debug("details[" + i + "]: " + JSON.stringify(details))
      }
      await assertWithError(Msp.approveRole([true], {from: bob}))
      await Msp.approveRole([true], {from: alice})
    })

    it('revoke role: normal', async () => {
      await Msp.requestEnroll(Role.P | Role.SP | Role.D, {from: bob})
      const nextIndex = await Msp.getNextIndex.call({from: alice})
      const requestLength = await Msp.getRequestLength.call({from: alice})
      for(var i = nextIndex; i < requestLength; i++) {
        const details = await Msp.getRequestDetails.call(i, {from: alice})
        Logger.debug("details[" + i + "]: " + JSON.stringify(details))
      }
      await Msp.approveRole([true], {from: alice})

      let verify = await Msp.verifyRole.call(bob, Role.P | Role.SP | Role.D, false)
      assert.isTrue(verify)
      let tx = await Msp.revokeUser(bob, Role.P, false, false, {from: alice})
      verify = await Msp.verifyRole.call(bob, Role.P | Role.SP | Role.D, false)
      assert.isFalse(verify)
      verify = await Msp.verifyRole.call(bob, Role.P, false)
      assert.isFalse(verify)
      verify = await Msp.verifyRole.call(bob, Role.SP | Role.D, false)
      assert.isTrue(verify)
    })

    it('revoke role: by NO contract owner (should be failed)', async () => {
      await Msp.requestEnroll(Role.P | Role.SP | Role.D, {from: bob})
      const nextIndex = await Msp.getNextIndex.call({from: alice})
      const requestLength = await Msp.getRequestLength.call({from: alice})
      for(var i = nextIndex; i < requestLength; i++) {
        const details = await Msp.getRequestDetails.call(i, {from: alice})
        Logger.debug("details[" + i + "]: " + JSON.stringify(details))
      }
      await Msp.approveRole([true], {from: alice})
      let verify = await Msp.verifyRole.call(bob, Role.P | Role.SP | Role.D, false)
      assert.isTrue(verify)
      await assertWithError(Msp.revokeUser(bob, Role.P, false, false, {from: bob}))
    })
  })

  describe("register data", () => {
    beforeEach(async () => {
      Msp = await BMSP.new({from: alice})
      Token = await BToken.new({from: alice}) 
      // Token2 = await BToken2.new({from: alice}) 
      
      Channel = await BChannel.new({from: alice})
      await Msp.setOnlyContract(Token.address, {from: alice})
      await Msp.setOnlyContract(Channel.address, {from: alice})
      await Msp.setOutsideContracts(Token.address, {from: alice})
      await Token.setOnlyContracts(Msp.address, Channel.address, {from: alice})
      await Token.setBMSPCon(Msp.address, {from: alice})
      await Channel.setConfig(100000000000, 10, 10000, {from: alice})
      //  await Channel.setConfig(1, 10, 10000, {from: alice})
      await Channel.setOutsideContracts(Msp.address, Token.address, {from: alice})
      await Msp.appointManager(alice, {from: alice})

      await Msp.requestEnroll(Role.P, {from: bob})
      await Msp.requestEnroll(Role.D, {from: dave})
      await Msp.approveRole([true, true], {from: alice})
      const verifyP = await Msp.verifyRole.call(bob, Role.P, false)
      const verifyCP = await Msp.verifyRole.call(charlie, Role.CP, true)
      const verifyD = await Msp.verifyRole.call(dave, Role.D, false)
      assert.isTrue(verifyP)
      assert.isFalse(verifyCP)
      assert.isTrue(verifyD)
    })

    it('register data & attribute: normal', async () => {
      let tx =  await Token.getCID(charlie, {from: bob})
      let cid = getId(tx)
      Logger.debug("cid : " + cid)
       tx =  await Token.getCID(charlie, {from: bob})
       cid = getId(tx)
      Logger.debug("cid : " + cid)

      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      tx = await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0], true, {from: charlie})
      let dataId = getId(tx)
      Logger.debug("data id: " + dataId)
      dataDetails = await Token.getDataDetails.call(dataId)
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
      assert.isTrue(dataDetails[6]) // validity
      Logger.debug("data info: " + JSON.stringify(dataDetails))

      tx = await  Token.registerDataAttr("ccid", "version", "dataInfo2", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0], false, {from: charlie});
      dataId = getId(tx)
      Logger.debug("modify data id: " + dataId)
      dataDetails = await Token.getDataDetails.call(dataId)
       dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
      assert.isTrue(dataDetails[6]) // validity
      Logger.debug("data info: " + JSON.stringify(dataDetails))

    }) 

    it('register data & attribute: register data twice with same cid (should be failed)', async () => {
      let tx =  await Token.getCID(charlie, {from: bob})
      let cid = getId(tx)
      Logger.debug("cid : " + cid)

      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      await assertWithError(Token.registerData(cid, "_ccid", "_version", 5000, fileHashes, fileChunks, {from: charlie}))
    })

    it('register data & attribute: register data twice with same ccid and version (should be failed)', async () => {
      let tx =  await Token.getCID(charlie, {from: bob})
      let cid1 = getId(tx)
      tx =  await Token.getCID(charlie, {from: bob})
      cid2 = getId(tx)
      Logger.debug("cid1: " + cid1)
      Logger.debug("cid2: " + cid2)

      await Token.registerData(cid1, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      await assertWithError(Token.registerData(cid2, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie}))
    })

    it('register data & attribute: get cid with msg.sender != packager (should be failed)', async () => {
      await assertWithError(Token.getCID(charlie, {from: alice}))
    })

    it('register data & attribute: get cid with revoked contents provider (should be failed)', async () => {
      await Msp.revokeUser(charlie, Role.CP, false, false, {from: alice})
      await assertWithError(Token.getCID(charlie, {from: bob}))
    })

    it('register data & attribute: by NO cid owner (should be failed)', async () => {
      let tx =  await Token.getCID(charlie, {from: bob})
      let cid = getId(tx)
      Logger.debug("cid : " + cid)
      const verify = await Msp.verifyRole.call(eve, Role.CP, true)
      assert.isFalse(verify)
      await assertWithError(Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: eve}))
    })

    it('register data & attribute: user who called registerData and the user who called registerDataAttr are different (should be failed)', async () => {
      let tx =  await Token.getCID(charlie, {from: bob})
      let cid = getId(tx)
      Logger.debug("cid : " + cid)
      await assertWithError(Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0], true, {from: bob}))
    })

    it('register data & attribute: call registerDataAttr by revoked contents provider (should be failed)', async () => {
      let tx =  await Token.getCID(charlie, {from: bob})
      let cid = getId(tx)
      Logger.debug("cid : " + cid)

      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      await Msp.revokeUser(charlie, Role.CP, false, false, {from: alice})
      await assertWithError(Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0], true, {from: charlie}))
    })
  })
  return;
  describe("register product", () => {
    before(async () => {
      Msp = await BMSP.new({from: alice})
      Token = await BToken.new({from: alice})
      Channel = await BChannel.new({from: alice})
      await Msp.setOnlyContract(Token.address, {from: alice})
      await Msp.setOnlyContract(Channel.address, {from: alice})
      await Msp.setOutsideContracts(Token.address, {from: alice})
      await Token.setOnlyContracts(Msp.address, Channel.address, {from: alice})
      await Token.setBMSPCon(Msp.address, {from: alice})
      await Channel.setConfig(100000000000, 10, 10000, {from: alice})
      await Channel.setOutsideContracts(Msp.address, Token.address, {from: alice})
      await Msp.appointManager(alice, {from: alice})

      await Msp.requestEnroll(Role.P, {from: bob})
      await Msp.requestEnroll(Role.D, {from: dave})
      await Msp.approveRole([true, true], {from: alice})
      const verifyP = await Msp.verifyRole.call(bob, Role.P, false)
      const verifyCP = await Msp.verifyRole.call(charlie, Role.CP, true)
      const verifyD = await Msp.verifyRole.call(dave, Role.D, false)
      assert.isTrue(verifyP)
      assert.isFalse(verifyCP)
      assert.isTrue(verifyD)

      let tx =  await Token.getCID(charlie, {from: bob})
      cid = getId(tx)
      Logger.debug("cid : " + cid)

      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      tx = await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} )
      dataId = getId(tx)
      Logger.debug("data id: " + dataId)
      dataDetails = await Token.getDataDetails.call(dataId)
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
    })

    it('register product: with distributor', async () => {
      let tx = await Token.registerProduct("ccid", "version", 50000, {from: dave})
      let productId = getId(tx)
      Logger.debug("product id: " + productId)
    })

    it('register product: by NO distributor (should be failed)', async () => {
      await assertWithError(Token.registerProduct("ccid", "version", 50000, {from: alice}))
    })

    it('register product: with invalid ccid & version (should be failed)', async () => {
      await assertWithError(Token.registerProduct("invalid_ccid", "invalid_version", 50000, {from: dave}))
    })

    it('register product: with lower price than data fee (should be failed)', async () => {
      await assertWithError(Token.registerProduct("ccid", "version", 4999, {from: dave}))
    })

    it('register product: with designated and NOT designated distributor', async () => {
      await Token.registerDataAttr("ccid", "version", "dataInfo", [dave], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} )
      let tx = await Token.registerProduct("ccid", "version", 50000, {from: dave})
      let productId = getId(tx)
      Logger.debug("product id: " + productId)
      await Token.registerDataAttr("ccid", "version", "dataInfo", [bob], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} )
      await assertWithError(Token.registerProduct("ccid", "version", 50000, {from: dave}))
    })

    it('register product: target user of a product is fixed after the product is registered ', async () => {
      await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], [alice], [1,0,0,0,0],  true, {from: charlie} )
      let tx = await Token.registerProduct("ccid", "version", 50000, {from: dave})
      let productId1 = getId(tx)
      Logger.debug("product id: " + productId1)
      let productInfo = await Token.getProductDetails.call(productId1)
      Logger.debug("product info: " + JSON.stringify(productInfo))
      assert.equal(JSON.stringify(productInfo[3]), JSON.stringify([alice]))
      await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], [bob], [1,0,0,0,0],  true, {from: charlie} )
      tx = await Token.registerProduct("ccid", "version", 50000, {from: dave})
      let productId2 = getId(tx)
      Logger.debug("product id: " + productId2)
      productInfo = await Token.getProductDetails.call(productId2)
      Logger.debug("product info: " + JSON.stringify(productInfo))
      assert.equal(JSON.stringify(productInfo[3]), JSON.stringify([bob]))
      productInfo = await Token.getProductDetails.call(productId1)
      Logger.debug("product info: " + JSON.stringify(productInfo))
      assert.equal(JSON.stringify(productInfo[3]), JSON.stringify([alice]))
    })

    it('register product: with revoked distributor (should be failed)', async () => {
      await Msp.revokeUser(dave, Role.D, false, false, {from: alice})
      var verifyD = await Msp.verifyRole.call(dave, Role.D, false)
      assert.isFalse(verifyD)
      await assertWithError(Token.registerProduct("ccid", "version", 50000, {from: dave}))
    })
  })

  describe("buy product", () => {
    var productId
    before(async () => {
      Msp = await BMSP.new({from: alice})
      Token = await BToken.new({from: alice})
      Channel = await BChannel.new({from: alice})
      await Msp.setOnlyContract(Token.address, {from: alice})
      await Msp.setOnlyContract(Channel.address, {from: alice})
      await Msp.setOutsideContracts(Token.address, {from: alice})
      await Token.setOnlyContracts(Msp.address, Channel.address, {from: alice})
      await Token.setBMSPCon(Msp.address, {from: alice})
      await Channel.setConfig(100000000000, 10, 10000, {from: alice})
      await Channel.setOutsideContracts(Msp.address, Token.address, {from: alice})
      await Msp.appointManager(alice, {from: alice})

      await Msp.requestEnroll(Role.P, {from: bob})
      await Msp.requestEnroll(Role.D, {from: dave})
      await Msp.approveRole([true, true], {from: alice})
      const verifyP = await Msp.verifyRole.call(bob, Role.P, false)
      const verifyCP = await Msp.verifyRole.call(charlie, Role.CP, true)
      const verifyD = await Msp.verifyRole.call(dave, Role.D, false)
      assert.isTrue(verifyP)
      assert.isFalse(verifyCP)
      assert.isTrue(verifyD)

      let tx =  await Token.getCID(charlie, {from: bob})
      cid = getId(tx)
      Logger.debug("cid : " + cid)

      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      tx = await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} )
      dataId = getId(tx)
      Logger.debug("data id: " + dataId)
      dataDetails = await Token.getDataDetails.call(dataId)
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))

      tx = await Token.registerProduct("ccid", "version", 50000, {from: dave})
      productId = getId(tx)
      Logger.debug("product id: " + productId)
    })

    it('buy product: normal', async () => {
      let productInfo = await Token.getProductDetails.call(productId)
      let tx = await Token.buyProduct(productId, {
        from: alice,
        value: productInfo[2]
      })
      let tokenId = getId(tx)
      Logger.debug("token id: " + tokenId)

      let tokenInfo = await Token.getTokenDetails.call(tokenId, {from: alice})
      Logger.debug(JSON.stringify(tokenInfo))
      assert.equal(tokenInfo[0], alice)
      assert.equal(parseInt(tokenInfo[1]), parseInt(productId))
      assert.equal(tokenInfo[2], TokenState.valid)
    })

    it('buy product: with invalid product id (should be failed)', async () => {
      let productInfo = await Token.getProductDetails.call(productId)
      await assertWithError(Token.buyProduct(0, {from: alice, value: productInfo[2]}))
    })

    it('buy product: with insufficient msg.value (should be failed)', async () => {
      let productInfo = await Token.getProductDetails.call(productId)
      await assertWithError(Token.buyProduct(productId, {from: alice, value: parseInt(productInfo[2]) - 500}))
    })

    it('buy product: with designated and NOT designated distributor', async () => {
      await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], [alice], [1,0,0,0,0],  true, {from: charlie} )
      dataDetails = await Token.getDataDetails.call(dataId)
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))

      tx = await Token.registerProduct("ccid", "version", 50000, {from: dave})
      productId = getId(tx)
      Logger.debug("product id: " + productId)

      let productInfo = await Token.getProductDetails.call(productId)
      await Token.buyProduct(productId, {from: alice, value: parseInt(productInfo[2])})
      await assertWithError(Token.buyProduct(productId, {from: bob, value: parseInt(productInfo[2])}))
    })
  })

  describe("open channel", () => {
    var productId
    var tokenId
    const key = "0x0000000000000000000000000000000000000000"
    before(async () => {
      Msp = await BMSP.new({from: alice})
      Token = await BToken.new({from: alice})
      Channel = await BChannel.new({from: alice})
      await Msp.setOnlyContract(Token.address, {from: alice})
      await Msp.setOnlyContract(Channel.address, {from: alice})
      await Msp.setOutsideContracts(Token.address, {from: alice})
      await Token.setOnlyContracts(Msp.address, Channel.address, {from: alice})
      await Token.setBMSPCon(Msp.address, {from: alice})
      await Channel.setConfig(100000000000, 10, 10000, {from: alice})
      await Channel.setOutsideContracts(Msp.address, Token.address, {from: alice})
      await Msp.appointManager(alice, {from: alice})

      await Msp.requestEnroll(Role.P, {from: bob})
      await Msp.requestEnroll(Role.D, {from: dave})
      await Msp.requestEnroll(Role.SP, {from: eve})
      await Msp.approveRole([true, true, true], {from: alice})
      const verifyP = await Msp.verifyRole.call(bob, Role.P, false)
      const verifyCP = await Msp.verifyRole.call(charlie, Role.CP, true)
      const verifyCP2 = await Msp.verifyRole.call(eve, Role.SP, false)
      const verifyD = await Msp.verifyRole.call(dave, Role.D, false)
      assert.isTrue(verifyP)
      assert.isFalse(verifyCP)
      assert.isTrue(verifyD)
      assert.isTrue(verifyCP2)

      let tx =  await Token.getCID(charlie, {from: bob})
      cid = getId(tx)
      Logger.debug("cid : " + cid)

      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      tx = await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} )
      dataId = getId(tx)
      Logger.debug("data id: " + dataId)
      dataDetails = await Token.getDataDetails.call(dataId)
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))

      tx = await Token.registerProduct("ccid", "version", 50000, {from: dave})
      productId = getId(tx)
      Logger.debug("product id: " + productId)

      let productInfo = await Token.getProductDetails.call(productId)
      tx = await Token.buyProduct(productId, {
        from: alice,
        value: productInfo[2]
      })
      tokenId = getId(tx)
      Logger.debug("token id: " + tokenId)

      let tokenInfo = await Token.getTokenDetails.call(tokenId, {from: alice})
      Logger.debug(JSON.stringify(tokenInfo))

      let depositInfo = await Channel.getDepositNCollateral.call(tokenId, {from: alice})
      Logger.debug("deposit info: " + JSON.stringify(depositInfo))
      deposit = parseInt(depositInfo[0]) + parseInt(depositInfo[1])
      Logger.debug("total deposit: " + deposit)
    })

    it('open channel: by NO token owner (should be failed)', async () => {
      await assertWithError(Channel.channelOpen(tokenId, key, totfileChunks, {from: bob, value: deposit}))
      let tokenInfo = await Token.getTokenDetails.call(tokenId)
      Logger.debug("tokenInfo: " + JSON.stringify(tokenInfo))
      assert.equal(tokenInfo[2], TokenState.valid)
    })

    it('open channel: with invalid token (should be failed)', async () => {
      await assertWithError(Channel.channelOpen(0, key, totfileChunks, {from: alice, value: deposit}) )
      let tokenInfo = await Token.getTokenDetails.call(tokenId)
      Logger.debug("tokenInfo: " + JSON.stringify(tokenInfo))
      assert.equal(tokenInfo[2], TokenState.valid)
    })

    it('open channel: with insufficient deposit (should be failed)', async () => {
      await assertWithError(Channel.channelOpen(tokenId, key, totfileChunks, {from: alice, value: parseInt(deposit) - 500}))
      let tokenInfo = await Token.getTokenDetails.call(tokenId)
      Logger.debug("tokenInfo: " + JSON.stringify(tokenInfo))
      assert.equal(tokenInfo[2], TokenState.valid)
    })

    it('open channel: by NO token owner but SP (should be succeed)', async () => {
      let tx = await Channel.channelOpen(tokenId, key, totfileChunks, {from: eve, value: deposit})
      let channelId = getId(tx)
      Logger.debug("channel id: " + channelId)

      let channelInfo = await Channel.getChannelDetails.call(channelId)
      Logger.debug("channelInfo: " + JSON.stringify(channelInfo))
      // assert.equal(parseInt(channelInfo[1]), parseInt(tokenId))
      assert.equal(parseInt(channelInfo[2]), key)
      // assert.equal(parseInt(channelInfo[3]) + parseInt(channelInfo[4]), deposit)
      assert.equal(channelInfo[7], ChannelState.open)

      // let tokenInfo = await Token.getTokenDetails.call(tokenId)
      // Logger.debug("tokenInfo: " + JSON.stringify(tokenInfo))
      // assert.equal(tokenInfo[2], TokenState.in_progress)
    })

    it('open channel: normal', async () => {
      let tx = await Channel.channelOpen(tokenId, key, totfileChunks, {
        from: alice,
        value: deposit
      })
      let channelId = getId(tx)
      Logger.debug("channel id: " + channelId)
      console.log("channel id: " + channelId)

      let channelInfo = await Channel.getChannelDetails.call(channelId)
      Logger.debug("channelInfo: " + JSON.stringify(channelInfo))
      assert.equal(channelInfo[0], alice)
      assert.equal(parseInt(channelInfo[1]), parseInt(tokenId))
      assert.equal(parseInt(channelInfo[2]), key)
      assert.equal(parseInt(channelInfo[3]) + parseInt(channelInfo[4]), deposit)
      assert.equal(channelInfo[7], ChannelState.open)

      let tokenInfo = await Token.getTokenDetails.call(tokenId)
      Logger.debug("tokenInfo: " + JSON.stringify(tokenInfo))
      assert.equal(tokenInfo[2], TokenState.in_progress)
    })


  })


  describe("settle channel", () => {
    var productId
    var tokenId
    const key = "0x0000000000000000000000000000000000000000"
    var channelId
    var chunks = [1000, 2000, 3000]
    before(async () => {
      Msp = await BMSP.new({from: alice})
      Token = await BToken.new({from: alice})
      Channel = await BChannel.new({from: alice})
      await Msp.setOnlyContract(Token.address, {from: alice})
      await Msp.setOnlyContract(Channel.address, {from: alice})
      await Msp.setOutsideContracts(Token.address, {from: alice})
      await Token.setOnlyContracts(Msp.address, Channel.address, {from: alice})
      await Token.setBMSPCon(Msp.address, {from: alice})
      await Channel.setConfig(100000000000, 10, 10000, {from: alice})
      await Channel.setOutsideContracts(Msp.address, Token.address, {from: alice})
      await Msp.appointManager(alice, {from: alice})

      await Msp.requestEnroll(Role.P, {from: bob})
      await Msp.requestEnroll(Role.D, {from: dave})
      await Msp.approveRole([true, true], {from: alice})
      const verifyP = await Msp.verifyRole.call(bob, Role.P, false)
      const verifyCP = await Msp.verifyRole.call(charlie, Role.CP, true)
      const verifyD = await Msp.verifyRole.call(dave, Role.D, false)
      assert.isTrue(verifyP)
      assert.isFalse(verifyCP)
      assert.isTrue(verifyD)

      let tx =  await Token.getCID(charlie, {from: bob})
      cid = getId(tx)
      Logger.debug("cid : " + cid)

      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      tx = await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} )
      dataId = getId(tx)
      Logger.debug("data id: " + dataId)
      dataDetails = await Token.getDataDetails.call(dataId)
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))

      tx = await Token.registerProduct("ccid", "version", 50000, {from: dave})
      productId = getId(tx)
      Logger.debug("product id: " + productId)

      let productInfo = await Token.getProductDetails.call(productId)
      tx = await Token.buyProduct(productId, {
        from: alice,
        value: productInfo[2]
      })
      tokenId = getId(tx)
      Logger.debug("token id: " + tokenId)

      let tokenInfo = await Token.getTokenDetails.call(tokenId, {from: alice})
      Logger.debug(JSON.stringify(tokenInfo))

      let depositInfo = await Channel.getDepositNCollateral.call(tokenId, {from: alice})
      Logger.debug("deposit info: " + JSON.stringify(depositInfo))
      deposit = parseInt(depositInfo[0]) + parseInt(depositInfo[1])
      Logger.debug("total deposit: " + deposit)

      tx = await Channel.channelOpen(tokenId, key, totfileChunks, {
        from: alice,
        value: deposit
      })
      channelId = getId(tx)
      Logger.debug("channel id: " + channelId)

      let channelInfo = await Channel.getChannelDetails.call(channelId)
      Logger.debug("channelInfo: " + JSON.stringify(channelInfo))
    })
    it('settle channel: by NO manager (should be failed)', async () => {
      await assertWithError(Channel.settleChannel("merkle root", channelId, [bob, charlie, dave], chunks, {from: bob}))
    })

    it('settle channel: with invalid information (senders.length != chunks.length) (should be failed)', async () => {
      await assertWithError(Channel.settleChannel("merkle root", channelId, [bob, charlie], chunks, {from: alice}))
    })

    it('settle channel: normal', async () => {
      let channelInfo = await Channel.getChannelDetails.call(channelId)
      assert.equal(channelInfo[7], ChannelState.open)
      const prevBobBalance = parseInt(await web3.eth.getBalance(bob))
      const prevCharlieBalance = parseInt(await web3.eth.getBalance(charlie))
      const prevDaveBalance = parseInt(await web3.eth.getBalance(dave))

      await Channel.settleChannel("merkle root", channelId, [bob, charlie, dave], chunks)
      // await Channel.settleChannel( channelId, [bob, charlie, dave], chunks)
      const bobBalanceDifference = parseInt(await web3.eth.getBalance(bob)) - prevBobBalance
      const charlieBalanceDifference = parseInt(await web3.eth.getBalance(charlie)) - prevCharlieBalance
      const daveBalanceDifference = parseInt(await web3.eth.getBalance(dave)) - prevDaveBalance
      Logger.debug("bobBalanceDifference: " + bobBalanceDifference)
      assert.equal(bobBalanceDifference, chunks[0] * priceByChunks)
      Logger.debug("charlieBalanceDifference: " + charlieBalanceDifference)
      assert.equal(charlieBalanceDifference, chunks[1] * priceByChunks)
      Logger.debug("daveBalanceDifference: " + daveBalanceDifference)
      assert.equal(daveBalanceDifference, chunks[2] * priceByChunks)

      channelInfo = await Channel.getChannelDetails.call(channelId)
      Logger.debug("channel info: " + JSON.stringify(channelInfo))
      assert.equal(channelInfo[7], ChannelState.settle)
      let tokenInfo = await Token.getTokenDetails.call(tokenId)
      Logger.debug("token info: " + JSON.stringify(tokenInfo))
      assert.equal(tokenInfo[2], TokenState.valid)
    })
  })

// /  const [alice, bob, charlie, dave, eve, me1, me2, me3, me4, me5, me6, me7, me8, me9] = accounts

  describe("settle all channel", () => {
    var productId
    var tokenId
    const key = "0x0000000000000000000000000000000000000000"
    var channelId
    var totchunk = 1279
    var chidAry = new Array();

    before(async () => {
      Msp = await BMSP.new({from: alice})
      Token = await BToken.new({from: alice})
      Channel = await BChannel.new({from: alice})
      await Msp.setOnlyContract(Token.address, {from: alice})
      await Msp.setOnlyContract(Channel.address, {from: alice})
      await Msp.setOutsideContracts(Token.address, {from: alice})
      await Token.setOnlyContracts(Msp.address, Channel.address, {from: alice})
      await Token.setBMSPCon(Msp.address, {from: alice})
      await Channel.setConfig(100, 10, 10000, {from: alice})
      await Channel.setOutsideContracts(Msp.address, Token.address, {from: alice})
      await Msp.appointManager(alice, {from: alice})

      await Msp.requestEnroll(Role.P, {from: bob})
      await Msp.requestEnroll(Role.D, {from: dave})
      await Msp.approveRole([true, true], {from: alice})
      const verifyP = await Msp.verifyRole.call(bob, Role.P, false)
      const verifyCP = await Msp.verifyRole.call(charlie, Role.CP, true)
      const verifyD = await Msp.verifyRole.call(dave, Role.D, false)
      assert.isTrue(verifyP)
      assert.isFalse(verifyCP)
      assert.isTrue(verifyD)

      let tx =  await Token.getCID(charlie, {from: bob})
      cid = getId(tx)
      Logger.debug("cid : " + cid)

      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      tx = await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0], {from: charlie},true)
      dataId = getId(tx)
      Logger.debug("data id: " + dataId)
      dataDetails = await Token.getDataDetails.call(dataId)
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))

      tx = await Token.registerProduct("ccid", "version", 50000, {from: dave})
      productId = getId(tx)
      Logger.debug("product id: " + productId)


      let productInfo = await Token.getProductDetails.call(productId)
      tx = await Token.buyProduct(productId, {
        from: alice,
        value: productInfo[2]
      })
      tokenId = getId(tx)
      Logger.debug("token id: " + tokenId)

      let tokenInfo = await Token.getTokenDetails.call(tokenId, {from: alice})
      Logger.debug(JSON.stringify(tokenInfo))


      let depositInfo = await Channel.getDepositNCollateral.call(tokenId, {from: alice})
      Logger.debug("deposit info: " + JSON.stringify(depositInfo))
      deposit = parseInt(depositInfo[0]) + parseInt(depositInfo[1])
      Logger.debug("total deposit: " + deposit)
     
      tx = await Channel.channelOpen(tokenId, key, totchunk, {
        from: alice,
        value: deposit
      })    
      channelId = getId(tx)
      Logger.debug("channel id: " + channelId)

      let channelInfo = await Channel.getChannelDetails.call(channelId)
      Logger.debug("channelInfo: " + JSON.stringify(channelInfo))

      chidAry.push( channelId ); 

      for( var k=0 ; k<9 ; k++ ) {
        var tx1 = await Channel.channelOpen(tokenId, key, totchunk, {
          from: alice,
          value: deposit
        })      
        chidAry.push(  getId(tx1) );  
        Logger.debug("channel id_step1: " + getId(tx1))
      }

      Logger.debug("channel id: " + chidAry[0])
      Logger.debug("channel id: " + chidAry[1])
      
      let channelInfo1 = await Channel.getChannelDetails.call(chidAry[0])
      Logger.debug("channelInfo: " + JSON.stringify(channelInfo1) )

    })

    var totcnt = 100
    var snds = [me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5, me1, me2, me3, me4, me5]
    var cnts   = [1, 1, 3, 1, 4, 10, 20, 20, 10, 30]
    var chunks = [1,2,3,4,5,6,7,8,9,10,9,40,34,3,2,3,4,2,38,3,9,40,34,3,2,3,4,2,38,3,9,40,34,3,2,3,4,2,38,3,9,40,34,3,2,3,4,2,38,3,9,40,34,3,2,3,4,2,38,3,9,40,34,3,2,3,4,2,38,3,9,40,34,3,2,3,4,2,38,3,9,40,34,3,2,3,4,2,38,3,9,40,34,3,2,3,4,2,38,3]
    var  merkleRoots = "0066fc6e26f04fcb8f204aa873c596d8cd3eceae69ba2bb2591b73446a1ad87207bff64747790db1f32c94c961fabdb10ee57f76556327a02a81ef0e3f29201808107d73e62861593bb530636d3a193348ef8a50c23c765ac88aceae2d309a170869de431c89e11ff452884d3b0b77a7592151629e8c617a21fd7b9ad924fe2008ad2ece01e083122c3f9f5fb68ab41d83ca4ae380df2d0db39c003e8999627f091bdde4dbb6f8350216083c0aacf988f08a6aceba258216f533440f647ee9880990d93224d4102f5371e9cac53c44bcaeaf6148911413391703b1c68335099f0a4fb25ef3b9b01760ba73a9b3b6f796668eeecdadd937cab7dbae838d440e190a890f98c4f343caccdee810a2bcbba6963256820b9b760e37d0a37f2725016b0a97729c262f6969a3b5cd467f7a685fc189089e3561db1d7fda59396d9d3fab"
 

    // var totcnt = 10
    // var snds = [me1, me2, me3, me1, me2, me3, me1, me2, me3, me3]
    // var cnts   = [5,3, 2]
    // var chunks = [10,20,30, 10,20,30, 30, 10,20,30]
    // var  merkleRoots = "0066fc6e26f04fcb8f204aa873c596d8cd3eceae69ba2bb2591b73446a1ad8720066fc6e26f04fcb8f204aa873c596d8cd3eceae69ba2bb2591b73446a1ad8720066fc6e26f04fcb8f204aa873c596d8cd3eceae69ba2bb2591b73446a1ad872"
 

    it('settle channel: normal', async () => {

      //me1, me2, me3, me4, me5, me6, me7, me8, me9
      Logger.debug(  "alice Balance: " + parseInt(await web3.eth.getBalance(alice)))
      Logger.debug(  "me1 Balance: " + parseInt(await web3.eth.getBalance(me1)))
      Logger.debug(  "me2 Balance: " + parseInt(await web3.eth.getBalance(me2)))
      Logger.debug(  "me3 Balance: " + parseInt(await web3.eth.getBalance(me3)))
      Logger.debug(  "me4 Balance: " + parseInt(await web3.eth.getBalance(me4)))
      Logger.debug(  "me5 Balance: " + parseInt(await web3.eth.getBalance(me5)))

      // await assertWithError(Channel.settleChannelAll( totcnt, cnts, merkleRoots, chidAry, [me1, me2, me3, me4, me5, me6, me7, me8, me9], chunks, {from: alice}))
      // function settleChannelAll(uint totcnt, uint[] cnt, string mroot, uint[] chid, address[] senders, uint[] chunks)
      Logger.debug(  "totcnt: " + totcnt )
      Logger.debug(  "cnts: " + cnts )
      Logger.debug(  "merkleRoots: " + merkleRoots )
      Logger.debug(  "chidAry: " + chidAry )
      Logger.debug(  "snds: " + chunks )


      await debug( Channel.settleChannelAll( totcnt, cnts, merkleRoots, chidAry, snds, chunks ) ) ;

      Logger.debug(  "alice Balance: " + parseInt(await web3.eth.getBalance(alice)))
      Logger.debug(  "me1 Balance: " + parseInt(await web3.eth.getBalance(me1)))
      Logger.debug(  "me2 Balance: " + parseInt(await web3.eth.getBalance(me2)))
      Logger.debug(  "me3 Balance: " + parseInt(await web3.eth.getBalance(me3)))
      Logger.debug(  "me4 Balance: " + parseInt(await web3.eth.getBalance(me4)))
      Logger.debug(  "me5 Balance: " + parseInt(await web3.eth.getBalance(me5)))

    })
    
  })
 

  describe("revoke distributor & product", () => {
    var productList
    before(async () => {
      Msp = await BMSP.new({from: alice})
      Token = await BToken.new({from: alice})
      Channel = await BChannel.new({from: alice})
      await Msp.setOnlyContract(Token.address, {from: alice})
      await Msp.setOnlyContract(Channel.address, {from: alice})
      await Msp.setOutsideContracts(Token.address, {from: alice})
      await Token.setOnlyContracts(Msp.address, Channel.address, {from: alice})
      await Token.setBMSPCon(Msp.address, {from: alice})
      await Channel.setConfig(100000000000, 10, 10000, {from: alice})
      await Channel.setOutsideContracts(Msp.address, Token.address, {from: alice})
      await Msp.appointManager(alice, {from: alice})

      await Msp.requestEnroll(Role.P, {from: bob})
      await Msp.requestEnroll(Role.D, {from: dave})
      await Msp.approveRole([true, true], {from: alice})
      const verifyP = await Msp.verifyRole.call(bob, Role.P, false)
      const verifyCP = await Msp.verifyRole.call(charlie, Role.CP, true)
      const verifyD = await Msp.verifyRole.call(dave, Role.D, false)
      assert.isTrue(verifyP)
      assert.isFalse(verifyCP)
      assert.isTrue(verifyD)

      let tx =  await Token.getCID(charlie, {from: bob})
      cid = getId(tx)
      Logger.debug("cid : " + cid)

      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      tx = await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} )
      dataId = getId(tx)
      Logger.debug("data id: " + dataId)
      dataDetails = await Token.getDataDetails.call(dataId)
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))

      await Token.registerProduct("ccid", "version", 50000, {from: dave})
      await Token.registerProduct("ccid", "version", 50000, {from: dave})
      await Token.registerProduct("ccid", "version", 50000, {from: dave})
      await Token.registerProduct("ccid", "version", 50000, {from: dave})
      await Token.registerProduct("ccid", "version", 50000, {from: dave})
      await Token.registerProduct("ccid", "version", 50000, {from: dave})

      productList = await Token.getList.call(1, {from: dave})
      Logger.debug(productList)
    })

    beforeEach(async() => {
      await Msp.requestEnroll(Role.D, {from: dave})
      await Msp.approveRole([true], {from: alice})
      const verifyD = await Msp.verifyRole.call(dave, Role.D, false)
      assert.isTrue(verifyD)
    })

    it('revoke product by contract owner', async () => {
      let productDetails = await Token.getProductDetails.call(productList[0])
      Logger.debug("product info: " + JSON.stringify(productDetails))
      await Msp.revokeProduct(productList[0], {from: alice})
      productDetails = await Token.getProductDetails.call(productList[0])
      Logger.debug("product info: " + JSON.stringify(productDetails))
      assert.isFalse(productDetails[4])
      await assertWithError(Token.buyProduct(productList[0], {from: alice, value: productDetails[2]}))
    })

    it('revoke product by NO contract owner (should be failed)', async () => {
      await assertWithError(Msp.revokeProduct(productList[1], {from: bob}))
      let productDetails = await Token.getProductDetails.call(productList[1])
      Logger.debug("product info: " + JSON.stringify(productDetails))
      assert.isTrue(productDetails[4])
      let tx = await Token.buyProduct(productList[1], {from: alice, value: productDetails[2]})
      let tokenId = getId(tx)
      Logger.debug("token id: " + tokenId)
    })

    it('revoke product with invalid product id (no effect)', async () => {
      let productDetails = await Token.getProductDetails.call(0)
      Logger.debug("product info (before): " + JSON.stringify(productDetails))
      assert.equal(JSON.stringify(productDetails), JSON.stringify({0:"0x0000000000000000000000000000000000000000",1:"0",2:"0",3:[],4:false}))
      await Msp.revokeProduct(0, {from: alice})
      let productDetailsAfter = await Token.getProductDetails.call(0)
      Logger.debug("product info (after): " + JSON.stringify(productDetailsAfter))
      assert.equal(JSON.stringify(productDetails), JSON.stringify(productDetailsAfter))
    })

    it('revoke distributor by contract owner', async () => {
      await Msp.revokeUser(dave, Role.D, false, false, {from: alice})
      let validity = await Msp.verifyRole(dave, Role.D, false)
      assert.isFalse(validity)
      await assertWithError(Token.registerProduct("ccid", "version", 500000, {from: dave}))
      // productList[0] is already revoked
      for(var i = 1; i < productList.length; i++) {
        let productDetails = await Token.getProductDetails(productList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isTrue(productDetails[4])
      }
      let productDetails = await Token.getProductDetails(productList[1])
      let tx = await Token.buyProduct(productList[1], {from: alice, value: productDetails[2]})
      let tokenId = getId(tx)
      Logger.debug("token id: " + tokenId)
    })

    it('revoke distributor by NO contract owner (should be failed)', async () => {
      await assertWithError(Msp.revokeUser(dave, Role.D, false, false, {from: bob}))
      let validity = await Msp.verifyRole(dave, Role.D, false)
      assert.isTrue(validity)
      let tx = await Token.registerProduct("ccid", "version", 500000, {from: dave})
      let productId = getId(tx)
      Logger.debug("product id: " + productId)
    })

    it('revoke distributor and his all products by NO contract owner (should be failed)', async () => {
      await assertWithError(Msp.revokeUser(dave, Role.D, false, true, {from: bob}))
      let validity = await Msp.verifyRole(dave, Role.D, false)
      assert.isTrue(validity)
      await Token.registerProduct("ccid", "version", 500000, {from: dave})
      // productList[0] is already revoked
      for(var i = 1; i < productList.length; i++) {
        let productDetails = await Token.getProductDetails.call(productList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isTrue(productDetails[4])
        let tx = await Token.buyProduct(productList[i], {from: alice, value: productDetails[2]})
        let tokenId = getId(tx)
        Logger.debug("token id: " + tokenId)
      }
    })

    it('revoke distributor and his all products by contract owner', async () => {
      await Msp.revokeUser(dave, Role.D, false, true, {from: alice})
      let validity = await Msp.verifyRole(dave, Role.D, false)
      assert.isFalse(validity)
      await assertWithError(Token.registerProduct("ccid", "version", 500000, {from: dave}))
      for(var i = 0; i < productList.length; i++) {
        let productDetails = await Token.getProductDetails.call(productList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isFalse(productDetails[4])
        await assertWithError(Token.buyProduct(productList[i], {from: alice, value: productDetails[2]}))
      }
    })
  })

  describe("revoke contents provider & data", () => {
    var dataList
    var data0ProductList
    var data1ProductList
    var data2ProductList
    var productList
    beforeEach(async () => {
      Msp = await BMSP.new({from: alice})
      Token = await BToken.new({from: alice})
      Channel = await BChannel.new({from: alice})
      await Msp.setOnlyContract(Token.address, {from: alice})
      await Msp.setOnlyContract(Channel.address, {from: alice})
      await Msp.setOutsideContracts(Token.address, {from: alice})
      await Token.setOnlyContracts(Msp.address, Channel.address, {from: alice})
      await Token.setBMSPCon(Msp.address, {from: alice})
      await Channel.setConfig(100000000000, 10, 10000, {from: alice})
      await Channel.setOutsideContracts(Msp.address, Token.address, {from: alice})
      await Msp.appointManager(alice, {from: alice})

      await Msp.requestEnroll(Role.P, {from: bob})
      await Msp.requestEnroll(Role.D, {from: dave})
      await Msp.approveRole([true, true], {from: alice})
      const verifyP = await Msp.verifyRole.call(bob, Role.P, false)
      const verifyCP = await Msp.verifyRole.call(charlie, Role.CP, true)
      const verifyD = await Msp.verifyRole.call(dave, Role.D, false)
      assert.isTrue(verifyP)
      assert.isFalse(verifyCP)
      assert.isTrue(verifyD)

      for(var i = 0; i < 3; i++) {
        let tx =  await Token.getCID(charlie, {from: bob})
        let cid = getId(tx)
        await Token.registerData(cid, "ccid" + i, "version" + i, 5000, fileHashes, fileChunks, {from: charlie})
        await Token.registerDataAttr("ccid" + i, "version" + i, "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} )
      }

      dataList = await Token.getList.call(0, {from: charlie})
      Logger.debug("data list: " + dataList)

      data0ProductList = []
      data1ProductList = []
      data2ProductList = []
      let tx = await Token.registerProduct("ccid0", "version0", 500000, {from: dave})
      let productId = getId(tx)
      data0ProductList.push(productId)
      tx = await Token.registerProduct("ccid0", "version0", 500000, {from: dave})
      productId = getId(tx)
      data0ProductList.push(productId)
      tx = await Token.registerProduct("ccid1", "version1", 500000, {from: dave})
      productId = getId(tx)
      data1ProductList.push(productId)
      tx = await Token.registerProduct("ccid1", "version1", 500000, {from: dave})
      productId = getId(tx)
      data1ProductList.push(productId)
      tx = await Token.registerProduct("ccid2", "version2", 500000, {from: dave})
      productId = getId(tx)
      data2ProductList.push(productId)
      tx = await Token.registerProduct("ccid2", "version2", 500000, {from: dave})
      productId = getId(tx)
      data2ProductList.push(productId)
      productList = await Token.getList.call(1, {from: dave})
      Logger.debug("data0 product list: " + data0ProductList)
      Logger.debug("data1 product list: " + data1ProductList)
      Logger.debug("data2 product list: " + data2ProductList)
      Logger.debug("product list: " + productList)
    })

    it('revoke data by contract owner', async () => {
      await Msp.revokeData(dataList[0], false, {from: alice})
      let dataDetails = await Token.getDataDetails.call(dataList[0])
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
      assert.isFalse(dataDetails[6])
      await assertWithError(Token.registerProduct("ccid", "version", 500000, {from: dave}))
      for (var i = 0; i < productList.length; i++) {
        let productDetails = await Token.getProductDetails.call(productList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isTrue(productDetails[4])
        let tx = await Token.buyProduct(productList[i], {from: alice, value: productDetails[2]})
        let tokenId = getId(tx)
        Logger.debug("token id: " + tokenId)
      }
    })

    it('revoke data by NO contract owner (should be failed)', async () => {
      await assertWithError(Msp.revokeData(dataList[0], false, {from: bob}))
      let dataDetails = await Token.getDataDetails.call(dataList[0])
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
      assert.isTrue(dataDetails[6])
      for (var i = 0; i < productList.length; i++) {
        let productDetails = await Token.getProductDetails.call(productList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isTrue(productDetails[4])
      }
    })

    it('revoke data and all associated products by contract owner', async () => {
      await Msp.revokeData(dataList[0], true, {from: alice})
      let dataDetails = await Token.getDataDetails.call(dataList[0])
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
      assert.isFalse(dataDetails[6])
      var tokenListBefore = await Token.getList.call(2, {from: alice})
      Logger.debug("token list: " + tokenListBefore)
      for (var i = 0; i < data0ProductList.length; i++) {
        let productDetails = await Token.getProductDetails.call(data0ProductList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isFalse(productDetails[4])
        await assertWithError(Token.buyProduct(data0ProductList[i], {from: alice, value: productDetails[2]}))
      }
      var tokenListAfter = await Token.getList.call(2, {from: alice})
      Logger.debug("token list: " + tokenListAfter)
      assert.equal(JSON.stringify(tokenListBefore), JSON.stringify(tokenListAfter))
    })

    it('revoke data and all associated products by NO contract owner (should be failed)', async () => {
      await assertWithError(Msp.revokeData(dataList[0], true, {from: bob}))
      let dataDetails = await Token.getDataDetails.call(dataList[0])
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
      assert.isTrue(dataDetails[6])
      var tokenListBefore = await Token.getList.call(2, {from: alice})
      Logger.debug("token list: " + tokenListBefore)
      for (var i = 0; i < data0ProductList.length; i++) {
        let productDetails = await Token.getProductDetails.call(data0ProductList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isTrue(productDetails[4])
        Token.buyProduct(data0ProductList[i], {from: alice, value: productDetails[2]})
      }
      var tokenListAfter = await Token.getList.call(2, {from: alice})
      Logger.debug("token list: " + tokenListAfter)
      assert.notEqual(JSON.stringify(tokenListBefore), JSON.stringify(tokenListAfter))
    })

    it('revoke contents provider by contract owner', async () => {
      let verify = await Msp.verifyRole.call(charlie, Role.CP, true)
      assert.isFalse(verify)
      let tx =  await Token.getCID(charlie, {from: bob})
      let cid = getId(tx)
      Logger.debug("cid: " + cid)
      await Msp.revokeUser(charlie, Role.CP, false, false, {from: alice})
      verify = await Msp.verifyRole.call(charlie, Role.CP, true)
      assert.isTrue(verify)
      await assertWithError(Token.getCID(charlie, {from: bob}))
      await assertWithError(Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie}))
      await assertWithError(Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} ))
      for (var i = 0; i < dataList.length; i++) {
        let dataDetails = await Token.getDataDetails.call(dataList[i])
        Logger.debug("data[" + i + "] info: " + JSON.stringify(dataDetails))
        let dataIdDetails = await Token.getDataAtDetails.call(dataId)
        Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
        assert.isTrue(dataDetails[6])
      }
      for (var i = 0; i < productList.length; i++) {
        let productDetails = await Token.getProductDetails.call(productList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isTrue(productDetails[4])
      }
    })

    it('revoke contents provider by NO contract owner (should be failed)', async () => {
      await assertWithError(Msp.revokeUser(charlie, Role.CP, false, false, {from: bob}))
      let verify = await Msp.verifyRole.call(charlie, Role.CP, true)
      assert.isFalse(verify)
      let tx =  await Token.getCID(charlie, {from: bob})
      let cid = getId(tx)
      Logger.debug("cid: " + cid)
      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      tx = await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0], {from: charlie})
      let dataId = getId(tx)
      Logger.debug("data id: " + dataId)
      let dataDetails = await Token.getDataDetails.call(dataList[0])
      Logger.debug("data info: " + JSON.stringify(dataDetails))
      let dataIdDetails = await Token.getDataAtDetails.call(dataId)
      Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
      assert.isTrue(dataDetails[6])
      for (var i = 0; i < productList.length; i++) {
        let productDetails = await Token.getProductDetails.call(productList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isTrue(productDetails[4])
      }
    })

    it('revoke contents provider along with all his data by contract owner', async () => {
      let tx = await Token.getCID(charlie, {from: bob})
      let cid = getId(tx)
      await Msp.revokeUser(charlie, Role.CP, true, false, {from: alice})
      let verify = await Msp.verifyRole.call(charlie, Role.CP, true)
      assert.isTrue(verify)
      await assertWithError(Token.getCID(charlie, {from: bob}))
      await assertWithError(Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie}))
      await assertWithError(Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} ))
      for (var i = 0; i < dataList.length; i++) {
        let dataDetails = await Token.getDataDetails.call(dataList[i])
        Logger.debug("data[" + i + "] info: " + JSON.stringify(dataDetails))
        let dataIdDetails = await Token.getDataAtDetails.call(dataId)
        Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
        assert.isFalse(dataDetails[6])
      }
      await assertWithError(Token.registerProduct("ccid", "version", 500000, {from: dave}))
      var tokenListBefore = await Token.getList.call(2, {from: alice})
      Logger.debug("token list: " + tokenListBefore)
      for (var i = 0; i < productList.length; i++) {
        let productDetails = await Token.getProductDetails.call(productList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isTrue(productDetails[4])
        await Token.buyProduct(productList[i], {from: alice, value: productDetails[2]})
      }
      var tokenListAfter = await Token.getList.call(2, {from: alice})
      Logger.debug("token list: " + tokenListAfter)
      assert.notEqual(JSON.stringify(tokenListBefore), JSON.stringify(tokenListAfter))
    })

    it('revoke contents provider along with all his data by NO contract owner (should be failed)', async () => {
      await assertWithError(Msp.revokeUser(charlie, Role.CP, true, false, {from: bob}))
      let verify = await Msp.verifyRole.call(charlie, Role.CP, true)
      assert.isFalse(verify)
      let tx = await Token.getCID(charlie, {from: bob})
      let cid = getId(tx)
      Logger.debug("cid: " + cid)
      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      tx = await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} )
      let dataId = getId(tx)
      Logger.debug("data id: " + dataId)
      await Token.registerProduct("ccid", "version", 50000, {from: dave})
      for (var i = 0; i < dataList.length; i++) {
        let dataDetails = await Token.getDataDetails.call(dataList[i])
        Logger.debug("data[" + i + "] info: " + JSON.stringify(dataDetails))
        let dataIdDetails = await Token.getDataAtDetails.call(dataId)
        Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
        assert.isTrue(dataDetails[6])
      }
      for (var i = 0; i < productList.length; i++) {
        let productDetails = await Token.getProductDetails.call(productList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isTrue(productDetails[4])
      }
    })

    it('revoke contents providers along with all his data and all products associated with revoked data by contract owner', async () => {
      let tx = await Token.getCID(charlie, {from: bob})
      let cid = getId(tx)
      await Msp.revokeUser(charlie, Role.CP, true, true, {from: alice})
      let verify = await Msp.verifyRole.call(charlie, Role.CP, true)
      assert.isTrue(verify)
      await assertWithError(Token.getCID(charlie, {from: bob}))
      await assertWithError(Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie}))
      await assertWithError(Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} ))
      for (var i = 0; i < dataList.length; i++) {
        let dataDetails = await Token.getDataDetails.call(dataList[i])
        Logger.debug("data[" + i + "] info: " + JSON.stringify(dataDetails))
        let dataIdDetails = await Token.getDataAtDetails.call(dataId)
        Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
        assert.isFalse(dataDetails[6])
      }
      await assertWithError(Token.registerProduct("ccid", "version", 500000, {from: dave}))
      var tokenListBefore = await Token.getList.call(2, {from: alice})
      Logger.debug("token list: " + tokenListBefore)
      for (var i = 0; i < productList.length; i++) {
        let productDetails = await Token.getProductDetails.call(productList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isFalse(productDetails[4])
        await assertWithError(Token.buyProduct(productList[i], {from: alice, value: productDetails[2]}))
      }
      var tokenListAfter = await Token.getList.call(2, {from: alice})
      Logger.debug("token list: " + tokenListAfter)
      assert.equal(JSON.stringify(tokenListBefore), JSON.stringify(tokenListAfter))
    })

    it('revoke contents providers along with all his data and all products associated with revoked data by NO contract owner (should be failed)', async () => {
      await assertWithError(Msp.revokeUser(charlie, Role.CP, true, true, {from: bob}))
      let verify = await Msp.verifyRole.call(charlie, Role.CP, true)
      assert.isFalse(verify)
      let tx = await Token.getCID(charlie, {from: bob})
      let cid = getId(tx)
      Logger.debug("cid: " + cid)
      await Token.registerData(cid, "ccid", "version", 5000, fileHashes, fileChunks, {from: charlie})
      tx = await Token.registerDataAttr("ccid", "version", "dataInfo", ['0x0000000000000000000000000000000000000000'], ['0x0000000000000000000000000000000000000000'], [1,0,0,0,0],  true, {from: charlie} )
      let dataId = getId(tx)
      Logger.debug("data id: " + dataId)
      await Token.registerProduct("ccid", "version", 50000, {from: dave})
      for (var i = 0; i < dataList.length; i++) {
        let dataDetails = await Token.getDataDetails.call(dataList[i])
        Logger.debug("data[" + i + "] info: " + JSON.stringify(dataDetails))
        let dataIdDetails = await Token.getDataAtDetails.call(dataId)
        Logger.debug("data info identity: " + JSON.stringify(dataIdDetails))
        assert.isTrue(dataDetails[6])
      }
      for (var i = 0; i < productList.length; i++) {
        let productDetails = await Token.getProductDetails.call(productList[i])
        Logger.debug("product[" + i + "] info: " + JSON.stringify(productDetails))
        assert.isTrue(productDetails[4])
      }
    })
  })
})
