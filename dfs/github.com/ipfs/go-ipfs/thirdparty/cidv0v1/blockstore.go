package cidv0v1

import (
	cid "gx/ipfs/QmTbxNB1NwDesLmKTscr4udL2tVP7MaxvXnD1D9yX7g3PN/go-cid"
	bs "gx/ipfs/QmXjKkjMDTtXAiLBwstVexofB8LeruZmE2eBd85GwGFFLA/go-ipfs-blockstore"
	blocks "gx/ipfs/QmYYLnAzR28nAQ4U5MFniLprnktu6eTFKibeNt96V21EZK/go-block-format"
	logging "gx/ipfs/QmbkT7eMTyXfpeyB3ZMxxcxg7XH8t6uXp49jqzz4HB7BGF/go-log"
	mh "gx/ipfs/QmerPMzPk1mJVowm8KgmoknWa4yCYvvugMPsgWmDNUvDLW/go-multihash"
)

var log = logging.Logger("cidv0v1")

type blockstore struct {
	bs.Blockstore
}

func NewBlockstore(b bs.Blockstore) bs.Blockstore {
	return &blockstore{b}
}

func (b *blockstore) Has(c cid.Cid) (bool, error) {

	have, err := b.Blockstore.Has(c)
	if have || err != nil {
		return have, err
	}
	c1 := tryOtherCidVersion(c)
	if !c1.Defined() {
		return false, nil
	}
	return b.Blockstore.Has(c1)
}

func (b *blockstore) Get(c cid.Cid) (blocks.Block, error) {

	block, err := b.Blockstore.Get(c)
	if err == nil {
		return block, nil
	}
	if err != bs.ErrNotFound {
		return nil, err
	}
	c1 := tryOtherCidVersion(c)
	if !c1.Defined() {
		return nil, bs.ErrNotFound
	}
	block, err = b.Blockstore.Get(c1)
	if err != nil {
		return nil, err
	}
	// modify block so it has the original CID
	block, err = blocks.NewBlockWithCid(block.RawData(), c)
	if err != nil {
		return nil, err
	}
	// insert the block with the original CID to avoid problems
	// with pinning
	err = b.Blockstore.Put(block)
	if err != nil {
		return nil, err
	}
	return block, nil
}

func (b *blockstore) GetSize(c cid.Cid) (int, error) {
	size, err := b.Blockstore.GetSize(c)
	if err == nil {
		return size, nil
	}
	if err != bs.ErrNotFound {
		return -1, err
	}
	c1 := tryOtherCidVersion(c)
	if !c1.Defined() {
		return -1, bs.ErrNotFound
	}
	size, err = b.Blockstore.GetSize(c1)
	return size, err
}

func tryOtherCidVersion(c cid.Cid) cid.Cid {
	prefix := c.Prefix()
	if prefix.Codec != cid.DagProtobuf || prefix.MhType != mh.SHA2_256 || prefix.MhLength != 32 {
		return cid.Undef
	}
	var c1 cid.Cid
	if prefix.Version == 0 {
		c1 = cid.NewCidV1(cid.DagProtobuf, c.Hash())
	} else {
		c1 = cid.NewCidV0(c.Hash())
	}
	return c1
}
