/*****************************************************************
*                           Chainlink API 
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5 
* @module ChainLink API / router 
* @history : 
*****************************************************************/

var express = require('express');
var url = require('url');

var router = express.Router();

/**
 * chainlink api 접속 확인   
 * - 함수 수행전 /account/login가 실행 필요 
 * @type Restful API 
 * @Method POST
 * @URL /mediablockchain/content/register 
 * @Response {json} 처리결과
 */
router.get('/content/register', async function (req, res) {
    try {
      global.Logger.debug('\n/content/register req: ' + JSON.stringify(req.body));
      var reqUrlString = req.url;
      var urlObject = url.parse(reqUrlString, true, false);
      global.Logger.debug(reqUrlString);
      global.Logger.debug(urlObject);
      res.json({
        result: 0,
        desc: 'Success'
      });
    } catch (error) {
      res.json({
        result: 500,
        desc: "fail"
      });
      global.Logger.error('error occured: ' + error);
    };
});

module.exports = router;