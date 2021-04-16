/*****************************************************************
*                           Chainlink API 
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5 
* @module ChainLink API / router 
* @history : 
*****************************************************************/
var express = require('express');
var Ether = require('../ether.js');
var Dapp = require('../dapp.js');

var router = express.Router();
const roles = ['P', 'CP', 'SP', 'D'];

/**
 * 사용 Ethereum과 Dappchain을 초기화한다. 
 * @param {string} address 사용자 계정 ID  
 * @param {string} password 계정ID의 Private Key Password 
 * @returns {EtherTools,DappTools}; 초기화된 Class 
 */
async function initTools(address, password) {
    /* init Ethereum elements */
    global.Logger.debug('init ethereum tools...');
    var EtherTools = await Ether.createAsync(address, password);
    // global.Logger.debug('init complete');

    /* init Dappchain elements */
    global.Logger.debug('init dapp tools...');
    var DappTools = await Dapp.createAsync(EtherTools.getDappPrivateKey());
    // global.Logger.debug('init complete');

    return {
      EtherTools,
      DappTools
    };
};

/**
 * 시스템의 관리자를 지정한다.
 * owner만 사용가능하며, 시스템 초기 구성시 1회만 사용한다. 
 * @type Restful API 
 * @Method POST
 * @URL /msp/appointManager
 * @Request {string} target 관리자 계정 id 
 * @Response {json} 처리결과
 */
router.post('/appointManager', async function (req, res) {
    try {
      global.Logger.debug('\n/msp/appointManager req: ' + JSON.stringify(req.body));
      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        return;
      };
      // var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;

      const target = req.body.target;
      await DappTools.appointManager(target);
      res.json({
        resultCode: 0,
        result: 'succeed'
      });
      global.Logger.debug('/msp/appointManager : succeed');
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message
      });
      global.Logger.error('/msp/appointManager error: ' + error.message);
    }; 
});

/**
 * 사용자 계정의 사용권한을 등록 요청한다. 
 * @type Restful API 
 * @Method POST
 * @URL /msp/authRequest
 * @Request {string} role 사용자의 사용권한 
 *       “P” : 콘텐츠암호화처리자(packager) 
         “CP” : 생산자 (contents provider) 
         “SP” : 스토리지 제공자 (storage provider)
         “D” : 유통업자 (distributor)
 * @Response {json} 처리결과
 */
router.post('/authRequest', async function (req, res) {
    try {
      global.Logger.debug('\n/msp/authRequest req: ' + JSON.stringify(req.body));
      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        return;
      };
      // var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;

      const role = req.body.role;
      const roleIndex = roles.indexOf(role);
      if (roleIndex == -1) {
        res.json({
          resultCode: 100,
          resultMessage: 'invalid role. choose P|SP|D'
        }); 
        return;
      };
      await DappTools.requestEnroll(2 ** roleIndex);
      res.json({
        resultCode: 0,
        result: 'succeed'
      });
      global.Logger.debug('/msp/authRequest : succeed');
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message
      });
      global.Logger.error('/msp/authRequest error : ' + error.message);
    }; 
});

/**
 * 관리자가 사용자의 권한을 부여하기 위하여 사용자가 요청한 사용권한 요청 목록 중 미처리 목록을 확인한다. 
 * @type Restful API 
 * @Method POST
 * @URL /msp/getRequests
 * @Response {json} 처리결과
 */
router.post('/getRequests', async function (req, res) {
    try {
      global.Logger.debug('\n/msp/getRequests req: ' + JSON.stringify(req.body));
      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        return;
      };
      // var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;

      const list = await DappTools.getRequests();
      res.json({
        resultCode: 0,
        list
      });

      global.Logger.debug('/msp/getRequests : succeed');
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message + "\nCheck your roles."
      });
      global.Logger.error('/msp/getRequests error: ' + error.message);
    }; 
});

/**
 * 관리자가 사용권한을 요청한 건에 대하여 승인처리한다.    
 * @type Restful API 
 * @Method POST
 * @URL /msp/approve
 * @Request {string} approvals 요청 건에 대한 승인 내역 
 * @Response {json} 처리결과
 */
router.post('/approve', async function (req, res) {
    try {
      global.Logger.debug('\n/msp/approve: ' + JSON.stringify(req.body));
      const approvals = req.body.approvals;
      // global.Logger.debug("approvals: " + approvals );
      // global.Logger.debug("approvals: " + JSON.parse(approvals) );

      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        return;
      };
      // var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;

      await DappTools.approveRole(JSON.parse(approvals));
      res.json({
        resultCode: 0,
        result: 'succeed'
      });
      global.Logger.debug('/msp/approve : succeed');
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message
      });
      global.Logger.error('/msp/approve error : ' + error.message);
    }; 
});

/**
 * 사용자의 사용권한이 정당한 권한을 가지고 있는지 확인한다.    
 * @type Restful API 
 * @Method POST
 * @URL /msp/verify
 * @Request {string} target 검증대상자의 accountId 
 * @Request {string} role 검증대상자의 사용권한  
 * @Response {json} 처리결과
 */
