package printer

import (
	"fmt"
	"os"
	"strings"
	"syscall"
	"unsafe"

	"golang.org/x/sys/windows"
)

const (
	esc = 0x1b
	gs  = 0x1d
)

var (
	modWinspool = windows.NewLazySystemDLL("winspool.drv")

	procOpenPrinterW     = modWinspool.NewProc("OpenPrinterW")
	procClosePrinter     = modWinspool.NewProc("ClosePrinter")
	procStartDocPrinterW = modWinspool.NewProc("StartDocPrinterW")
	procEndDocPrinter    = modWinspool.NewProc("EndDocPrinter")
	procStartPagePrinter = modWinspool.NewProc("StartPagePrinter")
	procEndPagePrinter   = modWinspool.NewProc("EndPagePrinter")
	procWritePrinter     = modWinspool.NewProc("WritePrinter")
	procEnumPrintersW    = modWinspool.NewProc("EnumPrintersW")
)

type docInfo1 struct {
	DocName    *uint16
	OutputFile *uint16
	Datatype   *uint16
}

func buildEscPosPayload(content string) []byte {
	body := []byte(content)
	out := make([]byte, 0, len(body)+16)
	out = append(out, esc, 0x40)
	out = append(out, body...)
	out = append(out, esc, 0x64, 3)
	out = append(out, gs, 0x56, 0x00)
	return out
}

func ListPrinters() ([]string, error) {
	flags := uint32(0x00000002 | 0x00000004) // LOCAL | CONNECTIONS
	var needed, returned uint32
	_, _, _ = procEnumPrintersW.Call(
		uintptr(flags), 0, 1, 0, 0,
		uintptr(unsafe.Pointer(&needed)),
		uintptr(unsafe.Pointer(&returned)),
	)
	if needed == 0 {
		return nil, nil
	}
	buf := make([]byte, needed)
	r1, _, err := procEnumPrintersW.Call(
		uintptr(flags), 0, 1,
		uintptr(unsafe.Pointer(&buf[0])),
		uintptr(needed),
		uintptr(unsafe.Pointer(&needed)),
		uintptr(unsafe.Pointer(&returned)),
	)
	if r1 == 0 {
		return nil, fmt.Errorf("EnumPrinters failed: %w", err)
	}

	type printerInfo1 struct {
		Flags       uint32
		Description *uint16
		Name        *uint16
		Comment     *uint16
	}

	names := make([]string, 0, returned)
	elemSize := unsafe.Sizeof(printerInfo1{})
	for i := uint32(0); i < returned; i++ {
		ptr := unsafe.Pointer(uintptr(unsafe.Pointer(&buf[0])) + uintptr(i)*elemSize)
		info := (*printerInfo1)(ptr)
		if info.Name != nil {
			names = append(names, windows.UTF16PtrToString(info.Name))
		}
	}
	return names, nil
}

func Verify(name string) error {
	list, err := ListPrinters()
	if err != nil {
		return err
	}
	for _, p := range list {
		if strings.EqualFold(p, name) {
			return nil
		}
	}
	if len(list) == 0 {
		return fmt.Errorf(`impresora "%s" no encontrada (sin impresoras en spooler)`, name)
	}
	return fmt.Errorf(`impresora "%s" no encontrada. Disponibles: %s`, name, strings.Join(list, ", "))
}

func PrintRawEscPos(printerName, content string) (int, error) {
	if err := Verify(printerName); err != nil {
		return 0, err
	}
	payload := buildEscPosPayload(content)
	return printRawBytes(printerName, payload)
}

func WriteDryRun(dir string, jobID int64, content string) (txtPath, binPath string, bytes int, err error) {
	if err = os.MkdirAll(dir, 0o755); err != nil {
		return "", "", 0, err
	}
	payload := buildEscPosPayload(content)
	txtPath = fmt.Sprintf("%s\\job-%d.txt", dir, jobID)
	binPath = fmt.Sprintf("%s\\job-%d.bin", dir, jobID)
	if err = os.WriteFile(txtPath, []byte(content), 0o644); err != nil {
		return "", "", 0, err
	}
	if err = os.WriteFile(binPath, payload, 0o644); err != nil {
		return "", "", 0, err
	}
	return txtPath, binPath, len(payload), nil
}

func printRawBytes(printerName string, data []byte) (int, error) {
	if len(data) == 0 {
		return 0, fmt.Errorf("empty print payload")
	}
	namePtr, err := windows.UTF16PtrFromString(printerName)
	if err != nil {
		return 0, err
	}

	var handle windows.Handle
	r1, _, e1 := procOpenPrinterW.Call(
		uintptr(unsafe.Pointer(namePtr)),
		uintptr(unsafe.Pointer(&handle)),
		0,
	)
	if r1 == 0 {
		return 0, fmt.Errorf("OpenPrinter failed: %w", e1)
	}
	defer procClosePrinter.Call(uintptr(handle))

	docName, _ := windows.UTF16PtrFromString("NightPOS Ticket")
	dataType, _ := windows.UTF16PtrFromString("RAW")
	di := docInfo1{DocName: docName, Datatype: dataType}

	r1, _, e1 = procStartDocPrinterW.Call(
		uintptr(handle), 1,
		uintptr(unsafe.Pointer(&di)),
	)
	if r1 == 0 {
		return 0, fmt.Errorf("StartDocPrinter failed: %w", e1)
	}
	defer procEndDocPrinter.Call(uintptr(handle))

	r1, _, e1 = procStartPagePrinter.Call(uintptr(handle))
	if r1 == 0 {
		return 0, fmt.Errorf("StartPagePrinter failed: %w", e1)
	}
	defer procEndPagePrinter.Call(uintptr(handle))

	var written uint32
	r1, _, e1 = procWritePrinter.Call(
		uintptr(handle),
		uintptr(unsafe.Pointer(&data[0])),
		uintptr(len(data)),
		uintptr(unsafe.Pointer(&written)),
	)
	if r1 == 0 {
		return 0, fmt.Errorf("WritePrinter failed: %w", e1)
	}
	if int(written) != len(data) {
		return int(written), fmt.Errorf("WritePrinter incomplete: %d of %d bytes", written, len(data))
	}
	return int(written), nil
}

// Win32 error helper
func errno(err error) syscall.Errno {
	if err == nil {
		return 0
	}
	if errno, ok := err.(syscall.Errno); ok {
		return errno
	}
	return syscall.Errno(0)
}
