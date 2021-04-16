const Succeeded = {succeed: true};
const Faild = {succeed: false};

function printFailJSON() {
    console.log(JSON.stringify(Faild, null, 4 ));
}

function printFailStrJSON(str) {
    if (!str || typeof str !== 'string') {
        printFailJSON();
    } else {
        var printedObj = {
            ...Faild,
            error: str
        }
        console.log(JSON.stringify(printedObj, null, 4 ));
    }
}

function printSucceedJSON() {
    console.log(JSON.stringify(Succeeded, null, 4 ));
}

function printSucceedObjJSON(Obj) {
    if (!Obj || typeof Obj !== 'object') {
        printFailJSON();
    } else {
        var printedObj = {
            ...Succeeded,
            ...Obj
        }
        console.log(JSON.stringify(printedObj, null, 4 ));
    }
}

function toUint8Array(hexString) {
    return new Uint8Array(hexString.match(/[\da-f]{2}/gi).map(byte => parseInt(byte, 16)));
}

function toHexString(bytes){
    return bytes.reduce((str, byte) => str + byte.toString(16).padStart(2, '0'), '');
}

module.exports = {
    printFailJSON,
    printFailStrJSON,
    printSucceedJSON,
    printSucceedObjJSON,
    toUint8Array,
    toHexString
}