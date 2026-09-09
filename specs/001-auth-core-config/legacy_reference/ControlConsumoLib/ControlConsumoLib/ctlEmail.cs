namespace ControlConsumoLib;

public class ctlEmail
{
	private readonly clsEmail clsEml;

	public ctlEmail()
	{
		clsEml = new clsEmail();
	}

	public void devolver(ref int idEmail, ref string Username, ref string Passw, ref string Port, ref string Frm, ref string Host, ref bool SSL)
	{
		clsEml.Devolver();
		idEmail = clsEml._EmailID;
		Username = clsEml._Username;
		Passw = clsEml._Passw;
		Port = clsEml._Port;
		Frm = clsEml._Frm;
		Host = clsEml._Host;
		SSL = clsEml._SSL;
	}

	public void Guardar(int idEmail, string Username, string Passw, string Port, string Frm, string Host, bool ssl)
	{
		clsEml._EmailID = idEmail;
		clsEml._Username = Username;
		clsEml._Passw = Passw;
		clsEml._Port = Port;
		clsEml._Frm = Frm;
		clsEml._Host = Host;
		clsEml._SSL = ssl;
		clsEml.Modificar();
	}
}
