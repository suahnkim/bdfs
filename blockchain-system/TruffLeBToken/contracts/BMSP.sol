pragma solidity ^0.4.24;

import "openzeppelin-solidity/contracts/ownership/Ownable.sol";
import "./BConstant.sol";
import "./BTokenInterface.sol";

contract BMSP is BConstant, Ownable {
  BTokenInterface BTokenCon;

  struct _RequestForm {
    address requester;
    uint8 role;
  }

  event RevokeID(uint flag, uint Id);
  mapping(address => bool) private onlyContracts;
  mapping(uint => _RequestForm) private Requests;
  mapping(address => uint8) internal Roles;
  mapping(address => uint8) internal Revokes;

  uint private nextIndex = 0;
  uint private requestLength = 0;

  modifier onlyAuthorizedOf {
    require(msg.sender == owner || onlyContracts[msg.sender]);
    _;
  }

  function setOnlyContract(address _address) external onlyOwner {
    onlyContracts[_address] = true;
  }

  function setOutsideContracts(address _address) external onlyOwner {
    BTokenCon = BTokenInterface(_address);
  }

  //function appointManager(address target) public onlyOwner {
  function appointManager(address target) public {
    Roles[target] |= M;
  }

  function requestEnroll(uint8 role) public {
    require(role < 16);
    Requests[requestLength] = _RequestForm(msg.sender, role);
    requestLength++;
  }

  //function getNextIndex() view public onlyOwner returns (uint) {
  function getNextIndex() view public returns (uint) {
    return nextIndex;
  }

  //function getRequestLength() view public onlyOwner returns (uint) {
  function getRequestLength() view public  returns (uint) {
    return requestLength;
  }

  //function getRequestDetails(uint requestId) view public onlyOwner returns (address requester, uint8 role) {
  function getRequestDetails(uint requestId) view public returns (address requester, uint8 role) {
    return (Requests[requestId].requester, uint8(Requests[requestId].role));
  }

  //function approveRole(bool[] approve) public onlyOwner {
  function approveRole(bool[] approve) public  {
    require(nextIndex + approve.length <= requestLength);
    for(uint i = 0; i < approve.length; i++) {
      if(approve[i] == true) {
         Roles[Requests[nextIndex + i].requester] |= Requests[nextIndex + i].role;
      }
    }
    nextIndex += approve.length;
  }

  function _revokeRole(address target, uint8 role) internal {
    if(verifyRole(target, role, false)) {
        Roles[target] ^= role;
    }
    Revokes[target] |= role;
  }

  function verifyRole(address target, uint8 role, bool isRevoke) view public returns (bool) {
    require(target != address(0));
    if(!isRevoke) {
      return Roles[target] ^ (Roles[target] | role) == 0;
    } else {
      return Revokes[target] ^ (Revokes[target] | role) == 0;
    }
  }

  function revokeData(uint dataId, bool deleteAll)
  public
  {
    require(verifyRole(msg.sender, M, false));
    BTokenCon.validityByRevoke(dataId, true);
    if(deleteAll) {
      uint[] memory productList = BTokenCon.getProductListForRevoke(dataId);
      for(uint i = 0; i < productList.length; i++) {
        revokeProduct(productList[i]);
      }
    }
    emit RevokeID(0, dataId);
  }

  function revokeProduct(uint productId)
  public
  {
    require(verifyRole(msg.sender, M, false));
    BTokenCon.validityByRevoke(productId, false);
    emit RevokeID(2, productId);
  }

  function revokeUser(address user, uint8 role, bool dD, bool dP)
  public
  {
    require(verifyRole(msg.sender, M, false));
    _revokeRole(user, role);
    if(dD && role == CP) {
      uint[] memory dataList = BTokenCon.getListForRevoke(user, 0);
      for(uint i = 0; i < dataList.length; i++) {
        revokeData(dataList[i], dP);
      }
    }
    if(dP && role == D) {
      uint[] memory productList = BTokenCon.getListForRevoke(user, 1);
      for(i = 0; i < productList.length; i++) {
        revokeProduct(productList[i]);
      }
    }
  }
}
