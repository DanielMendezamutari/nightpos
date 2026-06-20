package paths

import (
	"os"
	"path/filepath"
)

const (
	AppFolderName  = "NightPOS"
	AgentFolderName = "PrintAgent"
	ServiceName    = "NightPOSPrintAgent"
	DisplayName    = "NightPOS Print Agent"
)

func ProgramDataRoot() string {
	pd := os.Getenv("PROGRAMDATA")
	if pd == "" {
		pd = `C:\ProgramData`
	}
	return filepath.Join(pd, AppFolderName, AgentFolderName)
}

func InstallDir() string {
	pf := os.Getenv("ProgramFiles")
	if pf == "" {
		pf = `C:\Program Files`
	}
	return filepath.Join(pf, AppFolderName, AgentFolderName)
}

func InstalledExePath() string {
	return filepath.Join(InstallDir(), "NightPOSPrintAgent.exe")
}

func ConfigPath() string {
	return filepath.Join(ProgramDataRoot(), "config.json")
}

func LogDir() string {
	return filepath.Join(ProgramDataRoot(), "logs")
}

func LogFilePath() string {
	return filepath.Join(LogDir(), "agent.log")
}

func StatusFilePath() string {
	return filepath.Join(ProgramDataRoot(), "status.json")
}

func TrayPIDFile() string {
	return filepath.Join(ProgramDataRoot(), "tray.pid")
}

func ConfigExamplePath() string {
	return filepath.Join(ProgramDataRoot(), "config.example.json")
}

func EnsureDataDirs() error {
	for _, dir := range []string{ProgramDataRoot(), LogDir()} {
		if err := os.MkdirAll(dir, 0o755); err != nil {
			return err
		}
	}
	return nil
}
