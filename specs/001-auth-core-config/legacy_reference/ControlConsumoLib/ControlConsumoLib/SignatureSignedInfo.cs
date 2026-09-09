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
public class SignatureSignedInfo
{
	private SignatureSignedInfoCanonicalizationMethod canonicalizationMethodField;

	private SignatureSignedInfoSignatureMethod signatureMethodField;

	private SignatureSignedInfoReference referenceField;

	public SignatureSignedInfoCanonicalizationMethod CanonicalizationMethod
	{
		get
		{
			return canonicalizationMethodField;
		}
		set
		{
			canonicalizationMethodField = value;
		}
	}

	public SignatureSignedInfoSignatureMethod SignatureMethod
	{
		get
		{
			return signatureMethodField;
		}
		set
		{
			signatureMethodField = value;
		}
	}

	public SignatureSignedInfoReference Reference
	{
		get
		{
			return referenceField;
		}
		set
		{
			referenceField = value;
		}
	}
}
