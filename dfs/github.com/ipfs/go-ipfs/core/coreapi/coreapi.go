/*
Package coreapi provides direct access to the core commands in IPFS. If you are
embedding IPFS directly in your Go program, this package is the public
interface you should use to read and write files or otherwise control IPFS.

If you are running IPFS as a separate process, you should use `go-ipfs-api` to
work with it via HTTP. As we finalize the interfaces here, `go-ipfs-api` will
transparently adopt them so you can use the same code with either package.

**NOTE: this package is experimental.** `go-ipfs` has mainly been developed
as a standalone application and library-style use of this package is still new.
Interfaces here aren't yet completely stable.
*/
package coreapi

import (
	"context"
	"errors"
	"fmt"

	"github.com/ipfs/go-ipfs/core"
	"github.com/ipfs/go-ipfs/namesys"
	"github.com/ipfs/go-ipfs/pin"
	"github.com/ipfs/go-ipfs/repo"

	pstore "gx/ipfs/QmRhFARzTHcFh8wUxwN5KvyTGq73FLC65EfFAhz8Ng7aGb/go-libp2p-peerstore"
	dag "gx/ipfs/QmScf5hnTEK8fDpRJAbcdMnKXpKUp1ytdymzXUbXDCFssp/go-merkledag"
	ci "gx/ipfs/QmTW4SdgBWq9GjsBsHeUx8WuGxzhgzAf88UMH2w62PC8yK/go-libp2p-crypto"
	peer "gx/ipfs/QmTu65MVbemtUxJEWgsTtzv9Zv9P8rvmqNA4eG9TrTRGYc/go-libp2p-peer"
	exchange "gx/ipfs/QmWokDcQdSZCxrNxgaRzQDDBofALhActzNBaxRLtiRkUHg/go-ipfs-exchange-interface"
	record "gx/ipfs/QmX1vqjTLTP6pepDi2uiaGxwKbQbem2PD88nGCZrwxKPEW/go-libp2p-record"
	bserv "gx/ipfs/QmXBjp9iatjaiEpRqrEZpUuKVWTc71vuSUYoPQ5rRQ3SUU/go-blockservice"
	blockstore "gx/ipfs/QmXjKkjMDTtXAiLBwstVexofB8LeruZmE2eBd85GwGFFLA/go-ipfs-blockstore"
	ipld "gx/ipfs/QmZ6nzCLwGLVfRzYLpD7pW6UNuBDKEcA2imJtVpbEx2rxy/go-ipld-format"
	offlinexch "gx/ipfs/Qmb9fkAWgcyVRnFdXGqA6jcWGFj6q35oJjwRAYRhfEboGS/go-ipfs-exchange-offline"
	offlineroute "gx/ipfs/QmbRWQPxtwacR1M8phDY5Y2jJbS96HXZAsK5dKD7s12Wob/go-ipfs-routing/offline"
	logging "gx/ipfs/QmbkT7eMTyXfpeyB3ZMxxcxg7XH8t6uXp49jqzz4HB7BGF/go-log"
	routing "gx/ipfs/QmcxZXMqFu4vjLQRfG2tAcg6DPQNurgZ2SQ5iQVk6dXQjn/go-libp2p-routing"
	p2phost "gx/ipfs/Qmd52WKRSwrBK5gUaJKawryZQ5by6UbNB8KVW2Zy6JtbyW/go-libp2p-host"
	coreiface "gx/ipfs/QmeWKXQfEqbtUDCiQBAHzSZDja9br5LdPgk8eHu86oJxgr/interface-go-ipfs-core"
	"gx/ipfs/QmeWKXQfEqbtUDCiQBAHzSZDja9br5LdPgk8eHu86oJxgr/interface-go-ipfs-core/options"
	pubsub "gx/ipfs/QmepvmmYNM6q4RaUiwEikQFhgMFHXg2PLhx2E9iaRd3jmS/go-libp2p-pubsub"
)

var log = logging.Logger("core/coreapi")

type CoreAPI struct {
	nctx context.Context

	identity   peer.ID
	privateKey ci.PrivKey

	repo       repo.Repo
	blockstore blockstore.GCBlockstore
	baseBlocks blockstore.Blockstore
	pinning    pin.Pinner

	blocks bserv.BlockService
	dag    ipld.DAGService

	peerstore       pstore.Peerstore
	peerHost        p2phost.Host
	recordValidator record.Validator
	exchange        exchange.Interface

	namesys namesys.NameSystem
	routing routing.IpfsRouting

	pubSub *pubsub.PubSub

	checkPublishAllowed func() error
	checkOnline         func(allowOffline bool) error

	// ONLY for re-applying options in WithOptions, DO NOT USE ANYWHERE ELSE
	nd         *core.IpfsNode
	parentOpts options.ApiSettings
}

// NewCoreAPI creates new instance of IPFS CoreAPI backed by go-ipfs Node.
func NewCoreAPI(n *core.IpfsNode, opts ...options.ApiOption) (coreiface.CoreAPI, error) {
	parentOpts, err := options.ApiOptions()
	if err != nil {
		return nil, err
	}

	return (&CoreAPI{nd: n, parentOpts: *parentOpts}).WithOptions(opts...)
}

