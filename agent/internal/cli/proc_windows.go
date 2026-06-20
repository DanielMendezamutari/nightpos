//go:build windows

package cli

import "syscall"

func syscallSysProcAttrHideWindow() *syscall.SysProcAttr {
	return &syscall.SysProcAttr{HideWindow: true}
}
