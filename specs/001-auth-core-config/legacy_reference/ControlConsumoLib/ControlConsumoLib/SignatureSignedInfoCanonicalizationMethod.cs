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
public class SignatureSignedInfoCanonicalizationMethod
{
	private string algorithmField;

	[XmlAttribute]
	public string Algorithm
	{
		get
		{
			return algorithmField;
		}
		set
		{
			algorithmField = value;
		}
	}
}
