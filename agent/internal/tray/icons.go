package tray

import (
	"bytes"
	"encoding/binary"
)

var (
	iconGreen  = buildSolidIcon(0x22, 0xc5, 0x22)
	iconYellow = buildSolidIcon(0xf5, 0xc5, 0x11)
	iconRed    = buildSolidIcon(0xe5, 0x39, 0x35)
)

// buildSolidIcon creates a minimal 16x16 32bpp ICO with a solid color.
func buildSolidIcon(r, g, b byte) []byte {
	const size = 16
	pixels := size * size * 4
	andMask := size * size / 8

	bmpHeaderSize := 40
	imageSize := bmpHeaderSize + pixels + andMask

	var buf bytes.Buffer
	// ICONDIR
	_ = binary.Write(&buf, binary.LittleEndian, uint16(0))
	_ = binary.Write(&buf, binary.LittleEndian, uint16(1))
	_ = binary.Write(&buf, binary.LittleEndian, uint16(1))
	// ICONDIRENTRY
	buf.WriteByte(size)
	buf.WriteByte(size)
	buf.WriteByte(0)
	buf.WriteByte(0)
	_ = binary.Write(&buf, binary.LittleEndian, uint16(1))
	_ = binary.Write(&buf, binary.LittleEndian, uint16(32))
	_ = binary.Write(&buf, binary.LittleEndian, uint32(imageSize))
	_ = binary.Write(&buf, binary.LittleEndian, uint32(22))

	// BITMAPINFOHEADER
	_ = binary.Write(&buf, binary.LittleEndian, uint32(40))
	_ = binary.Write(&buf, binary.LittleEndian, int32(size))
	_ = binary.Write(&buf, binary.LittleEndian, int32(size*2)) // height * 2 for ICO
	_ = binary.Write(&buf, binary.LittleEndian, uint16(1))
	_ = binary.Write(&buf, binary.LittleEndian, uint16(32))
	_ = binary.Write(&buf, binary.LittleEndian, uint32(0))
	_ = binary.Write(&buf, binary.LittleEndian, uint32(pixels))
	_ = binary.Write(&buf, binary.LittleEndian, int32(0))
	_ = binary.Write(&buf, binary.LittleEndian, int32(0))
	_ = binary.Write(&buf, binary.LittleEndian, uint32(0))
	_ = binary.Write(&buf, binary.LittleEndian, uint32(0))

	// Pixels bottom-up BGRA
	for y := size - 1; y >= 0; y-- {
		for x := 0; x < size; x++ {
			buf.WriteByte(b)
			buf.WriteByte(g)
			buf.WriteByte(r)
			buf.WriteByte(0xFF)
		}
	}
	// AND mask
	buf.Write(make([]byte, andMask))
	return buf.Bytes()
}
