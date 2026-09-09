using System.Collections.Generic;
using Microsoft.VisualBasic.CompilerServices;
using Newtonsoft.Json;

namespace ControlConsumoLib;

[StandardModule]
public sealed class JsonHelper
{
	public static string FromClass<T>(T data, bool isEmptyToNull = false, JsonSerializerSettings jsonSettings = null)
	{
		string text = string.Empty;
		if (!EqualityComparer<T>.Default.Equals(data, default(T)))
		{
			text = JsonConvert.SerializeObject(data, jsonSettings);
		}
		if (!isEmptyToNull)
		{
			return text;
		}
		if (Operators.CompareString(text, "{}", TextCompare: false) != 0)
		{
			return text;
		}
		return "null";
	}

	public static T ToClass<T>(string data, JsonSerializerSettings jsonSettings = null)
	{
		object value = null;
		if (!string.IsNullOrEmpty(data))
		{
			value = ((jsonSettings == null) ? JsonConvert.DeserializeObject<T>(data) : JsonConvert.DeserializeObject<T>(data, jsonSettings));
		}
		return Conversions.ToGenericParameter<T>(value);
	}
}
