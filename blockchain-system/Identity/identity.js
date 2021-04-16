const logger = require('./logger').getLogger();
logger.debug( `Start [${__filename}]`);

const fs = require("fs");
const path = require('path');
const util = require('util')

const {
    NonceTxMiddleware,
    SignedTxMiddleware,
    Client,
    LocalAddress,
    LoomProvider,
    CryptoUtils
  } = require('loom-js/dist')

const {
    fromRpcSig,
    ecrecover,
    toBuffer,
    bufferToHex,
    pubToAddress
  } = require('ethereumjs-util')

const Web3 = require('web3');

// crypto-library
const nacl = require("tweetnacl")

const {
    printFailJSON,
    printFailStrJSON,
    printSucceedJSON,
    printSucceedObjJSON,
    toUint8Array,
    toHexString
} = require('./util');

// Utility functions

////////////////////////////////////////

// Configuration
var Loom_privkey = toUint8Array('9ae652e9c6d8b5ea7daa7ac6968f3adcca3dc8783814e4b0d1b6ff3df3c25bc883c04fd65a0c0ab40339f9f68c51bdedc5057514c993b34212c45c8177c9d3cc');
var Loom_pubkey = toUint8Array('83c04fd65a0c0ab40339f9f68c51bdedc5057514c993b34212c45c8177c9d3cc');
const IdentityContractsJsDir = './src/contracts/';
const ClaimHolderContractName = 'ClaimHolder';
///////////////////////////////////

// Global variables
var CLient;
var web3;
var ChainId;
var Account;
var IsLoomClientInitialized = false;
var IsClaimHolderContractJSLoaded = false;
var ClaimHolderContractJS;
///////////////////////////////////


async function startLoomClient() {
    logger.debug( `Call ${arguments.callee.name.toString()}()`);
    if ( IsLoomClientInitialized ) {
        logger.info('LoomClient is already started');
        return;
    }

    process.on('unhandledRejection', error => {
        // Prints "unhandledRejection woops!"
        logger.error('UnhandledRejection is globally caught');
        logger.error(`${error}`)
        printFailStrJSON(error.toString());
        process.exit();
    });

    CLient = new Client(
        'extdev-plasma-us1',
        'ws://extdev-plasma-us1.dappchains.com:80/websocket',
        'ws://extdev-plasma-us1.dappchains.com:80/queryws'
    );
    CLient.on('error', err=>{
        if ( typeof err === 'object' && err !== null ) {
            logger.error('Loom client error: ' + JSON.stringify(err, null, 4 ))
        }
        //printFailStrJSON('network error');
        process.exit();
    });

    CLient.txMiddleware = [
        new NonceTxMiddleware(Loom_pubkey, CLient),
        new SignedTxMiddleware(Loom_privkey)
    ];
    //create Web3 instance
    web3 = new Web3(new LoomProvider(CLient, Loom_privkey));
    logger.info('LoomClient and Web3 are initailized')

    //get Loom network chain ID
    ChainId = await web3.eth.net.getId();
    logger.info('chainID: ' + ChainId);

    //get Loom account from Loom_pubkey
    Account = LocalAddress.fromPublicKey(Loom_pubkey).toString();
    logger.info('Account: ' + Account);
    logger.info('Pubkey : 0x' + toHexString(Loom_pubkey))

    IsLoomClientInitialized = true;
}


async function endLoomClient()
{
    logger.debug( `Call ${arguments.callee.name.toString()}()`);
    CLient.disconnect();
    IsLoomClientInitialized = false;
    logger.info('LoomClient is disconnected');
}


/// @function loadClaimHolderContractJS
async function loadClaimHolderContractJS()
{
    logger.debug( `Call ${arguments.callee.name.toString()}()`);
    if (IsClaimHolderContractJSLoaded) {
        logger.debug('ClaimHolder contract JS is already loaded');
        return ClaimHolderContractJS;
    }
    var contractJsFile = path.resolve(IdentityContractsJsDir, ClaimHolderContractName + '.js');
    ClaimHolderContractJS = require(contractJsFile);
    IsClaimHolderContractJSLoaded = true;
    logger.debug('ClaimHolder contract JS is loaded')
    return ClaimHolderContractJS;
}


