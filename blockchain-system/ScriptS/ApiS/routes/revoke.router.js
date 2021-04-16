/*****************************************************************
*                           Chainlink API 
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5 
* @module ChainLink API / router 
* @history : 
*****************************************************************/

var express = require('express');
var router = express.Router();
const roles = ['P', 'CP', 'SP', 'D'];


/**
 * 사용자의 사용권한을 제거하고, 사용자가 생성한 콘텐츠와 상품을 모두 차단 처리한다. 
 * @type Restful API 
 * @Method POST
 * @URL /revoke/user
 * @Request {string} targetId 차단 대상자의 accountId
 * @Request {string} role 차단하고자하는 ROLE 
 * @Request {string} delete_all_datas 데이터 차단 여부 
 * @Request {string} delete_all_products 상품 차단 여부  
 * @Response {json} 처리결과
 */
router.post('/user', async function (req, res) {
    try {
      global.Logger.debug('\n/revoke/user req: ' + JSON.stringify(req.body));
      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        return;
      };
      // var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;

      const target = req.body.targetId;
      const role = req.body.role;
      const deleteDatas = req.body.delete_all_datas;
      const deleteProducts = req.body.delete_all_products;
      const roleIndex = roles.indexOf(role);
      if (roleIndex == -1) {
        res.json({
          resultCode: 100,
          resultMessage: 'invalid role. choose P|CP|SP|D'
        });        
        return;
      };
      await DappTools.revokeUser(target, 2 ** roleIndex, deleteDatas, deleteProducts);
      res.json({
        resultCode: 0,
        state: 'succeed'
      });
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message
      });
      global.Logger.error('/revoke/user error : ' + error.message);
    }; 
});

/**
 * 콘텐츠를 차단하고, 해당 콘텐츠를 사용한 상품을 모두 차단 처리한다. 
 * @type Restful API 
 * @Method POST
 * @URL /revoke/data
 * @Request {string} dataId 차단 대상자의 data id  
 * @Request {string} delete_all_products 상품 차단 여부  
 * @Response {json} 처리결과
 */
router.post('/data', async function (req, res) {
    try {
      global.Logger.debug('\n/revoke/data req: ' + JSON.stringify(req.body));
      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        return;
      };
      // var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;

      dataId = req.body.dataid;
      deleteAll = req.body.delete_all_products;
      await DappTools.revokeData(dataId, deleteAll);
      res.json({
        resultCode: 0,
        state: 'succeed'
      });
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message
      });
      global.Logger.error('/revoke/data error : ' + error.message);
    }; 
});

/**
 * 등록된 콘텐츠 상품을 차단 처리한다.  
 * @type Restful API 
 * @Method POST
 * @URL /revoke/product
 * @Request {string} productId 차단 대상 상품 id   
 * @Response {json} 처리결과
 */
router.post('/product', async function (req, res) {
    try {
      global.Logger.debug('/revoke/product req: ' + JSON.stringify(req.body));
      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        return;
      };
      // var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;

      const productId = req.body.productId;
      await DappTools.revokeProduct(productId);
      res.json({
        resultCode: 0,
        state: 'succeed'
      });
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message
      });
      global.Logger.error('error occured: ' + error);
    }; 
});

module.exports = router;