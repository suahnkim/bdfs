pragma solidity ^0.4.24;

import "openzeppelin-solidity/contracts/ownership/Ownable.sol";
import "./BTokenInterface.sol";

contract BProduct is Ownable {
  BTokenInterface BTokenCon;

  address public owner;


  struct _RData {
    address owner; 
    string c;
    string v;
    bool ser_receive;
    bool sto_receive; 
  }
  event NewID(uint flag, uint Id);   
  
  mapping(address => bool) private onlyContracts;
  mapping (uint => _RData) internal _RD;

  constructor () public {
    owner = msg.sender;
  }

  uint[] public _DIDs;

  function getDataLength() view public returns (uint) {
    return _DIDs.length;
  }


  // function setOnlyContract(address _address) external onlyOwner {
  //   onlyContracts[_address] = true;
  // }
  function setOutsideContracts(address _address) external onlyOwner {
    BTokenCon = BTokenInterface(_address);
  }

  function setReceive(string ccid, string version, bool sflag, bool tflag) public  {
    uint dataId = uint(keccak256(abi.encodePacked(ccid, version)));

    _RD[dataId] = _RData(msg.sender, ccid, version, sflag, tflag);
    _DIDs.push(dataId);
  }

  function setSearchReceive(string ccid, string version, bool sflag ) public  {
    uint dataId = uint(keccak256(abi.encodePacked(ccid, version)));

    _RD[dataId].ser_receive = sflag;
  }
  function setStorageReceive(string ccid, string version, bool tflag ) public  {
    uint dataId = uint(keccak256(abi.encodePacked(ccid, version)));

    _RD[dataId].sto_receive = tflag;
    if( tflag ) {
      BTokenCon.setStorageReceive(dataId);
    }
  }

  function isReceiveSrchNode(string ccid, string version) view public returns (bool) {
    uint dataId = uint(keccak256(abi.encodePacked(ccid, version)));
    return _RD[dataId].ser_receive ;
  }
  function isReceiveStoreNode(string ccid, string version) view public returns (bool) {
    uint dataId = uint(keccak256(abi.encodePacked(ccid, version)));
    return _RD[dataId].sto_receive ;
  }
  function isReceive(string ccid, string version) view public returns (string, string, bool, bool) {
    uint dataId = uint(keccak256(abi.encodePacked(ccid, version)));
    return ( _RD[dataId].c, _RD[dataId].v, _RD[dataId].ser_receive, _RD[dataId].sto_receive) ;
  }
  function isReceiveD(uint dataId) view public returns (string, string, bool, bool) { 
    return ( _RD[dataId].c, _RD[dataId].v, _RD[dataId].ser_receive, _RD[dataId].sto_receive) ;
  }
}
