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

/**
 * 사용자가 등록한 콘텐츠, 상품 목록을 조회한다.        
 * @type Restful API 
 * @Method POST
 * @URL /list   
 * @Response {json} 처리결과
 */
router.post('/', async function (req, res) {
    try {
      global.Logger.debug('/list req: ' + JSON.stringify(req.body));
      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        global.Logger.error('/list error: not logined' );
        return;
      };
      // var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;

      const dataList = await DappTools.getList(0);
      const productList = await DappTools.getList(1);
      const tokenList = await DappTools.getList(2);
      var data = [];
      for (var i = 0; i < dataList.length; i++) {
        var fileList = await DappTools.listFileWithDataId(dataList[i]);
        data.push({
          id: dataList[i],
          files: fileList
        });
      };

      res.json({
        resultCode: 0,
        dataList : data,
        productList: productList,
        purchaseList: tokenList
      });
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message
      });
      global.Logger.error('/list error: ' + error);
    }; 
});

module.exports = router;