using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.ComponentModel.Design;
using System.Configuration;
using System.Diagnostics;
using System.Globalization;
using System.Reflection;
using System.Resources;
using System.Runtime.CompilerServices;
using System.Runtime.InteropServices;
using System.Runtime.Versioning;
using System.Security;
using System.Security.Permissions;
using Microsoft.CodeAnalysis;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.ApplicationServices;
using Microsoft.VisualBasic.CompilerServices;
using Microsoft.VisualBasic.Devices;
using Microsoft.VisualBasic.MyServices.Internal;

[assembly: CompilationRelaxations(8)]
[assembly: RuntimeCompatibility(WrapNonExceptionThrows = true)]
[assembly: Debuggable(DebuggableAttribute.DebuggingModes.Default | DebuggableAttribute.DebuggingModes.DisableOptimizations | DebuggableAttribute.DebuggingModes.IgnoreSymbolStoreSequencePoints | DebuggableAttribute.DebuggingModes.EnableEditAndContinue)]
[assembly: AssemblyTitle("ConfigToptech")]
[assembly: AssemblyDescription("")]
[assembly: AssemblyCompany("")]
[assembly: AssemblyProduct("ConfigToptech")]
[assembly: AssemblyCopyright("toptech ©  2015")]
[assembly: AssemblyTrademark("Toptech srl")]
[assembly: ComVisible(false)]
[assembly: Guid("5642beb9-7e36-418d-be43-6103aa1faa67")]
[assembly: AssemblyFileVersion("3.0.15.0")]
[assembly: TargetFramework(".NETFramework,Version=v4.8", FrameworkDisplayName = ".NET Framework 4.8")]
[assembly: SecurityPermission(SecurityAction.RequestMinimum, SkipVerification = true)]
[assembly: AssemblyVersion("3.0.15.0")]
[module: UnverifiableCode]
[module: RefSafetyRules(11)]
namespace Microsoft.CodeAnalysis
{
	[CompilerGenerated]
	[Embedded]
	internal sealed class EmbeddedAttribute : Attribute
	{
	}
}
namespace System.Runtime.CompilerServices
{
	[CompilerGenerated]
	[Embedded]
	[AttributeUsage(AttributeTargets.Module, AllowMultiple = false, Inherited = false)]
	internal sealed class RefSafetyRulesAttribute : Attribute
	{
		public readonly int Version;

		public RefSafetyRulesAttribute(int P_0)
		{
			Version = P_0;
		}
	}
}
namespace ConfigToptech
{
	[StandardModule]
	public sealed class configuration
	{
		public enum MesasVisibilidad
		{
			SoloVeoMisMesas = 1,
			TodosVenTodo,
			VeoMesasLibresMasMisMesas
		}

