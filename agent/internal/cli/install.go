package cli

import (
	"fmt"
	"io"
	"os"
	"os/exec"
	"path/filepath"
	"strconv"
	"strings"

	"golang.org/x/sys/windows/registry"

	"github.com/nightpos/print-agent/internal/config"
	"github.com/nightpos/print-agent/internal/paths"
	"github.com/nightpos/print-agent/internal/wservice"
)

const runKey = `Software\Microsoft\Windows\CurrentVersion\Run`

func Install() error {
	self, err := os.Executable()
	if err != nil {
		return err
	}
	self, err = filepath.Abs(self)
	if err != nil {
		return err
	}

	targetExe := paths.InstalledExePath()

	if !strings.EqualFold(filepath.Clean(self), filepath.Clean(targetExe)) {
		if err := os.MkdirAll(paths.InstallDir(), 0o755); err != nil {
			return fmt.Errorf("create install dir: %w", err)
		}
		if err := copyFile(self, targetExe); err != nil {
			return fmt.Errorf("copy exe: %w", err)
		}
	}

	if err := paths.EnsureDataDirs(); err != nil {
		return err
	}
	if err := config.WriteExampleIfMissing(); err != nil {
		return err
	}

	s, err := wservice.NewAt(targetExe)
	if err != nil {
		return err
	}

	if err := s.Install(); err != nil {
		return fmt.Errorf("service install: %w (ejecute como Administrador)", err)
	}

	_ = configureServiceRecovery()

	trayCmd := fmt.Sprintf(`"%s" --tray`, targetExe)
	if err := setRunKey(trayCmd); err != nil {
		return fmt.Errorf("tray autostart: %w", err)
	}

	if err := s.Start(); err != nil {
		return fmt.Errorf("service start: %w", err)
	}

	startTray(targetExe)

	fmt.Println("NightPOS Print Agent instalado correctamente.")
	fmt.Println("  Ejecutable:", targetExe)
	fmt.Println("  Configuración:", paths.ConfigPath())
	fmt.Println("  Logs:", paths.LogFilePath())
	fmt.Println()
	fmt.Println("Edite config.json con backend_url, device_key y printer_name.")
	return nil
}

func Uninstall() error {
	stopTray()

	s, err := wservice.NewAt(paths.InstalledExePath())
	if err != nil {
		s, _ = wservice.New()
	}
	if s != nil {
		_ = s.Stop()
		if err := s.Uninstall(); err != nil {
			return err
		}
	}
	_ = removeRunKey()
	fmt.Println("Servicio y bandeja desinstalados.")
	fmt.Println("Datos conservados en:", paths.ProgramDataRoot())
	return nil
}

func Start() error   { return wservice.Control("start") }
func Stop() error    { return wservice.Control("stop") }
func Restart() error { return wservice.Control("restart") }

func Status() error {
	st, err := wservice.StatusText()
	if err != nil {
		return err
	}
	fmt.Printf("Servicio %s: %s\n", paths.ServiceName, st)
	fmt.Println("Config:", paths.ConfigPath())
	fmt.Println("Logs:", paths.LogFilePath())
	fmt.Println("Status:", paths.StatusFilePath())
	return nil
}

func configureServiceRecovery() error {
	cmd := exec.Command("sc.exe", "failure", paths.ServiceName,
		"reset=86400",
		"actions=restart/60000/restart/60000/restart/60000",
	)
	_ = cmd.Run()
	cmd2 := exec.Command("sc.exe", "config", paths.ServiceName, "start=auto")
	return cmd2.Run()
}

func setRunKey(command string) error {
	k, _, err := registry.CreateKey(registry.LOCAL_MACHINE, runKey, registry.SET_VALUE)
	if err != nil {
		k, _, err = registry.CreateKey(registry.CURRENT_USER, runKey, registry.SET_VALUE)
		if err != nil {
			return err
		}
	}
	defer k.Close()
	return k.SetStringValue(paths.ServiceName, command)
}

func removeRunKey() error {
	for _, hive := range []registry.Key{registry.LOCAL_MACHINE, registry.CURRENT_USER} {
		k, err := registry.OpenKey(hive, runKey, registry.SET_VALUE)
		if err != nil {
			continue
		}
		_ = k.DeleteValue(paths.ServiceName)
		k.Close()
	}
	return nil
}

func copyFile(src, dst string) error {
	in, err := os.Open(src)
	if err != nil {
		return err
	}
	defer in.Close()

	out, err := os.OpenFile(dst, os.O_CREATE|os.O_WRONLY|os.O_TRUNC, 0o755)
	if err != nil {
		return err
	}
	defer out.Close()

	_, err = io.Copy(out, in)
	return err
}

func startTray(exe string) {
	cmd := exec.Command(exe, "--tray")
	cmd.SysProcAttr = syscallSysProcAttrHideWindow()
	_ = cmd.Start()
}

func stopTray() {
	data, err := os.ReadFile(paths.TrayPIDFile())
	if err != nil {
		return
	}
	pid, err := strconv.Atoi(strings.TrimSpace(string(data)))
	if err != nil || pid <= 0 {
		return
	}
	proc, err := os.FindProcess(pid)
	if err != nil {
		return
	}
	_ = proc.Kill()
	_ = os.Remove(paths.TrayPIDFile())
}

func OpenConfig() error {
	if err := config.WriteExampleIfMissing(); err != nil {
		return err
	}
	return exec.Command("notepad.exe", paths.ConfigPath()).Start()
}

func OpenPrinterSettings() error {
	return exec.Command("rundll32.exe", "printui.dll,PrintUIEntry", "/o").Start()
}
