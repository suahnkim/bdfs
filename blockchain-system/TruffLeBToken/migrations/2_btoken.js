const { readFileSync, writeFileSync } = require('fs')

const BMSP = artifacts.require("./BMSP.sol")
const BToken = artifacts.require("./BToken.sol") 
const BChannel = artifacts.require("./BChannel.sol")
const Bconfig = artifacts.require("./Bconfig.sol")
const BProduct = artifacts.require("./BProduct.sol")


module.exports = function(deployer, network, accounts){
  deployer.then(async ()=>{
    try {
      const [_, user] = accounts
      console.log('>>> network: ' + network)
      for(let i = 0; i < accounts.length; i++){
        console.log('>>> accounts[' + i + ']: ' + accounts[i])
      }
      const BMSPInst = await deployer.deploy(BMSP)
      const BTokenInst = await deployer.deploy(BToken) 
      const BChannelInst = await deployer.deploy(BChannel)
      const BconfigInst = await deployer.deploy(Bconfig)
      const BProductInst = await deployer.deploy(BProduct)

      console.log("BMSP address: " + BMSP.address)
      console.log("BToken address: " + BToken.address) 
      console.log("BChannel address: " + BChannel.address)
      console.log("Bconfig address: " + Bconfig.address)
      console.log("BProduct address: " + BProduct.address)

      await BMSPInst.setOnlyContract(BToken.address) 
      await BMSPInst.setOnlyContract(BChannel.address)      
      await BMSPInst.setOutsideContracts(BToken.address)       
      await BTokenInst.setOnlyContracts(BMSP.address, BChannel.address)
      await BTokenInst.setBMSPCon(BMSP.address)      
      await BChannelInst.setConfig(1, 10, 10000)
      await BChannelInst.setOutsideContracts(BMSP.address, BToken.address)
      await BProductInst.setOutsideContracts(BToken.address) 
      
      const GwDAppAddr = readFileSync('../gateway_dappchain_address_local', 'utf-8')
      var jsonGwDAppAddr = {address: GwDAppAddr}
      writeFileSync("../WebCLnt/src/gateway_dappchain_address_local.json", JSON.stringify(jsonGwDAppAddr))
    } catch (err) {
      console.log("err: " + err)
    }

  })
}
