// NightPOS Print Agent — single EXE, Windows Service + system tray.
// Build: go build -ldflags "-H=windowsgui" -o NightPOSPrintAgent.exe .
// CLI build (install/status): go build -o NightPOSPrintAgent.exe .

package main

import (
	"context"
	"flag"
	"fmt"
	"os"
	"os/signal"
	"syscall"

	"github.com/kardianos/service"
	"github.com/nightpos/print-agent/internal/agent"
	"github.com/nightpos/print-agent/internal/cli"
	"github.com/nightpos/print-agent/internal/config"
	"github.com/nightpos/print-agent/internal/logger"
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
	openConfig := flag.Bool("open-config", false, "Abrir config.json en el editor")
	run := flag.Bool("run", false, "Ejecutar agente en primer plano (consola)")
	dryRun := flag.Bool("dry-run", false, "Forzar dry_run (no imprime en impresora)")
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
	if *openConfig {
		exitWith(cli.OpenConfig())
	}
	if *run || *dryRun {
		exitWith(runForeground(*dryRun))
	}

	// Invocado por el Administrador de servicios (sin consola interactiva).
	if !service.Interactive() {
		exitWith(wservice.RunService())
	}

	printUsage()
}

func runForeground(forceDryRun bool) error {
	cfg, err := config.Load()
	if err != nil {
		return err
	}
	if forceDryRun {
		cfg.DryRun = true
	}
	if err := config.Validate(cfg); err != nil {
		return err
	}
	if err := logger.InitWithLevel(cfg.LogLevel); err != nil {
		return err
	}
	defer logger.Close()

	logger.Banner(agent.Version)
	logger.Info("Backend conectado: %s", cfg.BackendURL)
	logger.Info("Impresora: %s", cfg.PrinterName)
	logger.Info("dry_run=%v poll_interval_ms=%d log_level=%s", cfg.DryRun, cfg.PollIntervalMS, cfg.LogLevel)

	ctx, stop := signal.NotifyContext(context.Background(), os.Interrupt, syscall.SIGTERM)
	defer stop()

	rt := agent.NewRuntime(cfg)
	rt.Start(ctx)
	<-ctx.Done()
	rt.Stop()
	logger.Info("Agente detenido")
	return nil
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
	fmt.Println("  NightPOSPrintAgent.exe --open-config  Abrir config.json")
	fmt.Println("  NightPOSPrintAgent.exe --run          Ejecutar en consola (debug)")
	fmt.Println("  NightPOSPrintAgent.exe --dry-run      Consola sin imprimir (archivo)")
	fmt.Println()
	fmt.Println("Tras instalar, edite:", paths.ConfigPath())
}

func exitWith(err error) {
	if err != nil {
		fmt.Fprintln(os.Stderr, "Error:", err)
		os.Exit(1)
	}
}
