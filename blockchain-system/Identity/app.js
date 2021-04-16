const path = require('path');
const fs = require('fs');

// to compile contract
const solc = require('solc');

// to deploy contracts to Loom(extdev-plasma-us1)
const Web3 = require('web3');
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

// crypto-library
const nacl = require("tweetnacl")

var solcOpts = {
    language: 'Solidity',
    settings: {
        metadata: { useLiteralContent: true },
        outputSelection: {
            '*': {
                '*': ['abi', 'evm.bytecode.object']
            }
        }
    }
}

function findImportsPath(prefix) {
    return function findImports(_path) {
        try {
            var importPath = path.resolve(prefix, _path);
            console.log( ' - import .sol: ' + importPath);
            return {
                contents: fs.readFileSync(importPath).toString()
            }
        } catch (e) {
            return { error: ' - error: File not found' }
        }
    }
}

// compile contract using solc
// contracts should be specified as relative directory path from current directory or absoulte prefix path.
// contractName is specified without .sol file extension
//!! Link is not considered here. May be neccessary to add linking functionality.
function compileContract(contractRootPath, contractName, outputJsDir )
{
    console.log( `>> compile contract: ${contractName}.sol`)
    const contractPath = path.resolve(contractRootPath, contractName+'.sol');
    try {
        if ( fs.existsSync(contractPath) ) {
            var sources = {
                [contractName]: {
                content: fs.readFileSync(contractPath).toString()
                }
            }
            var compileOpts = JSON.stringify({ ...solcOpts, sources });
            var rawOutput = solc.compileStandardWrapper(
                compileOpts,
                findImportsPath(contractRootPath)
            )
            var output = JSON.parse(rawOutput);
        }else{
            console.error( ' - error: File not found: ' + contractPath  );    
        }
    }catch(err) {
        console.error( err );
    }
    var { abi, evm: { bytecode } } = output.contracts[contractName][contractName];

    contractJs = path.resolve(outputJsDir, contractName + '.js');
    console.log( ' - write contract file to: ' + contractJs);
    fs.writeFileSync(
        contractJs,
        'module.exports = ' + JSON.stringify(
            {
            abi,
            data: bytecode.object
            },
            null,
            4
        )
    )
}

function compileContracts(contractsRootPath, contractNames, outputJsDir) {
    console.log( '>> Compile contracts: ' + contractNames );
    contractNames.forEach((contract)=>{
        compileContract(contractsRootPath, contract, outputJsDir);
    });    
}

function toUint8Array(hexString) {
    return new Uint8Array(hexString.match(/[\da-f]{2}/gi).map(byte => parseInt(byte, 16)));
}

function toHexString(bytes){
    return bytes.reduce((str, byte) => str + byte.toString(16).padStart(2, '0'), '');
}

// private key: 
// public key: 83c04fd65a0c0ab40339f9f68c51bdedc5057514c993b34212c45c8177c9d3cc

function generate_account() {
    const ExtdevPrivateKey = CryptoUtils.generatePrivateKey();
    const ExtdevPubLicKey = CryptoUtils.publicKeyFromPrivateKey(ExtdevPrivateKey);
    return [ExtdevPrivateKey, ExtdevPubLicKey];
}

async function deployContract(contractJsDir, contractName ) {
    contractJs = path.resolve(contractJsDir, contractName + '.js');
    console.log( fs.readFileSync(contractJs).toString() );
}

async function deployContracts(contractJsDir, contracts) {
    contracts.forEach((contract)=>{
        console.log( '>> deploy contract: ' + contract + '.js');
        deployContract(contractJsDir, contract);
    });    
}