/// @function getAttributeId
/// @param {string} Hex string of claim holder
/// @param {string} Hex string of claim type
async function getAttributeId(id, attributerId) {
    logger.debug( `Call ${arguments.callee.name.toString()}(${id},${attributerId})`);
    var identityContract = require(IdentityContract);
    var contract = new web3.eth.Contract(identityContract.abi, id);

    var attrIds = null;
    attrIds = await contract.methods.getClaimIdsByType(attributerId).call({
        gas: 0,
        from: Account })
    .catch(err => {
        logger.error('getClaimIdsByType: catch error '+ err);
        return null;
    })

    if ( attrIds == null || typeof attrIds !== 'object' ) {
        logger.debug(`${id}.${arguments.callee.name.toString()}(${attributerId}): return null `)
        return null;
    }

    logger.debug(`${id}.${arguments.callee.name.toString()}(${attributerId}): return ${attrIds[0]}`)
    return attrIds[0];
}


/// @function getAttrByAttrId
/// @param {string} Hex string of ID
/// @param {string} Hex string of attribute ID
async function getAttrByAttrId(id, attrId) {
    logger.debug( `Call ${arguments.callee.name.toString()}.(${id},${attrId})`);
    var attrIds = toUint8Array(attrId)
    //retrieve attribute with attrID
    var identityContract = require(IdentityContract);
    var contract = new web3.eth.Contract(identityContract.abi, id);
    var attribute = await contract.methods.getClaim(attrIds).call({
        gas: 0,
        from: Account })
    .catch(err => {
        logger.error(err);
    })
    if ( attribute && typeof attribute === 'object'  ) {
        logger.info(JSON.stringify(attribute, null, 4 ));
    }
    return attribute;
}

/// @function generateAccount
function generateAccount() {
    logger.debug( `Call ${arguments.callee.name.toString()}()`);

    var privateKeyArr = CryptoUtils.generatePrivateKey();
    var publicKeyArr = CryptoUtils.publicKeyFromPrivateKey(privateKeyArr);

    var account = LocalAddress.fromPublicKey(publicKeyArr).toString();
    var privateKey = '0x' + toHexString( privateKeyArr );
    var publicKey = '0x' + toHexString( publicKeyArr );
    return {account, privateKey, publicKey };
}

/// @function getKey
/// @param {string} Hex string of ID
async function getKey(id) {
    logger.debug( `Call ${arguments.callee.name.toString()}(${id})`);
//    await startLoomClient();

    var keyPurpose = DefaultKeyPurpose;

    logger.info("load identity contract");
    var identityContract = require(IdentityContract);
    var contract = new web3.eth.Contract(identityContract.abi, id);

    var keys  = await contract.methods.getKeysByPurpose(keyPurpose).call({
            gas: 0,
            from: Account })
        .catch(err => {
            logger.error('failed to get key: ' + err);
        })

    if ( keys && keys[0] ) {
        logger.info(`getKey(${id}) returns ${keys[0]}`);

        return keys[0];

        /*
        printSucceedObjJSON({
            id: id,
            key: keys[0]
        });
        */
    } else {
        //printFailStrJSON('failed to call getKey');
        return null;
    }

//    await endLoomClient();
}


/////////////////////////////////////////////////////////////
// declare exported APIs below

// API configuration
var IdentityContract = './identitycontract.js';
var IssuerConfigFile = './issuerConfig.json'
var DefaultKeyPurpose = 3; // MarkAny Blockchain : 3
var DefaultKeyType = 3; // custom key type ( NACL's Ed25519(EdDSA) ) : 3


