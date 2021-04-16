/*****************************************************************
*                           Chainlink API 
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5 
* @module ChainLink API / router 
* @history : 
*****************************************************************/

var express = require('express');
var Web3Util = require('web3-utils');
var CryptoUtils = require('loom-js');
var router = express.Router();

var {
  CryptoUtils
} = require('loom-js')

/**
 * 이더리움 계정에서 보유하고 있는 토큰을 사이드 체인 계정으로 전송한다 
 * @type Restful API 
 * @Method POST
 * @URL /send/ethereum
 * @Request {string} unit 전송하고자 하는 단위 
 * @Request {string} amount 전송하고자 토큰 금액  
 * @Response {json} 처리결과
 */
router.post('/ethereum', async function (req, res) {  
  try {
    global.Logger.debug('\n/send/ethereum req: ' + JSON.stringify(req.body));
    if(global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    var EtherTools = global.Tools.EtherTools;

    const unit = req.body.unit;
    const amount = req.body.amount;

    var EthWeb3 = EtherTools.getWeb3();
    const ethAddress = EtherTools.getWallet().getAddressString();
    const balanceBefore = await EthWeb3.eth.getBalance(ethAddress);
    
    await EtherTools.Deposit2GatewayAsync(ethAddress, unit, amount);
    const balanceAfter = await EthWeb3.eth.getBalance(ethAddress);
    
    res.json({
      resultCode: 0,
      ethAddress,
      balanceBefore,
      balanceAfter
    });
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/send/ethereum error: ' + error.message);
  };
});

/**
 * 사이드체인에서 보유하고 있는 토큰을 이더리움 메인넷 게이트웨이로 이더를 출금한다.  
 * @type Restful API 
 * @Method POST
 * @URL /send/loom
 * @Request {string} unit 전송하고자 하는 단위 
 * @Request {string} amount 전송하고자 토큰 금액  
 * @Response {json} 처리결과
 */
router.post('/loom', async function (req, res) {
    try {
      global.Logger.debug('/send/loom req: ' + JSON.stringify(req.body));
      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        return;
      };
      // var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;
      const unit = req.body.unit;
      const amount = req.body.amount;

      /* send ether from dapp account to gateway */
      const balance = (await DappTools.GetBaLanceAsync()).toString();
      console.log("balance : ", balance);

      const sendAmount = Web3Util.toWei(amount, unit);
      console.log("sendAmount : ", sendAmount);

      if (balance < sendAmount) {
        console.log(JSON.stringify({resultMessage: 'insufficient balance'}));
        res.json({
          resultCode: 400,
          resultMessage: 'insufficient balance'
        });
        return;
      };

      await DappTools.ApproveAsync(sendAmount);
      await DappTools.WithdrawEthAsync(sendAmount);

      res.json({
        resultCode: 0,
        sendAmount
      });
    } catch (error) {
      if (error.message.indexOf('pending') > -1) {
        res.json({
          resultCode: 500,
          resultMessage: 'pending already exists'
        });
      } else {
        res.json({
          resultCode: 500,
          resultMessage: error.message
        });
        global.Logger.error('/send/loom  error : ' + error);
      };
    }; 
});

/**
 * 메인넷 게이트웨이가 보관하고 있는 토큰을 이더리움 계정으로 전송한다
 * @type Restful API 
 * @Method POST
 * @URL /send/withdraw
 * @Request {string} unit 전송하고자 하는 단위 
 * @Request {string} amount 전송하고자 토큰 금액  
 * @Response {json} 처리결과
 */
router.post('/withdraw', async function (req, res) {
    try {
      global.Logger.debug('\n/send/withdraw req: ' + JSON.stringify(req.body));
      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        return;
      };
      var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;

      /* get ether from gateway to ethereum account */
      const Account = DappTools.GetAccount();
      const Data = await DappTools.WithdrawaLReceiptAsync(Account);

      let EtherBaLance = 0;
      if (Data) {
        switch (Data.tokenKind) {
          case 0:
            EtherBaLance = +Data.value.toString(10);
            break;
        };
      };

      const Owner = Data.tokenOwner.local.toString();
      const Signature = CryptoUtils.bytesToHexAddr(Data.oracleSignature);

      await EtherTools.WithdrawEthAsync(Owner, EtherBaLance, Signature);
      
      res.json({
        resultCode: 0,
        withdraw: EtherBaLance
      });
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message
      });
      global.Logger.error('/send/withdraw error : ' + error);
    }; 
});

module.exports = router;