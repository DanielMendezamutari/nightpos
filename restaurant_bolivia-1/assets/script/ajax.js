function objetoAjax(){
	var xmlhttp=false;
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
		   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (E) {
			xmlhttp = false;
  		}
	}
	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		xmlhttp = new XMLHttpRequest();
	}
	return xmlhttp;
}


//FUNCION PARA CARGAR DETALLES DE PAGOS DE CUOTAS AL INSCRIBIR ESTUDIANTES
function CargaDetallesSalsas(nomsalsa){

var muestra = document.getElementById('muestradetallesalsas');

var elementos = document.getElementsByName("nomsalsa[]");
	var cont = 0; 
		for (var x=0; x < elementos.length; x++) {
			if (elementos[x].checked && !elementos[x].disabled) {
				cont = cont + 1;
			}
		}
		if(cont > 0) {
			texto = []; 
			for (x=0;x<elementos.length;x++){
			if (elementos[x].checked && !elementos[x].disabled) {
			texto.push(elementos[x].value);
		}
	} 

ajax=objetoAjax();
ajax.open("GET", "detalles_delivery.php?CargaDetalleSalsasAgregadas=si&nomsalsa="+texto+"&numeros="+cont);
ajax.onreadystatechange=function() {
		if (ajax.readyState==1 || ajax.readyState==2 || ajax.readyState==3) {
			muestra.innerHTML = "<center><img src='assets/images/loading.gif' width='40' height='40'/></center>";
		}
		if (ajax.readyState==4) {
			var resul=ajax.responseText
			muestra.innerHTML = resul											
		}
	}
	//como hacemos uso del metodo GET
	//colocamos null
	ajax.send(null);		
	} 
}