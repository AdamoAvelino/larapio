var data_inicio;

var hora_inicio;

var data_fim;

var hora_fim;

var tempoIniciar;

var valor_um

var valor_dois

var valor_tres

var dataCompletaIn

var dataCompletaFm

var segundosTotais;

var hoje = new Date();

var inicio = new Date(0,0,0,0,0,0,0);

var co2Total;

var entradaUmTotal;

var entradaDoisTotal;


/**
* ----------------------------------------------------------------------------
*/
window.onload = function()
{
  hora_inicio = document.getElementById('hora-inicio').value
  data_inicio = document.getElementById('data-inicio').value
  data_fim = document.getElementById('data-fim').value
  hora_fim = document.getElementById('hora-fim').value
  
  valor_um = document.getElementById('emissoes-resultado').value
  valor_dois = document.getElementById('emissoes-kg-um').value
  valor_tres = document.getElementById('emissoes-kg-dois').value
  
  var dtInicio = data_inicio.split('-');
  var dtfim = data_fim.split('-');
  var hrInicio = hora_inicio.split(':');
  var hrfim = hora_fim.split(':');
  dataCompletaIn = new Date(dtInicio[0], dtInicio[1] - 1, dtInicio[2], hrInicio[0], hrInicio[1], 0, 0)
  dataCompletaFm = new Date(dtfim[0], dtfim[1] - 1, dtfim[2], hrfim[0], hrfim[1], 0, 0)
  
  
  if (dataCompletaFm.getTime() > hoje.getTime()) {
    calculaTempo(false)
    preparaData();
  }else {
    calculaTempo(true);
  }
}

/**
* --------------------------------------------------------------------------------------
*/
function preparaData()
{
  
  setInterval(function(){
    
    inicio = new Date(0,0,0,0,0,0,0);
    hoje = new Date();
    tempoIniciar =  hoje.getTime() - dataCompletaIn.getTime();
    tempoFinalizar = hoje.getTime() - dataCompletaFm.getTime();

    console.log(tempoIniciar);
    
    if(tempoIniciar > 0 && tempoFinalizar <= 0) {
      
      inicio.setMilliseconds(tempoIniciar);
      tempoRodando = inicio;
      segundosParcial = tempoIniciar / 1000
      calcularEmissao(tempoRodando, segundosParcial, tempoIniciar);
      
    } else if(tempoFinalizar >= 0){
      
      calculaTempo(true);
    }
  }, 1000);
  
}
/**
* --------------------------------------------------------------------------------
*/
function calcularEmissao(objetoTempo, segundosParcial, tempoHora)
{

  var co2Parcial = co2Total * segundosParcial;  
  var entradaUmParcial = entradaUmTotal * segundosParcial;  
  var entradaDoisParcial = entradaDoisTotal * segundosParcial;

  apresentarResultado(co2Parcial, entradaUmParcial, entradaDoisParcial, objetoTempo, tempoHora)  
}

/**
* ---------------------------------------------------------------------------------------
* @param {bool} tempoFinal 
* 
*/
function calculaTempo(tempoFinal){
  
  var tempo = dataCompletaFm.getTime() - dataCompletaIn.getTime();
  
  inicio = new Date(0,0,0,0,0,0,0);
  inicio.setMilliseconds(tempo);
  
  tempoConcluido = inicio;
  segundosTotais = tempo / 1000;
  
  co2Total = valor_um / segundosTotais;
  entradaUmTotal = co2Total / valor_dois;
  entradaDoisTotal = co2Total / valor_tres;
  
  if(tempoFinal){
    calcularEmissao(tempoConcluido, segundosTotais, tempo) 
  }
  
}


function apresentarResultado(co2, EntradaUm, EntradaDois, tempoReferencia, tempoHora)
{
  
  document.getElementById('valor-um').innerHTML =  co2.toLocaleString('pt-BR', { currency: 'BRL' , maximumFractionDigits : 2, minimumFractionDigits : 2});
  document.getElementById('valor-dois').innerHTML =  EntradaUm.toLocaleString('pt-BR', { currency: 'BRL' , maximumFractionDigits : 2, minimumFractionDigits : 2});
  document.getElementById('valor-tres').innerHTML =  EntradaDois.toLocaleString('pt-BR', { currency: 'BRL', maximumFractionDigits : 2, minimumFractionDigits : 2});
  
}