		public enum styleBolichesId
		{
			Pupis = 2,
			Tang = 3,
			Dubai = 6,
			JardinPollos = 8,
			PollosBatman = 9,
			TangExpress = 10,
			OrientePetrolero = 12,
			DonShawarmaLite = 13,
			lePetitCafetier = 16,
			Fragolia = 17,
			DonMiguel = 18,
			Marguerita = 21,
			shiwu = 23,
			TangExpress2 = 26,
			ElCuartito = 27,
			Bestial = 24,
			Tito = 30,
			Depilnova = 35,
			Stigma = 36,
			expoFood = -10,
			Bless = 42,
			ElSolar = 45,
			BuenDia = 46,
			Ranchero = 49,
			Brasargent = 50,
			Agronaciente = 52,
			ToptechOLD = 53,
			Mileta = 54,
			Andromeda = 56,
			London = 58,
			Contecc = 59,
			Acai = 61,
			BAHREM = 63,
			Jardin = 64,
			Cocolulu = 65,
			Sabores = 66,
			MicromercadoCercaTuyo = 67,
			LogicTruck = 70,
			servimans = 72,
			Alex = 76,
			MagnoGym = -3,
			Solstice = 79,
			LoNuestro = 80,
			CurtiembreTauro = -1,
			multistock = 81,
			Nectar = 83,
			FastTaste = 85,
			IrishPub = 86,
			PincheTaco = 87,
			GloboLoco = 88,
			LocosAsar = 89,
			LaGaira = 96,
			Bistrovia = 99,
			NatuLife = 101,
			Paradise = 105,
			Zucchini = 107,
			Meraki = 109,
			qDeliSql = 110,
			LosLomitos = 111,
			Illuminart = 115,
			IrishPubFac = 116,
			srPollo = 117,
			MGTRAILER = 119,
			Dalias15 = 120,
			EspigaDeOro = 121,
			Asai = 123,
			TorrezSoliz = 124,
			Soboce = 125,
			PolloPicapiedra = 126,
			coffeeTime = 127,
			kawai = 128,
			Tartina = 129,
			PedroDelBrete = 133,
			elRancho = 134,
			Sansha = 135,
			Mongaru = 136,
			Brazzeiro = 137,
			FitZone1 = 138,
			RinconBrasilero = 139,
			OnceTintos = 140,
			elRanchoNew = 141,
			PedroDelBreteXpress = 142,
			LosLomitosExpress = 147,
			Pagode = 150,
			MGTRAILER2 = 154,
			ElCubo = 155,
			PizzaRio = 156,
			Container = 157,
			SrCarne = 158,
			DolceVita = 159,
			Galette = 161,
			Boulangerie = 162,
			GESA = 163,
			Empanaderia = 164,
			Ottimo = 166,
			Pavlova = 167,
			Ciberal = 168,
			Allegronet1 = 170,
			Cheraa = 171,
			KIKY = 172,
			guacamole1 = 173,
			CafeAme = 174,
			Cine = 175,
			Naoki = 176,
			Oishi = 179,
			Cheers = 181,
			Buteco = 183,
			MarDeLimon = 185,
			Beer = 186,
			PipiCucu = 187,
			Kabana = 188,
			Arabia = 189,
			malegria1 = 190,
			Serendipity = 191,
			Creacion = 192,
			Serdagen = 193,
			Iturri = 194,
			Vintage = 195,
			Panessa = 196,
			PolpaNorte = 197,
			AguaViva1 = 198,
			Burshi = 199,
			Chaplin = 200,
			Etmuller = 201,
			AltaMar = 202,
			Mahalo = 203,
			Luxos = 204,
			Deterlinos = 205,
			dataJardinPollosNew = 206,
			Tag = 207,
			IlPane = 208,
			Acaizero = 209,
			LaGaleria = 210,
			Texas = 211,
			Moscada = 212,
			Mandarin1 = 213,
			Renaissance = 214,
			LondonCochabamba = 215,
			Riad = 216,
			Rokani = 217,
			ConTenedores = 218,
			LaBarra = 219,
			Rinconada = 220,
			Shimaya = 221,
			Tuticapa = 222,
			Barberia = 223,
			Doce = -224,
			Sonnengarten = 225,
			Sahara = 227,
			Tapekua = 228,
			Belen = 229,
			marisaViera = 230,
			ElCortijo = 231,
			Ginecosalud = 233,
			Pranna = 234,
			FitZone2 = 235,
			Jacuu = 236,
			Palermo = Dubai,
			Dollhouse = 239,
			Aroma = 237,
			HeladeriaSucre = Dollhouse,
			LeMayen = 240,
			GreenLovers = 241,
			Ludo = 242,
			PizzaGo = 243,
			PlanB = 245,
			Landivar = 246,
			Mune = 247,
			Ojodelamo = 248,
			Kaos = 250,
			SirFrancis = 252,
			TuTazon = 253,
			LolaBurguer = 254,
			Republica = 256,
			Jungla = 257,
			RedEstel = 258,
			JESPfacturacion = 259,
			Restomenu = 260,
			MariaDeMolina = 261,
			CocinasOcultas = 262,
			MATFOOD = 263,
			PollosBatmanNew = 264,
			darkKitchen = 265,
			InesEspana = 266,
			Vikingo = 268,
			NuevaChina = 270,
			TartinaFactura = 271,
			Malaba = 272,
			Crapuzzi = 273,
			Beneficios = 274,
			PizzaSteve = 275,
			Chopao = 276,
			PizzaZapi = Chopao,
			MandarinFac = 277,
			Mediterranea = 278,
			Panino = 280,
			LolaBar = 281,
			Jarana = 282,
			Pioneros = 283,
			MicromercadoPasse = 284,
			KeySolutions = 285,
			Vienessa = 287,
			Pekelicious = 288,
			delRio = 289,
			BurgerMunch = 290,
			NotMac = 291,
			KulturBerlin = 292,
			SanTelmo = 293,
			TangDelicenter = 294,
			Fuego = 295,
			bigBaby = 296,
			casaGrande = 297,
			AltoTostado = 300,
			CrossfitBol = 381,
			Mediterraneo = 302,
			Odontoclinica = -303,
			steak = 304,
			Michelangelo = 305,
			Landsua = 306,
			Black = 307,
			Kaldi = 308,
			Alquimia = 309,
			ManosBolivianas = 310,
			Cowboy = 311,
			LolaHuari = 312,
			Yantar = 313,
			Capital = 314,
			malboro = 316,
			PokePoke = 317,
			Donal = 318,
			Tenis = 319,
			Belmond = 320,
			PizzaBizarra = 321,
			Fogo = 322,
			LaCastañuela = 323,
			Hapo = 324,
			Toro = 325,
			Vacafria = 326,
			VacafriaLaPaz = 368,
			Vitrina = 327,
			Hawai = 328,
			Endulzate = 329,
			LaCondesa = 330,
			Toroo = 331,
			LaTabla = 332,
			PerformancePHP = -11,
			Bohemia = 333,
			Rodeo = 334,
			ComidaSuarez = 335,
			Tulum = 336,
			HabibiShow = 337,
			Habito = 338,
			LomoGrill = 339,
			InesEspanaPanaderia = 340,
			PuertoMilanesa = 341,
			Riviera = 342,
			BiancaFlor = 343,
			Solarcomex = 344,
			pizzaRing = 345,
			Panorama = 346,
			Cornerstone = 347,
			Migre = 348,
			Serafina = 349,
			Oma = 350,
			ElMordisco = 351,
			LaNegra = 352,
			CheGaucho = 353,
			SamImport = 354,
			Dadesaf = 355,
			SwissBurger = 356,
			Levent = 357,
			lavasecoUniversal1 = 358,
			EsquinaChina = 360,
			PuroMar = 361,
			Swissco = 362,
			Craft = 363,
			Rhuna = 364,
			LaCucharaBrava = 365,
			GalloNegro = 366,
			Tributo = 367,
			JibaBurger = VacafriaLaPaz,
			Amerana = 369,
			Hito = 370,
			CasaCero = 371,
			FogonGringo = 372,
			BaileysLiquors = 373,
			Singapur = 374,
			Oportunidades = -12,
			PastaMadre = 375,
			Pollononon = 376,
			Middagh = -13,
			Kaddosh = -14,
			LogiaDeLosSabores = 377,
			KAO = 378,
			CristianMora = 379,
			DonPato = 380,
			Donatella = CrossfitBol,
			PizzaGrande = 382,
			Brizza = 383,
			ReyShawarma = 384,
			FloBakery = 385,
			Jahazias = -15,
			Bocarte = 387,
			MexicanFood = 388,
			UgosPizza = 389,
			DonTaco = 390,
			Jelti = 391,
			Swisshotel = 392,
			SirPieper = 393,
			Aerocruz = 394,
			LaTribu = 395,
			Argentino = 396,
			MiAlegria = 397,
			MangaRosa = 398,
			Bonita = 399,
			Goss = 400,
			Palestino = 401,
			Vento = 402,
			FormulaFitness = 403,
			LaSuisse = 404,
			Mozza = 405,
			Subway = 406,
			Castilla = 407,
			Mundocruz = -16,
			PressAllen = 408,
			Bistecca = 409,
			LubricantesCristo1 = -17,
			LubricantesCristo2 = -20,
			Franabol = 410,
			Cilantro = 411,
			Memima = 412,
			Gozo = 413,
			Bruse = 414,
			Copacabana = 415,
			California = 416,
			LandHaus = 417,
			Sopocachi = 418,
			MHTraining = 419,
			ElMovima = 450,
			SushiFlash = 451,
			Gunters = 452,
			TigreMorado = 453,
			Zuppa = 454,
			Brios = 455,
			Confetti = 456,
			Distinto = 457,
			SanHo = 458,
			DellaCasa = 459,
			CasaCuina = 460,
			Okoa = 461,
			Bravissimo = 462,
			WashSpot = 463,
			PolloDeOro = 464,
			IceLand = 465,
			Vulcanica = 466,
			Soho = 467,
			JetSet = 468,
			ReyDelTaco = 469,
			TerraPampa = 470,
			HaciendaDelGaucho = 471,
			MartinFuego = 472,
			Bracan = 473,
			FreshToGo = 474,
			BarraBistro = 475,
			Bubba = 476,
			GaleriaFoodGarden = 477,
			Refricenter = 478,
			LeonImportaciones = 479,
			CateringSacherCorp = 480,
			SaintGeorge = 481,
			XpressPlaza = 482,
			Kivon = 483,
			Fortunata = -18,
			Catalinda = 484,
			Economato = 485,
			Jalapenos = 486,
			SalteneriaSC = 487,
			Zerwinflex = -19,
			Sacura = 488,
			Asador = 489,
			Healthy = 490,
			Toptech = 491,
			Batos = 492,
			Listo = 493,
			CasaAsbun = 494,
			Fussion = 495,
			MacPlay = 496,
			Bernadette = 497,
			Buhoo = 498,
			Elements = 499,
			SantaMaria = 500,
			HappyGreen = Fortunata,
			Mhinos = 501,
			ElementsAccess = Zerwinflex,
			Savanna = 502,
			Jaleo = 503,
			Doner = 504,
			Botanica = 505,
			LatePorTi = 506,
			Noa = 507,
			CosmoMar = 508,
			Casa22 = 509
		}

