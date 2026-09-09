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
public class SignatureSignedInfoReference
{
	private SignatureSignedInfoReferenceTransform[] transformsField;

	private SignatureSignedInfoReferenceDigestMethod digestMethodField;

	private string digestValueField;

	private string uRIField;

	[XmlArrayItem("Transform", IsNullable = false)]
	public SignatureSignedInfoReferenceTransform[] Transforms
	{
		get
		{
			return transformsField;
		}
		set
		{
			transformsField = value;
		}
	}

	public SignatureSignedInfoReferenceDigestMethod DigestMethod
	{
		get
		{
			return digestMethodField;
		}
		set
		{
			digestMethodField = value;
		}
	}

	public string DigestValue
	{
		get
		{
			return digestValueField;
		}
		set
		{
			digestValueField = value;
		}
	}

	[XmlAttribute]
	public string URI
	{
		get
		{
			return uRIField;
		}
		set
		{
			uRIField = value;
		}
	}
}