/// @function generateIssuer
async function generateIssuer(){
    logger.debug( `Call ${arguments.callee.name.toString()}()`);

    // generate issuer account
    var issuerAccount = generateAccount();

    setAccount(issuerAccount.publicKey, issuerAccount.privateKey);

    await startLoomClient();

    // load contract ABI
    logger.info("load identity contract");
    var identityContract = require(IdentityContract);
    var contract = new web3.eth.Contract(identityContract.abi);

    // encode ABI
    logger.info('encode identity contract ABI');
    try {
        var data = await contract.deploy({
            data: '0x' + identityContract.data,
            arguments: []
        }).encodeABI();
    }
    catch (err) {
        logger.error( 'encodeABI error: ' + err );
        printFailStrJSON('faild to deploy identity contract');
        await endLoomClient();
        return;
    }

    // deploy ID contract
    var contractId;
    await web3.eth
        .sendTransaction({
            from: Account, 
            data,
            value: 0,
            gas: 0,
            ChainId
        })
        .once('transactionHash: ', hash => {
            logger.info('transaction hash of identity contract deployment: ' + hash)
        })
        .once('receipt', receipt => {
            contractId = receipt.contractAddress
            logger.info( `deployed ${contractId} (${
                receipt.cumulativeGasUsed
                } gas used)`);
        })
        .catch('error', err => {
            logger.error('failed to deploy identity contract: ' + err);
            resolve();
        })

    if (!contractId) {
        logger.error( 'faild to deploy identity contract');
        printFailStrJSON('faild to deploy identity contract');
        await endLoomClient();
        return;
    }

    var publicKey = Loom_pubkey; // make account publickey as signing key
    var purpose = DefaultKeyPurpose; // MarkAny Blockchain : 3
    var keyType = DefaultKeyType; // custom key type ( NACL's Ed25519(EdDSA) ) : 3

    // add account public key as signing key
    var failedToAddKey = false;
    contract.options.address = contractId;
    await contract.methods.addKey(publicKey, purpose, keyType).send({
        gas: 0,
        from: Account
        })
        .once('transactionHash', hash => {
            logger.info('transaction hash of addKey: ' + hash);
        })
        .once('receipt', receipt => {
            logger.info(
                `call ${receipt.contractAddress}.addKey() (${
                receipt.cumulativeGasUsed
                } gas used)`);
        })
        .catch('error', err => {
            logger.error('faild to call addKey: ' + err);
            failedToAddKey = true;
            resolve();
        })

    if (failedToAddKey) {
        printFailStrJSON('faild to call addKey');
        await endLoomClient();
        return;
    }

    issuerAccount = {...issuerAccount, issuerId: contractId};
    var config = JSON.stringify(issuerAccount, null, 2);
    fs.writeFileSync(IssuerConfigFile, config);
    
    printSucceedObjJSON({
        ...issuerAccount,
        issuerId: contractId,
        key: '0x'+toHexString(publicKey)
    });
    
    await endLoomClient();
}

/// @function registerId
async function registerId(){
    logger.debug( `Call ${arguments.callee.name.toString()}()`);
    await startLoomClient();

    // load contract ABI
    logger.info("load identity contract");
    var identityContract = require(IdentityContract);
    var contract = new web3.eth.Contract(identityContract.abi);

    // encode ABI
    logger.info('encode identity contract ABI');
    try {
        var data = await contract.deploy({
            data: '0x' + identityContract.data,
            arguments: []
        }).encodeABI();
    }
    catch (err) {
        logger.error( 'encodeABI error: ' + err );
        printFailStrJSON('faild to deploy identity contract');
        await endLoomClient();
        return;
    }

    // deploy ID contract
    var contractId;
    await web3.eth
        .sendTransaction({
            from: Account, 
            data,
            value: 0,
            gas: 0,
            ChainId
        })
        .once('transactionHash: ', hash => {
            logger.info('transaction hash of identity contract deployment: ' + hash)
        })
        .once('receipt', receipt => {
            contractId = receipt.contractAddress
            logger.info( `deployed ${contractId} (${
                receipt.cumulativeGasUsed
                } gas used)`);
        })
        .catch('error', err => {
            logger.error('failed to deploy identity contract: ' + err);
            resolve();
        })

    if (!contractId) {
        logger.error( 'faild to deploy identity contract');
        printFailStrJSON('faild to deploy identity contract');
        await endLoomClient();
        return;
    }

    var publicKey = Loom_pubkey; // make account publickey as signing key
    var purpose = DefaultKeyPurpose; // MarkAny Blockchain : 3
    var keyType = DefaultKeyType; // custom key type ( NACL's Ed25519(EdDSA) ) : 3

    // add account public key as signing key
    var failedToAddKey = false;
    contract.options.address = contractId;
    await contract.methods.addKey(publicKey, purpose, keyType).send({
        gas: 0,
        from: Account
        })
        .once('transactionHash', hash => {
            logger.info('transaction hash of addKey: ' + hash);
        })
        .once('receipt', receipt => {
            logger.info(
                `call ${receipt.contractAddress}.addKey() (${
                receipt.cumulativeGasUsed
                } gas used)`);
        })
        .catch('error', err => {
            logger.error('faild to call addKey: ' + err);
            failedToAddKey = true;
            resolve();
        })

    if (failedToAddKey) {
        printFailStrJSON('faild to call addKey');
        await endLoomClient();
        return;
    }
    
    printSucceedObjJSON({
        id: contractId,
        key: '0x'+toHexString(publicKey)
    });
    
    await endLoomClient();
}


