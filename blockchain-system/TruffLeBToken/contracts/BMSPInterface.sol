pragma solidity ^0.4.24;

contract BMSPInterface{

  function verifyRole(address target, uint8 role, bool isRevoke) public view returns (bool);

  function revokeUser(address user, uint8 role, bool dD, bool dP) public;

}