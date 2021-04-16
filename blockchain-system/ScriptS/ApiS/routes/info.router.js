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
 * 콘텐츠 상세 정보를 조회한다.   
 * @type Restful API 
 * @Method POST
 * @URL /info/data  
 * @Request {string} dataId 등록된 콘텐츠의 data id
 * @Request {string} ccid 복합 콘텐츠 ID
 * @Request {string} version CCID 버전
 * @Response {json} 처리결과
 */
router.post('/data', async function (req, res) {
    try {
      global.Logger.debug('\n/info/data req: ' + JSON.stringify(req.body));
      if(global.Tools == null) {
        res.json({
          resultCode: 300,
          resultMessage: 'not logined'
        });
        return;
      };
    //   var EtherTools = global.Tools.EtherTools;
      var DappTools = global.Tools.DappTools;

      const dataId = req.body.dataId;
      const ccid = req.body.ccid;
      const version = req.body.version;

      if(dataId == null && (ccid == null || version == null)) {
        res.json({
          code:500,
          resultMessage: 'data_id or ccid and version must be entered'
        });
      };

      if(dataId != null) {
          var dataInfo = await DappTools.getDataDetailsWithId(dataId);
          var UsageRestriction = await DappTools.getDataAtDetailsID(dataId);
      } else {
          var dataInfo = await DappTools.getDataDetailsWithCCIDNVersion(ccid, version);
          var UsageRestriction = await DappTools.getDataAtDetailsWithCCIDNVersion(ccid, version);
      };

      res.json({
        resultCode: 0,
        owner: dataInfo[0],
        cid:  "CID" + pad(dataInfo[1], 13),
        ccid: dataInfo[2],
        version: dataInfo[3],
        fee: dataInfo[4],
        validity: dataInfo[6],
        info: dataInfo[7],
        target_dist: dataInfo[8],
        target_user: dataInfo[9],
        getDataAtDetails: UsageRestriction,
      });
    } catch (error) {
      res.json({
        resultCode: 500,
        resultMessage: error.message
      });
      global.Logger.error('error occured: ' + error);
    }; 
});

/**
 * 콘텐츠의 구성 파일 ID를 조회한다.    
 * @type Restful API 
 * @Method POST
 * @URL /info/file  
 * @Request {string} fileId 등록된 콘텐츠의 file id 
 * @Request {string} hash 콘텐츠 정보에 포함되는 파일의 구분을 위한 유니크한 값
 * @Response {json} 처리결과
 */
router.post('/file', async function (req, res) {
    try {
        global.Logger.debug('\n/info/file req: ' + JSON.stringify(req.body));
        if(global.Tools == null) {
            res.json({
                resultCode: 300,
                resultMessage: 'not logined'
            });
            return;
        };
        // var EtherTools = global.Tools.EtherTools;
        var DappTools = global.Tools.DappTools;

        const fileId = req.body.fileId;
        const hash = req.body.hash;

        if(fileId == null && hash == null) {
            res.json({
                resultCode: 500,
                resultMessage: 'file_id or hash must be entered'
            }); 
            return;
        };

        if(fileId != null) {
            var fileInfo = await DappTools.getFileDetailsWithId(fileId);
        } else {
            var fileInfo = await DappTools.getFileDetailsWithHash(hash);
        }

        res.json({
            resultCode: 0,
            dataId: fileInfo[0],
            chunks: fileInfo[1],
        });
    } catch (error) {
        res.json({
            resultCode: 500,
            resultMessage: error.message
        });
        global.Logger.error('error occured: ' + error);
    }; 
});

/**
 * 상품 상세 정보를 조회한다.     
 * @type Restful API 
 * @Method POST
 * @URL /info/product  
 * @Request {string} productId 등록된 상품의 product id 
 * @Response {json} 처리결과
 */
router.post('/product', async function (req, res) {
    try {
        global.Logger.debug('\n/info/product req: ' + JSON.stringify(req.body));
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
        const productInfo = await DappTools.getProductDetails(productId);
        const UsageRestriction = await DappTools.getDataAtDetailsID(productInfo[1]);

        res.json({
            resultCode: 0,
            owner: productInfo[0],
            dataId: productInfo[1],
            price: productInfo[2],
            target_user: productInfo[3],
            validity: productInfo[4],
            getDataAtDetails: UsageRestriction
        });
    } catch (error) {
        res.json({
            resultCode: 500,
            resultMessage: error.message
        });
        global.Logger.error('error occured: ' + error);
    }; 
});

/**
 * 상품 구매 상세 정보를 조회한다.      
 * @type Restful API 
 * @Method POST
 * @URL /info/token  
 * @Request {string} purchaseId 구매 ID
 * @Response {json} 처리결과
 */
router.post('/token', async function (req, res) {
    try {
        global.Logger.debug('/info/token req: ' + JSON.stringify(req.body));
        if(global.Tools == null) {
            res.json({
                resultCode: 300,
                resultMessage: 'not logined'
            });
            global.Logger.error('/info/token error : not logined' );
            return;
        };
        // var EtherTools = global.Tools.EtherTools;
        var DappTools = global.Tools.DappTools;

        const tokenId = req.body.purchaseId;
        var state = ['invalid', 'valid', 'in_progress'];
        const detailsInfo = await DappTools.getTokenDetails(tokenId);
        const deposit = await DappTools.getDepositNCollateral(tokenId);
        res.json({
            resultCode: 0,
            info: {
                owner: detailsInfo[0],
                productId: detailsInfo[1],
                state: state[detailsInfo[2]]
            },
            deposit: {
                deposit: deposit[0],
                collateral: deposit[1],
                total: parseInt(deposit[0]) + parseInt(deposit[1])
            }
        })
    } catch (error) {
        res.json({
            resultCode: 500,
            resultMessage: error.message
        });
        global.Logger.error('/info/token error : ' + error);
    }; 
});

/**
 * 오프체인 채널 상세 정보를 조회한다.       
 * @type Restful API 
 * @Method POST
 * @URL /info/channel  
 * @Request {string} channelId 등록된 채널의 id
 * @Response {json} 처리결과
 */
router.post('/channel', async function (req, res) {
    try {
        global.Logger.debug('/info/channel req: ' + JSON.stringify(req.body));
        if(global.Tools == null) {
            res.json({
                resultCode: 300,
                resultMessage: 'not logined'
            });
            global.Logger.error('/info/channel error: not logined' );
            return;
        };
        // var EtherTools = global.Tools.EtherTools;
        var DappTools = global.Tools.DappTools;

        channelId = req.body.channelId;
        var state = ['invalid', 'open', 'off', 'settle'];
        const channelInfo = await DappTools.getChannelDetails(channelId);
        res.json({
            resultCode: 0,
            receiver: channelInfo[0],
            purchaseId: channelInfo[1],
            publicKey: channelInfo[2],
            deposit: channelInfo[3],
            collateral: channelInfo[4],
            timestamp: channelInfo[5],
            timeout: channelInfo[6],
            state: state[channelInfo[7]]
        });
    } catch (error) {
        res.json({
            resultCode: 500,
            resultMessage: error.message
        });
        global.Logger.error('/info/channel error : ' + error);
    }; 
});

/**
 * 입력된 n에  width 길이 만큼 0 padding한다. 
 * @param {string} n 입력값
 * @param {int} width 0 padding 길이 
 * @returns {string} 0 padding된 문자열
 */
function pad(n, width) {
    n = n + '';
    return n.length >= width ? n : new Array(width - n.length + 1).join('0') + n;
}

module.exports = router;