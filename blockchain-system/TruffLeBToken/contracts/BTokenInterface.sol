pragma solidity ^0.4.24;

contract BTokenInterface{

  // BChannel
  function getDetailsForChannel(uint uTokenId) view external returns (address, uint, uint8, uint);
  function tokenStateByChannel(uint uTokenId, uint8 state) external;

  // BMSP
  function getProductListForRevoke(uint dataId) view external returns (uint[]);
  function validityByRevoke(uint id, bool isData) external;
  function getListForRevoke(address user, uint8 flag) view external returns (uint[]);

  //Bproduct
  function setStorageReceive( uint dataId ) external;
}