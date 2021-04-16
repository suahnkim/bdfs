const cmd = require('commander');
const fs = require("fs");
// const _logger = require('./logger');
// var logger = _logger.createLogger('none');
var Identity = null;

var logger ;
var logFolder = "./logs"
var Log4JS = require('log4js');
if( !fs.existsSync( logFolder ) ) {
  fs.mkdirSync(logFolder)
  }

Log4JS.configure({
  appenders: { Identitier: { type: 'file', filename: logFolder + '/identity.log', maxLogSize: 524288, backups: 2, compress: true }
   },
  categories: { default: { appenders: ['Identitier'], level: 'error' } }
})
logger = Log4JS.getLogger('Identitier')
logger.level = "debug"

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



cmd
    .version('0.1.0')
    .description('CLI tools for blockchain identity');


cmd
    .option('-l, --loglevel <level>', 'set loglevel as error, info, debug, or error ex) -l debug', (value, prev)=>{
        if ( value.toLowerCase() === 'debug' || value.toLowerCase() === 'info' || value.toLowerCase() === 'error' ) {
            logger = _logger.createLogger(value.toLowerCase()); // error:0 < warn:1 < info:2 < debug:4
        } // else then leave loglevel none
        logger.info(`Log level is set to '${value}'`);
    })

cmd
    .option('-a, --account <keypair>', 'set an account(key pair) to be used ex) -a 0x0...123,0x01...567', (keypair, prev)=>{
        var arrKeypair = keypair.split(',');
        if (arrKeypair.length !== 2 ) {
            printFailStrJSON('account format is not correct');
            process.exit();
        }
        var ret = reqIdentity().setAccount(arrKeypair[0], arrKeypair[1]);
        if (!ret) {
            printFailStrJSON('not valid account');
            process.exit();
        }
        logger.info(`An account is successfully set: ${arrKeypair[0]}, ${arrKeypair[1]}`)
    })

cmd
    .command('registerid')
    .alias('r')
    .description('register an identity ex) registerid')
    .action((publicKey)=>{
        actionExecuted = true;
        logger.info('Do registerid action');
        reqIdentity().registerId();
    });

cmd
    .command('genissuer')
    .alias('g')
    .description('generate issuer ex) genissuer')
    .action((publicKey)=>{
        actionExecuted = true;
        logger.info('Do genissuer action');
        reqIdentity().generateIssuer();
    });

/*
cmd
    .command('getkey [id]')
    .alias('k')
    .description('get key of identity ex) getkey 0x00...123')
    .action((id)=>{
        actionExecuted = true;
        if ( !id ) {
            logger.error('getkey command needs <id>')
            printFailStrJSON('getclaim command needs <id>');
            return;
        }
        logger.info('Do getkey action');
        reqIdentity().getKey(id);
    });
*/

cmd
    .command('addattr [id] [attribute]')
    .alias('a')
    .description('add attribute of identity ex) addattr 0x00...123 7')
    .action((id, attr)=>{
        actionExecuted = true;
        if ( !id || !attr ) {
            logger.error('addattr command needs <id> <attribute>')
            printFailStrJSON('addattr command needs <id> <attribute>');
            return;
        }
        logger.info('Do addattr action');
        reqIdentity().addAttribute(id, attr);
    });

cmd
    .command('approve [id] [attributeId]')
    .alias('p')
    .description('approve attribute of identity ex) addattr [id] [attributeId]')
    .action((id, attrId)=>{
        actionExecuted = true;
        if ( !id || !attrId ) {
            logger.error('addattr command needs <id> <attributeId>')
            printFailStrJSON('addattr command needs <id> <attributeId>');
            return;
        }
        logger.info('Do approve action');
        reqIdentity().approveAttribute(id, attrId);
    });

cmd
    .command('verify [id] [attribute]')
    .alias('v')
    .description('verify attribute ex) verify 0x00...123 7')
    .action((id, attr)=>{
        actionExecuted = true;
        if ( !id || !attr ) {
            logger.error('verify command needs <id> <attribute>')
            printFailStrJSON('verify command needs <id> <attribute>');
            return;
        }
        logger.info(`Do verify action with [${id}] and [${attr}]`);
        reqIdentity().verify(id, attr);
    });

cmd.parse(process.argv);

if ( cmd.args.length === 0 || !actionExecuted) {
    logger.debug('no input of command');
    printFailStrJSON('no input of command');
}
