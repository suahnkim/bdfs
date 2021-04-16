package namesys

import (
	"context"
	"errors"

	path "gx/ipfs/QmR3bNAtBoTN6xZ2HQNqpRQARcDoazH9jU6zKUNjFyQKWS/go-path"
	proquint "gx/ipfs/QmYnf27kzqR2cxt6LFZdrAFJuQd6785fTkBvMuEj9EeRxM/proquint"
	opts "gx/ipfs/QmeWKXQfEqbtUDCiQBAHzSZDja9br5LdPgk8eHu86oJxgr/interface-go-ipfs-core/options/namesys"
)

type ProquintResolver struct{}

// Resolve implements Resolver.
func (r *ProquintResolver) Resolve(ctx context.Context, name string, options ...opts.ResolveOpt) (path.Path, error) {
	return resolve(ctx, r, name, opts.ProcessOpts(options))
}

// resolveOnce implements resolver. Decodes the proquint string.
func (r *ProquintResolver) resolveOnceAsync(ctx context.Context, name string, options opts.ResolveOpts) <-chan onceResult {
	out := make(chan onceResult, 1)
	defer close(out)

	ok, err := proquint.IsProquint(name)
	if err != nil || !ok {
		out <- onceResult{err: errors.New("not a valid proquint string")}
		return out
	}
	// Return a 0 TTL as caching this result is pointless.
	out <- onceResult{value: path.FromString(string(proquint.Decode(name)))}
	return out
}