// [] Configuration Flag
// o General
var CompileFlag = false;
var GenkeyFlag = false;
// o ClaimHolder
var ClaimHolderAddr = '0x18C11F18a3E6712244467Ac53a52f03C56749B91';
var DeployClaimHolderFlag = false;
var CH_CallRemoveKeyFlag = false;
var CH_CallAddKeyFlag = false;
// o ClaimIssuer
var CI_CallAddClaimFlag = false;
// o ClaimVerifier
var ClaimVerifierAddr = '0x1b4f45bA3450F963A52298d72a3507356dac6A8d';
var DeployClaimVeriferFlag = false;
// o Preset of LOOM account
var Loom_privkey = toUint8Array('9ae652e9c6d8b5ea7daa7ac6968f3adcca3dc8783814e4b0d1b6ff3df3c25bc883c04fd65a0c0ab40339f9f68c51bdedc5057514c993b34212c45c8177c9d3cc');
var Loom_pubkey = toUint8Array('83c04fd65a0c0ab40339f9f68c51bdedc5057514c993b34212c45c8177c9d3cc');


var CLient;
var web3;
var ChainId;
var log = true;

(async ()=>{
    var identityContractsDir = './contracts/';
    var identityContractsJsDir = './src/contracts/';
    var identityContracts = ['ClaimHolder','ClaimVerifier'];

    //compile contracts
    if (CompileFlag) {
        compileContracts(identityContractsDir, identityContracts, identityContractsJsDir);
    }

    //generate Loom account keys or use default Loom account keys
    if (GenkeyFlag) {
        [Loom_privkey, Loom_pubkey] = generate_account();
    } 
    console.log('>> private key: ' + toHexString(Loom_privkey));
    console.log('>> public key: ' + toHexString(Loom_pubkey));

    //create LOOM Client and Web3 instance
    CLient = new Client(
        'extdev-plasma-us1',
        'ws://extdev-plasma-us1.dappchains.com:80/websocket',
        'ws://extdev-plasma-us1.dappchains.com:80/queryws'
    );
    CLient.on('error', err=>{
        if ( typeof err === 'object' && err !== null ) {
            console.log('>> Loom client error: ' + JSON.stringify(err, null, 4 ))
        }
        CLient.disconnect();
    });

    
    process.on('unhandledRejection', error => {
        // Prints "unhandledRejection woops!"
        console.log('>> UnhandledRejection is globally caught');
        console.log(` - error: ${error}`)
        CLient.disconnect();
    });
    
    


    CLient.txMiddleware = [
        new NonceTxMiddleware(Loom_pubkey, CLient),
        new SignedTxMiddleware(Loom_privkey)
    ];
    //create Web3 instance
    web3 = new Web3(new LoomProvider(CLient, Loom_privkey));

    //get Loom network chain ID
    ChainId = await web3.eth.net.getId();
    console.log('>> chainID: ' + ChainId);

    //get Loom account from Loom_pubkey
    var account = LocalAddress.fromPublicKey(Loom_pubkey).toString();
    console.log('>> account: ' + account);


    //set contract variables
    var contractJsFile;
    var contractJs;
    var Contract;
    var contract;


    //[] ClaimHolder 
    //o load compiled ClaimHolder contract
    contractJsFile = path.resolve(identityContractsJsDir, identityContracts[0] + '.js');
    contractJs = require(contractJsFile);
    Contract = new web3.eth.Contract(contractJs.abi);


    //o deploy ClaimHolder
    if ( DeployClaimHolderFlag ) {
        console.log( '>> deploy ClaimHolder contract')
        //1) encode ABI with compiled contract and contract's constructor parameters
        console.log( ' - encodeABI' )
        try {
            var data = await Contract.deploy({
                data: '0x' + contractJs.data,
                arguments: []
            }).encodeABI();
        }
        catch (err) {
            console.log( ' - encodeABI error: ' + e )
        }

        //2) send encoded ABI as a transaction
        console.log( ' - sendTransaction')
        await web3.eth
        .sendTransaction({
            from: account, 
            data,
            value: 0,
            gas: 0,
            ChainId
        })
        .once('transactionHash', hash => {
            if (log) {
                console.log(' - Transaction Hash', hash)
            }
        })
        .once('receipt', receipt => {
            ClaimHolderAddr = receipt.contractAddress
            if (log) {
                console.log(
                    ` - Deployed ${identityContracts[0]} to ${receipt.contractAddress} (${
                    receipt.cumulativeGasUsed
                    } gas used)`
                )
            }
        })
        .catch('error', err => {
            console.log(' - error: ' + err)
            resolve()
        })
    }
    
    //o get deployed ClaimHolder
    console.log('>> use KeyHolder as ClaimHolder contract: ' + ClaimHolderAddr);
    contract = new web3.eth.Contract(contractJs.abi, ClaimHolderAddr);

    
    var purpose = 3; // MarkAny Media Blockchain : 3
    var keyType = 3; // custom key type ( NACL's Ed25519(EdDSA) ) : 3
    var key = Loom_pubkey;
    
    //o call ClaimHolder.removeKey()
    if ( CH_CallRemoveKeyFlag ) {
        console.log(' - call removeKey()');

        await contract.methods.removeKey(key).send({
            gas: 0,
            from: account
            })
            .once('transactionHash', hash => {
                if (log) {
                    console.log('Transaction Hash', hash)
                }
            })
            .once('receipt', receipt => {
                if (log) {
                    console.log(
                        ` - Call ${receipt.contractAddress}.removeKey() (${
                        receipt.cumulativeGasUsed
                        } gas used)`
                    )
                    console.log( receipt );
                }
            })
            .catch('error', err => {
                console.log(' - ' + err)
                resolve()
            })
    }

    //o call ClaimHolder.addKey()
    if ( CH_CallAddKeyFlag ) {
        console.log(' - call addKey()');

        await contract.methods.addKey(key, purpose, keyType).send({
            gas: 0,
            from: account
            })
            .once('transactionHash', hash => {
                if (log) {
                    console.log('Transaction Hash', hash)
                }
            })
            .once('receipt', receipt => {
                if (log) {
                    console.log(
                        ` - Call ${receipt.contractAddress}.addKey() (${
                        receipt.cumulativeGasUsed
                        } gas used)`
                    )
                    console.log( receipt );
                }
            })
            .catch('error', err => {
                console.log(' - ' + err)
                resolve()
            })
    }
    key = Loom_pubkey;

    //o call ClaimHolder.getKey()
    console.log(' - call getKey()');
    var callRet  = await contract.methods.getKey(key).call({
            gas: 0,
            from: account })
        .catch(err => {
            console.log(' - error: ' + err)
        })
    
    if ( callRet !== null && typeof callRet === 'object'  ) {
        strCallRet = JSON.stringify(callRet, null, 4 );
    }
    else {
        strCallRet = 'undefined';
    }
    console.log( ` - Return of ClaimHolder.getKey(): ${strCallRet}` );

    //o call ClaimHolder.getKeysByPurpose()
    console.log(' - call getKeysByPurpose()');
    var keyPurpose = 3;
    callRet  = await contract.methods.getKeysByPurpose(keyPurpose).call({
            gas: 0,
            from: account })
        .catch(err => {
            console.log(' - error: ' + err)
        })
    
    if ( callRet !== null && typeof callRet === 'object'  ) {
        strCallRet = JSON.stringify(callRet, null, 4 );
    }
    else {
        strCallRet = 'undefined';
    }
    console.log( ` - Return of ClaimHolder.getKeysByPurpose(): ${strCallRet}` );
    var keyP3str = callRet[0];


    //[] ClaimHolder who holds idenitty
    console.log('>> use ClaimHolder as ClaimHolder contract: ' + ClaimHolderAddr);
    if ( CI_CallAddClaimFlag ) {
        let targetIdentity = ClaimHolderAddr;
        let claimType = 7; // verfied
        let scheme = 1; // ECDSA
        let claimIssuer = ClaimHolderAddr;
        let data = "그는 의사다";

        let targetIdentityBuf = Buffer.from(targetIdentity.slice(2), 'hex')
        let claimTypeBuf = Buffer.from(claimType.toString())
        let hexedData = web3.utils.utf8ToHex(data)
        let dataBuf = Buffer.from(hexedData.slice(2), 'hex')
        let signedMsg = Buffer.concat([targetIdentityBuf, claimTypeBuf, dataBuf]);
        console.log(` - signedMsg=0x${signedMsg.toString('hex')}`)
        let signatureArray = nacl.sign.detached(signedMsg, Loom_privkey);
        let signature = '0x' + toHexString(signatureArray);
        
        /*
        var signature = await web3.eth.sign(
            web3.utils.soliditySha3(targetIdentity, claimType, hexedData),
            account
        )
        */
        var uri = "http://";

        console.log(` - target identity contract: ${targetIdentity}`)
        console.log(` - Generate addClaim abi(claimType=${claimType},\n   scheme=${scheme},\n   claimIssuer=${claimIssuer},\n   signature=${signature},\n   data=${hexedData},\n   uri=${uri})`);
        var UserIdentity = new web3.eth.Contract(contractJs.abi, targetIdentity);
        var abi = await UserIdentity.methods
            .addClaim(claimType, scheme, claimIssuer, signature, hexedData, uri)
            .encodeABI()
        
        await UserIdentity.methods.execute(targetIdentity, 0, abi).send({
            gas: 0,
            from: account
        })
        .once('transactionHash', hash => {
            if (log) {
                console.log('Transaction Hash', hash)
            }
        })
        .once('receipt', receipt => {
            if (log) {
                console.log(
                ` - Call ${receipt.contractAddress}.execute() (${
                receipt.cumulativeGasUsed
                } gas used)`
                )
                console.log( receipt );
            }
        })
        .catch('error', err => {
            console.log(' - ' + err)
            resolve()
        })
            
    }

    //o getClaimIdsByType
    console.log(' - call getClaimIdsByType()');
    var queryClaimType = 7;
    callRet  = await contract.methods.getClaimIdsByType(queryClaimType).call({
            gas: 0,
            from: account })
        .catch(err => {
            console.log(' - error: ' + err)
        })

    if ( callRet !== null && typeof callRet === 'object'  ) {
        strCallRet = JSON.stringify(callRet, null, 4 );
    }
    else {
        strCallRet = 'undefined';
    }
    console.log( ` - Return of getClaimIdsByType(): ${strCallRet}` );
    
    //o getClaim
    //o getClaimIdsByType
    console.log(' - call getClaim()');
    var claimIndex = 0;
    var claim;
    var strClaim;
    var claimId = toUint8Array(callRet[claimIndex]);
    callRet  = await contract.methods.getClaim(claimId).call({
            gas: 0,
            from: account })
        .catch(err => {
            console.log(' - error: ' + err)
        })
    var claimData = '';
    if ( callRet !== null && typeof callRet === 'object'  ) {
        claim = callRet;
        claimData = web3.utils.hexToUtf8(claim.data);
        strClaim = JSON.stringify(claim, null, 4 );
    }
    else {
        strClaim = 'undefined';
    }
    console.log( ` - Return of getClaim(): ${strClaim}` );
    console.log( `  . claim.data: ${claimData}`)


    //o verify claim
    console.log( ' - verifying the above claim')
    var verifyingIdenity = ClaimHolderAddr;
    if ( claim !== null && typeof claim === 'object'  ) {
        if ( claim.scheme === '1') {
            claimData
            console.log( `  . claim { type: ${claim.claimType},\n           data: ${claim.data},\n           signature: ${claim.signature},\n           issuer: ${claim.issuer},\n           }`)
            let targetIdentity = ClaimHolderAddr;
            let claimType = claim.claimType; // verfied
            let claimIssuer = claim.issuer;
            let data = "그는 의사다";

            let targetIdentityBuf = Buffer.from(targetIdentity.slice(2), 'hex')
            let issuerPubKeyBuf = Buffer.from(keyP3str.slice(2), 'hex') // todo: claim issuer의 컨트렉으로 부터 3번 목적의 키를 받아서 사용 
            let claimTypeBuf = Buffer.from(claim.claimType)
            let dataBuf = Buffer.from(claim.data.slice(2), 'hex') //hex data임으로 앞의 0x를 slice(2)를 이용해 제거
            let msgBuf = Buffer.concat([targetIdentityBuf, claimTypeBuf, dataBuf]);
            let sigBuf = Buffer.from(claim.signature.slice(2), 'hex')
            console.log(`  . signature=${claim.signature}`)
            console.log(`  . msg=0x${msgBuf.toString('hex')}` )
            if ( nacl.sign.detached.verify(msgBuf, sigBuf, issuerPubKeyBuf) ) {
                if ( LocalAddress.fromPublicKey(issuerPubKeyBuf).toString() == account ) {
                    console.log('  . claim is valid.')
                }
                else {
                    console.log('  . claim is valid, but trusted issuer doesn\'t match this account.')
                }
                
            } else {
                console.log('  . claim is not valid')
            }
            
            /*
            var hashedMessage = web3.utils.soliditySha3(
                verifyingIdenity,
                claim.claimType,
                claim.data
            )
            // make it as following
            // "\x19Ethereum Signed Message:\n" + message.length + message"
            var prefixedMsg = web3.eth.accounts.hashMessage(hashedMessage)
            var dataBuf = toBuffer(prefixedMsg)
            var sig = fromRpcSig(claim.signature)
            var recovered = ecrecover(dataBuf, sig.v, sig.r, sig.s)
            var recoveredKeyBuf = pubToAddress(recovered)
            var recoveredKey = bufferToHex(recoveredKeyBuf)
            var hashedRecovered = web3.utils.soliditySha3(recoveredKey)
            console.log( `  . ${bufferToHex(recovered)}`)
            var hashedRecovered = LocalAddress.fromPublicKey(recovered).toString();
            console.log( `  . recoveredkey: ${hashedRecovered}`)
            */
        } else {
            console.log( `  . scheme(${claim.scheme}) not supported scheme`)
        }
    }

    //o sign and verify test
    var t_msg = toBuffer("hello");
    var m_msg = toBuffer("hello");
    var t_signature = nacl.sign.detached(t_msg, Loom_privkey);
    
    if ( nacl.sign.detached.verify(m_msg, t_signature, Loom_pubkey) ) {
        console.log( ">> verification: true");
    } else {
        console.log( ">> verification: false");
    }
    

    //[] ClaimVerifer
    //o load compiled ClaimVerifer contract
    contractJsFile = path.resolve(identityContractsJsDir, identityContracts[1] + '.js');
    contractJs = require(contractJsFile);
    Contract = new web3.eth.Contract(contractJs.abi);


    //o deploy ClaimVerifier
    if ( DeployClaimVeriferFlag ) {
        console.log( '>> deploy ClaimVerifier contract')
        //1) encode ABI with compiled contract and contract's constructor parameters
        console.log( ' - encodeABI' )

        try {
            var data = await Contract.deploy({
                data: '0x' + contractJs.data,
                arguments: [ClaimHolderAddr] // constructor paramenters
            }).encodeABI();    
        }
        catch ( e ){
            console.log( ' - encodeABI error: ' + e )
        }

        //2) send encoded ABI as a transaction
        console.log( ' - sendTransaction')
        await web3.eth
        .sendTransaction({
            from: account, 
            data,
            value: 0,
            gas: 0,
            ChainId
        })
        .once(' - transactionHash', hash => {
            if (log) {
                console.log('Transaction Hash', hash)
            }
        })
        .once('receipt', receipt => {
            ClaimVerifierAddr = receipt.contractAddress
            if (log) {
                console.log(
                    ` - Deployed ${identityContracts[1]} to ${receipt.contractAddress} (${
                    receipt.cumulativeGasUsed
                    } gas used)`
                )
            }
        })
        .catch('error', err => {
            console.log( ' - error: ' + err)
            resolve()
        })
    }
    
    //o get deployed ClaimVerifier
    contract = new web3.eth.Contract(contractJs.abi, ClaimVerifierAddr);



    //to close web3 connection
    CLient.disconnect();


    //await deployContracts(identityContractsJsDir, identityContracts);
})();