router.post('/verify', async function (req, res) {
    try {
      global.Logger.debug('/msp/verify req: ' + JSON.stringify(req.body));
      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        return;
      };
      // var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;

      const role = req.body.role;
      //const target = req.body.target
      var target = "";
      if( req.body.target.substr(0, 2).toLowerCase() == "0x") {
        target = req.body.target.substr(2, req.body.target.length).toLowerCase();
      }else{
        target = req.body.target.toLowerCase();
      };

      const roleIndex = roles.indexOf(role);
      if (roleIndex == -1) {
        res.json({
          resultCode: 100,
          resultMessage: 'invalid role. choose P|CP|SP|D'
        }); 
        global.Logger.error('/msp/verify  error : ' + 'invalid role. choose P|CP|SP|D' );
        return;
      };
      const verify = await DappTools.verifyRole(target, 2 ** roleIndex);
      var result  = "false";
      if( verify == true ) {
        res.json({
          resultCode: 0,
          result : "succeed"
        });
        global.Logger.debug('/msp/verify : succeed');
      }else{
        res.json({
          resultCode: 400,
          resultMessage: " verify fail",
          result : "false"
        });
        global.Logger.error('/msp/verify  error : ' + " verify fail" );
      };
      
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message
      });
      global.Logger.error('/msp/verify  error : ' + error.message);
    }; 
});


/**
 * 테스트를 위한 사용자 계정을 셋팅한다.     
 * @type Restful API 
 * @Method POST
 * @URL /msp/test 
 * @Response {json} 처리결과
 */
router.post('/test', async function (req, res) {
    try {
      global.Logger.debug('\n/msp/test req: ' + JSON.stringify(req.body));
      var ContractOwner = '1ee77618b9e4f7651381e2ede71b0d389f27a5c6';
      var Packager = 'e8a524218524edc9af8a921aef70f0fa4fad7fb5';
      var Packager2 = 'e6b086ce68ab7bf68c712d820a38f33fb9f8d552';
      var ContentsPovider = '9bcecd9085fae8fa787ac3f3bd3c2f25a90e0610';
      var Distributor = 'c7cf04aa9a7a6d548e6d1dac8f7401f4a36ad32b';
      var ServiceProvider = 'eccc317d9cd4757b361ed355f66626b5f2fb6292';

      var SP1 = '5a5e93fc1a30428a311fd701c537bd0e9d81992f';
      var SP2 = '28fef8aa5232dca6147546cef2d5cf27893d1f3b';
      var SP3 = 'aaf18c8532a367e490e7657a0e64c2a1d5217148';
      var SP4 = 'c550f24b372a6d14e14919ccd151beebffb5e4f8';
      var CP1 = '5a5e93fc1a30428a311fd701c537bd0e9d81992f';
      var CP2 = '8868246e25a582bf930d772bb67ca002cbafd17a';
      var CP3 = 'abdfbfc1bc4ae925986a52eee07e6e37dedf0cb6';
      var CP4 = 'f7c431f83cb706d470606833db8ce6dc2bceb1fe';
      var DP1 = '2aa60d4469514e5ab40b3790a675e5dcba58c6bc';

      var OwnerTools = await initTools(ContractOwner, 'p@ssw0rd');
      var EtherTools = OwnerTools.EtherTools;
      var DappTools = OwnerTools.DappTools;
 
      if(!(await DappTools.verifyRole(ContractOwner, 16))) { 
        await DappTools.appointManager(ContractOwner); 
      };
 
      if(!(await DappTools.verifyRole(Packager, 1))) {
        let ReqTools = await initTools(Packager, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(1);
        await DappTools.approveRole([true]);
      }; 

      if(!(await DappTools.verifyRole(Packager2, 1))) {
        let ReqTools = await initTools(Packager2, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(1);
        await DappTools.approveRole([true]);
      };
      
      if(!(await DappTools.verifyRole(ContentsPovider, 2))) {
        let ReqTools = await initTools(ContentsPovider, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(2);
        await DappTools.approveRole([true]);
      };

      if(!(await DappTools.verifyRole(ServiceProvider, 4))) {
        let ReqTools = await initTools(ServiceProvider, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(4);
        await DappTools.approveRole([true]);
      };

      if(!(await DappTools.verifyRole(Distributor, 8))) {
        let ReqTools = await initTools(Distributor, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(8);
        await DappTools.approveRole([true]);
      };

      if(!(await DappTools.verifyRole(SP1, 4))) {
        let ReqTools = await initTools(SP1, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(4);
        await DappTools.approveRole([true]);
      };

      if(!(await DappTools.verifyRole(SP2, 4))) {
        let ReqTools = await initTools(SP2, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(4);
        await DappTools.approveRole([true]);
      };

      if(!(await DappTools.verifyRole(SP3, 4))) {
        let ReqTools = await initTools(SP3, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(4);
        await DappTools.approveRole([true]);
      };

      if(!(await DappTools.verifyRole(SP4, 4))) {
        let ReqTools = await initTools(SP4, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(4);
        await DappTools.approveRole([true]);
      };

      if(!(await DappTools.verifyRole(CP1, 2))) {
        let ReqTools = await initTools(CP1, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(2);
        await DappTools.approveRole([true]);
      };

      if(!(await DappTools.verifyRole(CP2, 2))) {
        let ReqTools = await initTools(CP2, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(2);
        await DappTools.approveRole([true]);
      };

      if(!(await DappTools.verifyRole(CP3, 2))) {
        let ReqTools = await initTools(CP3, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(2);
        await DappTools.approveRole([true]);
      };

      if(!(await DappTools.verifyRole(CP4, 2))) {
        let ReqTools = await initTools(CP4, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(2);
        await DappTools.approveRole([true]);
      };

      if(!(await DappTools.verifyRole(DP1, 8))) {
        let ReqTools = await initTools(DP1, 'p@ssw0rd');
        let ReqEtherTools = ReqTools.EtherTools;
        let ReqDappTools = ReqTools.DappTools;
        await ReqDappTools.requestEnroll(8);
        await DappTools.approveRole([true]);
      };

      res.json({
        resultCode: 0,
        state: 'succeed'
      });
      global.Logger.error('/msp/test sucess ' );
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message
      });
      global.Logger.error('/msp/test error : ' + error.message );
    }; 
});


module.exports = router;