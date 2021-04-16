pragma solidity ^0.4.24;

import "openzeppelin-solidity/contracts/ownership/Ownable.sol";
import "./BConstant.sol";

contract BMSPInterface {
  function verifyRole(address target, uint8 role, bool isRevoke) view public returns (bool);
}

contract BFactory is BConstant,Ownable {

  BMSPInterface BMSPCon;
  address mspAddr;
  address channelAddr;
  
  function setOnlyContracts(address _mspAddr, address _channelAddr) external onlyOwner {
    mspAddr = _mspAddr;
    channelAddr = _channelAddr; 
  }

  function setBMSPCon(address _address) external onlyOwner {
    BMSPCon = BMSPInterface(_address);
  }

  uint internal nonce = 0;
  enum TokenState {
    invalid,
    valid,
    in_progress
  }

  enum RequestState {
    request,
    approved,
    denied
  }

  struct _CID {
    address owner;
    bool validity;
  }

  struct _Data {
    address owner;
    uint cid;
    string c;
    string v;
    uint fee;
    uint BN;
    bool validity;
  }

  struct _DataAttr {
    string info;
    address[] TD;
    address[] TU;    
    uint[] ad;
  }

  struct _File {
    uint dataId;
    uint chunks;
  }

  struct _Product {
    address owner;
    uint dataId;
    uint price;
    address[] TU;
    bool validity;
  }

  struct _Token {
    address owner;
    uint productId;
    TokenState state;
  }

  mapping (uint => _CID) internal C2O;                           //  cid => cid structure
  mapping (uint => _Data) internal _Ds;                            //  data id => data structure
  mapping (uint => _DataAttr) internal _DAs;                       //  data id => data attribute structure
  mapping (uint => _File) internal _Fs;                            //  file id => file structure
  mapping (uint => _Product) internal _Ps;                         //  product id => product structure
  mapping (uint => _Token) internal _Ts;                           //  token id => token structure
  mapping (uint => mapping(uint8 => uint[])) internal I2I;         //  data id => file ids or data id => product ids
  mapping (address => mapping(uint8 => uint[])) internal O2I;      //  owner => ids (0: data id, 1: product id, 2: token id)
  mapping (address => mapping(uint => uint[])) internal OC2T;     //  owner & cid => token ids

  event NewID(uint flag, uint Id);                                 //  0: cid, 1: data id, 2: file id, 3: product id, 4: token id, 5: channel id,   6: modify data id 

  uint[] public _DIDs;

  modifier onlyRoleOf(address target, uint8 role, bool isRevoke) {
    require(target != address(0));
    if(!isRevoke) {
      require(BMSPCon.verifyRole(target, role, false));
    } else {
      require(!BMSPCon.verifyRole(target, role, true));
    }
    _;
  }

  function checkAddr(address[] src, address target) pure internal returns (bool) {
    bool res = false;
    for (uint i = 0; i < src.length; i++) {
      if (src[i] == target) {
        res = true;
        break;
      }
    }
    return res;
  }
}
