using System;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

[StandardModule]
public sealed class TipoUsuarioIDExtensions
{
	public static string TipoUsuarioIDToFriendlyString(this ctlMeseros.TipoUsuarioID tipoUsuario)
	{
		return Enum.GetName(typeof(ctlMeseros.TipoUsuarioID), tipoUsuario);
	}

	public static string TipoUsuarioPeluqueriaIDToFriendlyString(this ctlMeseros.TipoUsuarioPeluqueriaID tipoUsuario)
	{
		return Enum.GetName(typeof(ctlMeseros.TipoUsuarioPeluqueriaID), tipoUsuario);
	}

	public static string TipoUsuarioOtrosIDToFriendlyString(this ctlMeseros.TipoUsuarioOtrosID tipoUsuario)
	{
		return Enum.GetName(typeof(ctlMeseros.TipoUsuarioOtrosID), tipoUsuario);
	}

	public static string TipoUsuarioLlevarIDToFriendlyString(this ctlMeseros.TipoUsuarioLlevarID tipoUsuario)
	{
		return Enum.GetName(typeof(ctlMeseros.TipoUsuarioLlevarID), tipoUsuario);
	}

	public static string TipoUsuarioCateringIDToFriendlyString(this ctlMeseros.TipoUsuarioCateringID tipoUsuario)
	{
		return Enum.GetName(typeof(ctlMeseros.TipoUsuarioCateringID), tipoUsuario);
	}
}
