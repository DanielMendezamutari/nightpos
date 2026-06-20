// NightPOS Print Agent — single EXE, Windows Service + system tray.
// Build: go build -ldflags "-H=windowsgui" -o NightPOSPrintAgent.exe .
// CLI build (install/status): go build -o NightPOSPrintAgent.exe .

package main

import (
	"flag"
	"fmt"
	"os"

	"github.com/kardianos/service"
	"github.com/nightpos/print-agent/internal/cli"
	"github.com/nightpos/print-agent/internal/paths"
	"github.com/nightpos/print-agent/internal/tray"
	"github.com/nightpos/print-agent/internal/wservice"
)

func main() {
	install := flag.Bool("install", false, "Instalar servicio Windows + bandeja")
	uninstall := flag.Bool("uninstall", false, "Desinstalar servicio")
	start := flag.Bool("start", false, "Iniciar servicio")
	stop := flag.Bool("stop", false, "Detener servicio")
	restart := flag.Bool("restart", false, "Reiniciar servicio")
	status := flag.Bool("status", false, "Estado del servicio")
	trayMode := flag.Bool("tray", false, "Modo bandeja del sistema (interno)")
	flag.Parse()

	if *install {
		exitWith(cli.Install())
	}
	if *uninstall {
		exitWith(cli.Uninstall())
	}
	if *start {
		exitWith(cli.Start())
	}
	if *stop {
		exitWith(cli.Stop())
	}
	if *restart {
		exitWith(cli.Restart())
	}
	if *status {
		exitWith(cli.Status())
	}
	if *trayMode {
		tray.Run()
		return
	}

	// Invocado por el Administrador de servicios (sin consola interactiva).
	if !service.Interactive() {
		exitWith(wservice.RunService())
	}

	printUsage()
}

func printUsage() {
	fmt.Println("NightPOS Print Agent")
	fmt.Println()
	fmt.Println("  NightPOSPrintAgent.exe --install      Instalar (como Administrador)")
	fmt.Println("  NightPOSPrintAgent.exe --uninstall    Desinstalar")
	fmt.Println("  NightPOSPrintAgent.exe --start        Iniciar servicio")
	fmt.Println("  NightPOSPrintAgent.exe --stop         Detener servicio")
	fmt.Println("  NightPOSPrintAgent.exe --restart      Reiniciar servicio")
	fmt.Println("  NightPOSPrintAgent.exe --status       Ver estado")
	fmt.Println()
	fmt.Println("Tras instalar, edite:", paths.ConfigPath())
}

func exitWith(err error) {
	if err != nil {
		fmt.Fprintln(os.Stderr, "Error:", err)
		os.Exit(1)
	}
}
