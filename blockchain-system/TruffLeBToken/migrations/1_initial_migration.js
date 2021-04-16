const Migrations = artifacts.require('./Migrations.sol')

module.exports = (deployer)=>{
  deployer.deploy(Migrations)
};

// const Bconfigs = artifacts.require('./Bconfig.sol')

// module.exports = (deployer)=>{
//   deployer.deploy(Bconfigs)
// };