// Unixfs returns the UnixfsAPI interface implementation backed by the go-ipfs node
func (api *CoreAPI) Unixfs() coreiface.UnixfsAPI {
	return (*UnixfsAPI)(api)
}

// Block returns the BlockAPI interface implementation backed by the go-ipfs node
func (api *CoreAPI) Block() coreiface.BlockAPI {
	return (*BlockAPI)(api)
}

// Dag returns the DagAPI interface implementation backed by the go-ipfs node
func (api *CoreAPI) Dag() coreiface.APIDagService {
	return &dagAPI{
		api.dag,
		api,
	}
}

// Name returns the NameAPI interface implementation backed by the go-ipfs node
func (api *CoreAPI) Name() coreiface.NameAPI {
	return (*NameAPI)(api)
}

// Key returns the KeyAPI interface implementation backed by the go-ipfs node
func (api *CoreAPI) Key() coreiface.KeyAPI {
	return (*KeyAPI)(api)
}

// Object returns the ObjectAPI interface implementation backed by the go-ipfs node
func (api *CoreAPI) Object() coreiface.ObjectAPI {
	return (*ObjectAPI)(api)
}

// Pin returns the PinAPI interface implementation backed by the go-ipfs node
func (api *CoreAPI) Pin() coreiface.PinAPI {
	return (*PinAPI)(api)
}

// Dht returns the DhtAPI interface implementation backed by the go-ipfs node
func (api *CoreAPI) Dht() coreiface.DhtAPI {
	return (*DhtAPI)(api)
}

// Swarm returns the SwarmAPI interface implementation backed by the go-ipfs node
func (api *CoreAPI) Swarm() coreiface.SwarmAPI {
	return (*SwarmAPI)(api)
}

// PubSub returns the PubSubAPI interface implementation backed by the go-ipfs node
func (api *CoreAPI) PubSub() coreiface.PubSubAPI {
	return (*PubSubAPI)(api)
}

// WithOptions returns api with global options applied
func (api *CoreAPI) WithOptions(opts ...options.ApiOption) (coreiface.CoreAPI, error) {
	settings := api.parentOpts // make sure to copy
	_, err := options.ApiOptionsTo(&settings, opts...)
	if err != nil {
		return nil, err
	}

	if api.nd == nil {
		return nil, errors.New("cannot apply options to api without node")
	}

	n := api.nd

	subApi := &CoreAPI{
		nctx: n.Context(),

		identity:   n.Identity,
		privateKey: n.PrivateKey,

		repo:       n.Repo,
		blockstore: n.Blockstore,
		baseBlocks: n.BaseBlocks,
		pinning:    n.Pinning,

		blocks: n.Blocks,
		dag:    n.DAG,

		peerstore:       n.Peerstore,
		peerHost:        n.PeerHost,
		namesys:         n.Namesys,
		recordValidator: n.RecordValidator,
		exchange:        n.Exchange,
		routing:         n.Routing,

		pubSub: n.PubSub,

		nd:         n,
		parentOpts: settings,
	}

	subApi.checkOnline = func(allowOffline bool) error {
		if !n.OnlineMode() && !allowOffline {
			return coreiface.ErrOffline
		}
		return nil
	}

	subApi.checkPublishAllowed = func() error {
		if n.Mounts.Ipns != nil && n.Mounts.Ipns.IsActive() {
			return errors.New("cannot manually publish while IPNS is mounted")
		}
		return nil
	}

	if settings.Offline {
		cfg, err := n.Repo.Config()
		if err != nil {
			return nil, err
		}

		cs := cfg.Ipns.ResolveCacheSize
		if cs == 0 {
			cs = core.DefaultIpnsCacheSize
		}
		if cs < 0 {
			return nil, fmt.Errorf("cannot specify negative resolve cache size")
		}

		subApi.routing = offlineroute.NewOfflineRouter(subApi.repo.Datastore(), subApi.recordValidator)
		subApi.namesys = namesys.NewNameSystem(subApi.routing, subApi.repo.Datastore(), cs)

		subApi.peerstore = nil
		subApi.peerHost = nil
		subApi.recordValidator = nil
	}

	if settings.Offline || !settings.FetchBlocks {
		subApi.exchange = offlinexch.Exchange(subApi.blockstore)
		subApi.blocks = bserv.New(subApi.blockstore, subApi.exchange)
		subApi.dag = dag.NewDAGService(subApi.blocks)
	}

	return subApi, nil
}

// getSession returns new api backed by the same node with a read-only session DAG
func (api *CoreAPI) getSession(ctx context.Context) *CoreAPI {
	sesApi := *api

	// TODO: We could also apply this to api.blocks, and compose into writable api,
	// but this requires some changes in blockservice/merkledag
	sesApi.dag = dag.NewReadOnlyDagService(dag.NewSession(ctx, api.dag))

	return &sesApi
}
