package tray

import (
	"fmt"
	"os"
	"time"

	"github.com/getlantern/systray"
	"github.com/nightpos/print-agent/internal/cli"
	"github.com/nightpos/print-agent/internal/logger"
	"github.com/nightpos/print-agent/internal/paths"
	"github.com/nightpos/print-agent/internal/status"
)

func Run() {
	_ = os.WriteFile(paths.TrayPIDFile(), []byte(fmt.Sprintf("%d", os.Getpid())), 0o644)
	defer os.Remove(paths.TrayPIDFile())

	systray.Run(onReady, onExit)
}

func onReady() {
	systray.SetIcon(iconGreen)
	systray.SetTitle("NightPOS")
	systray.SetTooltip("NightPOS Print Agent — iniciando...")

	mRestart := systray.AddMenuItem("Reiniciar agente", "Reinicia el servicio Windows")
	mLogs := systray.AddMenuItem("Ver logs", "Abrir archivo de log")
	mLogDir := systray.AddMenuItem("Abrir carpeta logs", "")
	mPrinter := systray.AddMenuItem("Cambiar impresora (Windows)", "Abrir impresoras de Windows")
	mConfig := systray.AddMenuItem("Abrir configuración", "Editar config.json")
	systray.AddSeparator()
	mExit := systray.AddMenuItem("Salir icono bandeja", "Cierra solo el icono (servicio sigue)")

	go refreshLoop()

	go func() {
		for {
			select {
			case <-mRestart.ClickedCh:
				_ = cli.Restart()
			case <-mLogs.ClickedCh:
				_ = logger.OpenLogFile()
			case <-mLogDir.ClickedCh:
				_ = logger.OpenLogFolder()
			case <-mPrinter.ClickedCh:
				_ = cli.OpenPrinterSettings()
			case <-mConfig.ClickedCh:
				_ = cli.OpenConfig()
			case <-mExit.ClickedCh:
				systray.Quit()
				return
			}
		}
	}()
}

func onExit() {}

func refreshLoop() {
	ticker := time.NewTicker(2 * time.Second)
	defer ticker.Stop()

	for {
		snap := status.LoadFromFile()
		emoji := status.Emoji(snap)
		label := status.Label(snap)
		tooltip := fmt.Sprintf("%s %s", emoji, label)
		if snap.LastError != "" {
			tooltip += "\n" + snap.LastError
		}
		systray.SetTooltip(tooltip)

		switch snap.State {
		case status.StateConnected:
			systray.SetIcon(iconGreen)
		case status.StateNoInternet:
			systray.SetIcon(iconYellow)
		case status.StatePrinterError, status.StateConfigError:
			systray.SetIcon(iconRed)
		default:
			systray.SetIcon(iconYellow)
		}

		<-ticker.C
	}
}
