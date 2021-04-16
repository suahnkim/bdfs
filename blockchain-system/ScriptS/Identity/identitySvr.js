var express = require('express');
var cors = require('cors');
var bodyParser = require('body-parser');
var app = express();
const path = require('path')
const cluster = require('cluster');
const numCPUs = require('os').cpus().length;
const process = require('process');

const cmd = require('commander');
const _logger = require('./logger');
var logger = _logger.createLogger('none');
var Log4JS = require('log4js');

var Identity = null;
var _httpPort = 55446
var _procCnt = 1
var _logHome = "./logs"

const resultCode = "resultCode"


var Logger ;
Log4JS.configure({
  appenders: { Identitier: { type: 'file', filename: _logHome + '/Verifier_.log', maxLogSize: 524288, backups: 2, compress: true }
   },
  categories: { default: { appenders: ['Identitier'], level: 'error' } }
})

Logger = Log4JS.getLogger('Identitier')
Logger.level = "debug"


const {
    printFailStrJSON,
    printFailJSON
} = require('./util');

var actionExecuted = false;

function reqIdentity() {
    if (Identity == null) {
        Identity = require('./identity');
    }
    return Identity;
}

if (cluster.isMaster) {
  let numReqs = 0;

  // Count requests
  function messageHandler(msg) {
    if (msg.cmd && msg.cmd === 'notifyRequest') {
      numReqs += 1;
    }
  }

  // Start workers and listen for messages containing notifyRequest
  for (let i = 0; i < _procCnt; i++) {
    cluster.fork();
  }

  for (const id in cluster.workers) {
    cluster.workers[id].on('message', messageHandler);
  }

  cluster.on('exit', (worker, code, signal) => {
    console.log(`worker ${worker.process.pid} died`);
    cluster.fork();
  });
} else {
  app.use(cors());
  app.use(bodyParser.json());
  app.use(bodyParser.urlencoded({
    extended: true
  }));

  app.post('/registerid', async function (req, res) {
    try {
      Logger.debug('registerid req: ' + JSON.stringify(req.body))

      if( actionExecuted == true ){
        res.json({
          resultCode: 500,
          resultMessage: "Job Processing."
        })
        return
      }
      actionExecuted = true;
      let rst = await reqIdentity().registerId();
      // rst.resultCode = 0
      actionExecuted = false;
      Logger.debug('registerid res: ' + JSON.stringify(rst))
      res.json( rst )
    } catch (err) {
      Logger.error("error occured: " + err)
      res.json({ resultCode: 500, result: "internal error:"+err.message })
      actionExecuted = false;
    }
  })

  app.post('/genissuer', async function (req, res) {
  try {
      Logger.debug('genissuer req: ' + JSON.stringify(req.body))

      let rst = await reqIdentity().generateIssuer();
      //rst.resultCode = 0 
      Logger.debug('genissuer res: ' + JSON.stringify(rst))
      res.json( rst )

    } catch (err) {
      Logger.error("error occured: " + err)
      res.json({ resultCode: 500, result: "internal error:"+err.message })
    }
  })

  app.post('/addattr', async function (req, res) {
  try {
        Logger.debug('addattr req: ' + JSON.stringify(req.body))
        let id = req.body.id
        let attr = req.body.attr

        if ( !id  ) {
          Logger.error('register Id is empty.')
          res.json({ resultCode: 500, result: "internal error:"+err.message })
          return;
        }
        if ( !attr ) {
          Logger.error('attribute is empty.')
          res.json({ resultCode: 500, result: "internal error:"+err.message })
          return;
        }

        let rst = await reqIdentity().addAttribute(id, attr);
        //rst.resultCode = 0 
        Logger.debug('addattr res: ' + JSON.stringify(rst))
        res.json( rst )

      } catch (err) {
        Logger.error("error occured: " + err)
        res.json({ resultCode: 500, result: "internal error:"+err.message })
      }
  })

  app.post('/approve', async function (req, res) {
  try {
      Logger.debug('approve req: ' + JSON.stringify(req.body))
      let id = req.body.id
      let attrId = req.body.attrId

      if ( !id  ) {
        Logger.error('register Id is empty.')
        return;
      }
      if ( !attrId ) {
        Logger.error('attribute id is empty.')
        return;
      }

      let rst = await reqIdentity().approveAttribute(id, attrId);
      //rst.resultCode = 0 
      Logger.debug('approve res: ' + JSON.stringify(rst))
      res.json( rst )
    } catch (err) {
      Logger.error("error occured: " + err)
      res.json({ resultCode: 500, result: "internal error:"+err.message })
    }
  })

  app.post('/verify', async function (req, res) {
  try {
      Logger.debug('verify req: ' + JSON.stringify(req.body))
      let id = req.body.id
      let attr = req.body.attr


      if ( !id  ) {
        Logger.error('register Id is empty.')
        return;
      }
      if ( !attr ) {
        Logger.error('attribute is empty.')
        return;
      }

      let rst = await reqIdentity().verify(id, attr);
      rst.resultCode = 0 
      Logger.debug('verify res: ' + JSON.stringify(rst))
      res.json( rst )
    } catch (err) {
      Logger.error("error occured: " + err)
      res.json({ resultCode: 500, result: "internal error:"+err.message })
    }
  })

  app.listen(_httpPort, () => {
    Logger.debug('http server listening on port ' + _httpPort);
  });

}