		public static bool gCodigosMesasNumericos = false;

		public static string _PublicIP = "";

		public static readonly bool gPensiones = false;

		public static readonly bool gPeluqueria = false;

		public static readonly bool gCombosSeimprimenComoItem = true;

		public static readonly bool gEnCombosSeimprimeTodo = false;

		public static readonly bool gSoloFacturacionGrande = false;

		public static readonly bool gFormatoFacturaGrande = false;

		public static readonly bool gAgruparPedidos = false;

		public static readonly MesasVisibilidad gMesasVisibilidad = MesasVisibilidad.TodosVenTodo;

		public static readonly bool gSupermercado = false;

		public static readonly bool gGimnasio = false;

		public static readonly bool gModo_Remoto = false;

		public static readonly string CaracterFecha = "'";

		public static readonly int gMODO_ACCESS = 0;

		public static bool gManejaElServicio = true;

		public static readonly bool gComidaRapida = false;

		public static readonly bool gVersionLite = false;

		public static readonly bool gIdentificadorPersonaRecoge = false;

		public static readonly styleBolichesId gStyleBoliches1 = styleBolichesId.Casa22;

		public static readonly bool gConPedidoParaLlevar = true;

		public static readonly string db_file = "ControlConsumoCasa22";

		public static readonly string db_user = "sa";

