var erro = 0;
window.onload = function()
{
  if ($('#salvar').length) {
    
    $('#btnSalvar').click(function()
    {
      if(!existeImagem()){
        // $('#btnSalvar').parents('form').submit(function(event){
        //   event.preventDefault();
        // });
      }
      
    });

  } else if($('#atualizar')){
    
    $('#btnSalvar').click(function ()
    {
      $(this).parent('form').submit( function(){
        return true;
      });

    });
  }
  
  uploadArquivo();
}

/**
* -------------------------------------------------------------------------------
*/
function uploadArquivo()
{
  $('.arquivo').each(function(index){
    
    var inputArquivo = $(this).attr('id');
    
    $('#'+inputArquivo).change(function()
    {
      var arquivo = (this.files.length > 0 ? this.files[0].name  : '');
      $('#'+'arquivo-'+inputArquivo).removeClass('text-danger');
      $('#'+'arquivo-'+inputArquivo).addClass('text-success');
      $('#'+'arquivo-'+inputArquivo).html(arquivo);
    })
  });
}

/**
* ------------------------------------------------------------------------------------
*/
function existeImagem() 
{
  
  $('.verifica-arquivo').each(function()
  {
    erro = 0;
    
    var idComposto = $(this).attr('id');
    var id = idComposto.split('-'); 
    
    if(!$('#'+id[1]).val()){
      $(this).removeClass('text-success');
      $(this).addClass('text-danger');
      $(this).html('Escolha uma imagem');
      erro = 1;
    } 

  });

  return erro ? false : true;
}