async function addAttribute(targetId, attribute){
    logger.debug( `Call ${arguments.callee.name.toString()}(${targetId}, ${attribute})`);
    if (!targetId || !attribute) {
        return;
    }

    //setup issuer
    if (!fs.existsSync(IssuerConfigFile)){
        printFailStrJSON('faild to read issuer configuration.');
        return;
    }

    var issuerAccount = require(IssuerConfigFile);
    setAccount(issuerAccount.publicKey, issuerAccount.privateKey);
    await startLoomClient();

    var targetIdentity = targetId;
    var attributeType = attribute; // verfied
    var scheme = 1; // ECDSA
    var issuer = Account; // Current account is issuer
    var data = 'verifed'; // data has fixed as 'verified'. To Be Extended.
    var uri = 'http://'; // attribute can be connected to uri


    //sign (targetIdentity+attributeType+data)
    var targetIdentityBuf = Buffer.from(targetIdentity.slice(2), 'hex')
    var attributeTypeBuf = Buffer.from(attributeType.toString())
    var hexedData = web3.utils.utf8ToHex(data)
    var dataBuf = Buffer.from(hexedData.slice(2), 'hex')
    var signedMsg = Buffer.concat([targetIdentityBuf, attributeTypeBuf, dataBuf]);
    logger.info(`signed message is 0x${signedMsg.toString('hex')}`)
    var signatureArray = nacl.sign.detached(signedMsg, Loom_privkey);
    var signature = '0x' + toHexString(signatureArray);

    logger.info("load identity contract");
    var identityContract = require(IdentityContract);
    var contract = new web3.eth.Contract(identityContract.abi, targetIdentity);

    // add attribute by issuer
    var execId;
    var abi = await contract.methods
            .addClaim(attributeType, scheme, issuer, signature, hexedData, uri)
            .encodeABI();

    var failedToExecute = false;
    await contract.methods.execute(targetIdentity, 0, abi).send({
        gas: 0,
        from: Account
    })
    .once('transactionHash', hash => {
        logger.info('transaction hash of addAttribute: ' + hash);
    })
    .once('receipt', receipt => {
        logger.info(
            `call ${receipt.contractAddress}.execute() (${
            receipt.cumulativeGasUsed
            } gas used)`
            )
        logger.info(util.inspect(receipt, false, null, false));
        execId = receipt.events.ExecutionRequested.returnValues.executionId;
        logger.info('executionId: ' + execId);
    })
    .catch('error', err => {
        logger.error('faild to call addAttribute: ' + err);
        failedToExecute = true;
        resolve();
    })

    if (failedToExecute) {
        printFailStrJSON('faild to call addAttribute');
        await endLoomClient();
        return;
    }

    printSucceedObjJSON({
        targetId: targetIdentity,
        issuer: issuer,
        attribute: attributeType,
        attributeId: execId
    });

    await endLoomClient();
}


/// @function approveExecution
async function  approveAttribute(targetId, attributeId) {
    logger.debug( `Call ${arguments.callee.name.toString()}(${targetId}, ${attributeId})`);
    if (!targetId || !attributeId) {
        return;
    }

    await startLoomClient();
    
    var targetIdentity = targetId;

    logger.info("load identity contract");
    var identityContract = require(IdentityContract);
    var contract = new web3.eth.Contract(identityContract.abi, targetIdentity);

    // approve attribute by id owner
    var failedToApporve = false;
    await contract.methods.approve(attributeId, true).send({
        gas: 0,
        from: Account
    })
    .once('transactionHash', hash => {
        logger.info('transaction hash of approve: ' + hash);
    })
    .once('receipt', receipt => {
        logger.info(
            `call ${receipt.contractAddress}.approve() (${
            receipt.cumulativeGasUsed
            } gas used)`
            )
    })
    .catch('error', err => {
        logger.error('faild to call approve: ' + err);
        failedToApporve = true;
        resolve();
    })

    if (failedToApporve) {
        printFailStrJSON('faild to call apporve');
        await endLoomClient();
        return;
    }

    printSucceedObjJSON({
        targetId: targetIdentity,
        approvedAttributeId: attributeId
    });

    await endLoomClient();
}