		public static readonly string db_file_password = "toptech";

		public static readonly string db_instance = "(localdb)\\MSSQLLocalDB";

		public static readonly bool gManejaComidaKilo = false;

		public static readonly int gTipoFacturacion = 2;

		public static readonly bool gManejaTurnos = true;

		public static readonly bool gRedonderCentavos = true;

		public static bool _gVentaExpressTeclado = false;

		public static readonly bool gHuelladigital = false;

		public static void setVentaExpressTeclado(bool status)
		{
			_gVentaExpressTeclado = status;
		}

		public static bool gVentaExpressTeclado()
		{
			return _gVentaExpressTeclado;
		}
	}
}
namespace ConfigToptech.My
{
	[GeneratedCode("MyTemplate", "11.0.0.0")]
	[EditorBrowsable(EditorBrowsableState.Never)]
	internal class MyApplication : ApplicationBase
	{
	}
	[GeneratedCode("MyTemplate", "11.0.0.0")]
	[EditorBrowsable(EditorBrowsableState.Never)]
	internal class MyComputer : Computer
	{
		[DebuggerHidden]
		[EditorBrowsable(EditorBrowsableState.Never)]
		public MyComputer()
		{
		}
	}
	[StandardModule]
	[HideModuleName]
	[GeneratedCode("MyTemplate", "11.0.0.0")]
	internal sealed class MyProject
	{
		[EditorBrowsable(EditorBrowsableState.Never)]
		[MyGroupCollection("System.Web.Services.Protocols.SoapHttpClientProtocol", "Create__Instance__", "Dispose__Instance__", "")]
		internal sealed class MyWebServices
		{
			[EditorBrowsable(EditorBrowsableState.Never)]
			[DebuggerHidden]
			public override bool Equals(object o)
			{
				return base.Equals(RuntimeHelpers.GetObjectValue(o));
			}

			[EditorBrowsable(EditorBrowsableState.Never)]
			[DebuggerHidden]
			public override int GetHashCode()
			{
				return base.GetHashCode();
			}

			[EditorBrowsable(EditorBrowsableState.Never)]
			[DebuggerHidden]
			internal new Type GetType()
			{
				return typeof(MyWebServices);
			}

