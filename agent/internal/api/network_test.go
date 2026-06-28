package api

import (
	"errors"
	"io"
	"testing"
)

func TestIsNetworkError(t *testing.T) {
	cases := []struct {
		err  error
		want bool
	}{
		{nil, false},
		{errors.New("401 Clave inválida"), false},
		{errors.New("invalid API response"), false},
		{errors.New("stream error: stream ID 1; CANCEL; received from peer"), true},
		{errors.New("read tcp: wsarecv: connection reset by peer"), true},
		{errors.New("read tcp: wsarecv: Se ha forzado la interrupción"), true},
		{errors.New("EOF"), true},
		{io.EOF, true},
		{io.ErrUnexpectedEOF, true},
		{errors.New("empty reply from server"), true},
	}

	for _, tc := range cases {
		if got := IsNetworkError(tc.err); got != tc.want {
			t.Errorf("IsNetworkError(%q) = %v, want %v", tc.err, got, tc.want)
		}
	}
}