/// @function verify
/// @param {string} Hex string of account address
/// @param {string} string of claim type
async function verify(id, attr) {
    logger.debug( `Call ${arguments.callee.name.toString()}.(${id},${attr})`);
    if ( !id ) retrun;
    await startLoomClient();

    var attributeId = await getAttributeId(id, attr);
    if (!attributeId) {
        logger.info(`Couldn't find any attribute(${attr}) from ID(${id})`)
        printFailStrJSON(`Couldn't find any attribute(${attr}) from ID(${id})`);
        await endLoomClient();
        return;
    }
    logger.info(`Found matched attribute(${attr})`);

    var attribute = await getAttrByAttrId(id, attributeId);
    
    if ( attribute && typeof attribute === 'object'  ) {
        logger.info('attribute is retrieved'); 
    } else {
        logger.info('failed to retrieve attribute');
        printFailStrJSON('failed to retrieve attribute');
        await endLoomClient();
        return
    }

    //Get issuer key 
    if (!fs.existsSync(IssuerConfigFile)){
        printFailStrJSON('faild to read issuer configuration.');
        return;
    }
    var issuerAccount = require(IssuerConfigFile);
    var issuerPublicKey = await getKey(issuerAccount.issuerId);

    //verfication
    let targetIdentityBuf = Buffer.from(id.slice(2), 'hex')
    let issuerPubKeyBuf = Buffer.from(issuerPublicKey.slice(2), 'hex') // todo: claim issuer의 컨트렉으로 부터 3번 목적의 키를 받아서 사용 
    let attrTypeBuf = Buffer.from(attribute.claimType)
    let dataBuf = Buffer.from(attribute.data.slice(2), 'hex') //hex data임으로 앞의 0x를 slice(2)를 이용해 제거
    let msgBuf = Buffer.concat([targetIdentityBuf, attrTypeBuf, dataBuf]);
    let sigBuf = Buffer.from(attribute.signature.slice(2), 'hex');
    let valid = false;
    logger.info(`signature=${attribute.signature}`)
    logger.info(`msg=0x${msgBuf.toString('hex')}` )
    if ( nacl.sign.detached.verify(msgBuf, sigBuf, issuerPubKeyBuf) ) {
        valid = true;
        logger.info('signature is valid');
    } else {
        valid = false;
        logger.info('signature is invalid');
    }

    var printAttr = {
        holder: id,
        issuer: attribute.issuer,
        type: attribute.claimType,
        data: attribute.data,
        signature: attribute.signature,
        valid
    };

    printSucceedObjJSON(printAttr);
    await endLoomClient();
}


/// @function setAccount
/// @param {string} Hex string of account's public key
/// @param {string} Hex string of account's private key
/// @return {boolean} ture or false
function setAccount(loom_pubkey, loom_privkey) {
    logger.debug( `Call ${arguments.callee.name.toString()}(${loom_pubkey},${loom_privkey})`);
    if ( typeof loom_pubkey !== 'string' || loom_pubkey.length !== 66 || typeof loom_privkey !== 'string' || loom_privkey.length !== 130 ) {
        logger.debug(`isString:${typeof loom_pubkey === 'string'}, is66:${loom_pubkey.length}, isString:${typeof loom_privkey === 'string'}, is130:${loom_privkey.length}`);
        return false;
    }
    Loom_privkey = toUint8Array(loom_privkey.slice(2));
    Loom_pubkey = toUint8Array(loom_pubkey.slice(2));

    return true;
}


module.exports = {
    generateIssuer,
//    getKey,
    registerId,
    addAttribute,
    approveAttribute,
    verify,
    setAccount
};

logger.debug( `End [${__filename}]`);