			[EditorBrowsable(EditorBrowsableState.Never)]
			[DebuggerHidden]
			public override string ToString()
			{
				return base.ToString();
			}

			[DebuggerHidden]
			private static T Create__Instance__<T>(T instance) where T : new()
			{
				if (instance == null)
				{
					return new T();
				}
				return instance;
			}

			[DebuggerHidden]
			private void Dispose__Instance__<T>(ref T instance)
			{
				instance = default(T);
			}

			[DebuggerHidden]
			[EditorBrowsable(EditorBrowsableState.Never)]
			public MyWebServices()
			{
			}
		}

		[EditorBrowsable(EditorBrowsableState.Never)]
		[ComVisible(false)]
		internal sealed class ThreadSafeObjectProvider<T> where T : new()
		{
			private readonly ContextValue<T> m_Context;

			internal T GetInstance
			{
				[DebuggerHidden]
				get
				{
					T val = m_Context.Value;
					if (val == null)
					{
						val = new T();
						m_Context.Value = val;
					}
					return val;
				}
			}

			[DebuggerHidden]
			[EditorBrowsable(EditorBrowsableState.Never)]
			public ThreadSafeObjectProvider()
			{
				m_Context = new ContextValue<T>();
			}
		}

		private static readonly ThreadSafeObjectProvider<MyComputer> m_ComputerObjectProvider = new ThreadSafeObjectProvider<MyComputer>();

		private static readonly ThreadSafeObjectProvider<MyApplication> m_AppObjectProvider = new ThreadSafeObjectProvider<MyApplication>();

		private static readonly ThreadSafeObjectProvider<User> m_UserObjectProvider = new ThreadSafeObjectProvider<User>();

		private static readonly ThreadSafeObjectProvider<MyWebServices> m_MyWebServicesObjectProvider = new ThreadSafeObjectProvider<MyWebServices>();

		[HelpKeyword("My.Computer")]
		internal static MyComputer Computer
		{
			[DebuggerHidden]
			get
			{
				return m_ComputerObjectProvider.GetInstance;
			}
		}

		[HelpKeyword("My.Application")]
		internal static MyApplication Application
		{
			[DebuggerHidden]
			get
			{
				return m_AppObjectProvider.GetInstance;
			}
		}

		[HelpKeyword("My.User")]
		internal static User User
		{
			[DebuggerHidden]
			get
			{
				return m_UserObjectProvider.GetInstance;
			}
		}

		[HelpKeyword("My.WebServices")]
		internal static MyWebServices WebServices
		{
			[DebuggerHidden]
			get
			{
				return m_MyWebServicesObjectProvider.GetInstance;
			}
		}
	}
	[CompilerGenerated]
	[GeneratedCode("Microsoft.VisualStudio.Editors.SettingsDesigner.SettingsSingleFileGenerator", "17.4.0.0")]
	[EditorBrowsable(EditorBrowsableState.Advanced)]
	internal sealed class MySettings : ApplicationSettingsBase
	{
		private static MySettings defaultInstance = (MySettings)SettingsBase.Synchronized(new MySettings());

		public static MySettings Default => defaultInstance;
	}
	[StandardModule]
	[HideModuleName]
	[DebuggerNonUserCode]
	[CompilerGenerated]
	internal sealed class MySettingsProperty
	{
		[HelpKeyword("My.Settings")]
		internal static MySettings Settings => MySettings.Default;
	}
}
namespace ConfigToptech.My.Resources
{
	[StandardModule]
	[GeneratedCode("System.Resources.Tools.StronglyTypedResourceBuilder", "17.0.0.0")]
	[DebuggerNonUserCode]
	[CompilerGenerated]
	[HideModuleName]
	internal sealed class Resources
	{
		private static ResourceManager resourceMan;

		private static CultureInfo resourceCulture;

		[EditorBrowsable(EditorBrowsableState.Advanced)]
		internal static ResourceManager ResourceManager
		{
			get
			{
				if (resourceMan == null)
				{
					ResourceManager resourceManager = new ResourceManager("ConfigToptech.Resources", typeof(Resources).Assembly);
					resourceMan = resourceManager;
				}
				return resourceMan;
			}
		}

		[EditorBrowsable(EditorBrowsableState.Advanced)]
		internal static CultureInfo Culture
		{
			get
			{
				return resourceCulture;
			}
			set
			{
				resourceCulture = value;
			}
		}
	}
}
