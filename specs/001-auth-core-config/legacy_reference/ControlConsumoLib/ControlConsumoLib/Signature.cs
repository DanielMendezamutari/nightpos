using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Serialization;

namespace ControlConsumoLib;

[Serializable]
[GeneratedCode("xsd", "4.8.3928.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(AnonymousType = true, Namespace = "http://www.w3.org/2000/09/xmldsig#")]
[XmlRoot(Namespace = "http://www.w3.org/2000/09/xmldsig#", IsNullable = false)]
public class Signature
{
	private SignatureSignedInfo signedInfoField;

	private string signatureValueField;

	private SignatureKeyInfo keyInfoField;

	public SignatureSignedInfo SignedInfo
	{
		get
		{
			return signedInfoField;
		}
		set
		{
			signedInfoField = value;
		}
	}

	public string SignatureValue
	{
		get
		{
			return signatureValueField;
		}
		set
		{
			signatureValueField = value;
		}
	}

	public SignatureKeyInfo KeyInfo
	{
		get
		{
			return keyInfoField;
		}
		set
		{
			keyInfoField = value;
		}
	}
}
